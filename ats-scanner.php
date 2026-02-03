<?php
$pageTitle = 'ATS Tarayıcı';
require_once 'config.php';
requireLogin();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && isset($_FILES['cv_file'])) {
    $file = $_FILES['cv_file'];
    
    if ($file['error'] === 0) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        $allowedExts = ['pdf', 'doc', 'docx', 'txt'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowedExts) && $file['size'] <= MAX_UPLOAD_SIZE) {
            $filename = 'ats_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $filepath = UPLOAD_PATH . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Extract text based on file type
                $text = '';
                $errorDetails = [];
                
                if ($ext === 'txt') {
                    $text = file_get_contents($filepath);
                } elseif ($ext === 'pdf') {
                    $extractResult = extractPDFText($filepath);
                    $text = $extractResult['text'];
                    $errorDetails = $extractResult['errors'];
                } elseif ($ext === 'docx') {
                    $zip = new ZipArchive();
                    if ($zip->open($filepath) === true) {
                        $xml = $zip->getFromName('word/document.xml');
                        $zip->close();
                        $text = strip_tags($xml);
                    }
                }
                
                // Trim and check if we have meaningful text
                $text = trim($text);
                
                if (!empty($text) && strlen($text) > 10) {
                    // Analyze the CV
                    $analysis = analyzeCVText($text);
                    $score = calculateATSScore($analysis);
                    
                    // Save to database
                    $stmt = db()->prepare("INSERT INTO ats_scans (user_id, filename, original_filename, file_path, score, analysis_json) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $filename,
                        $file['name'],
                        $filepath,
                        $score,
                        json_encode($analysis, JSON_UNESCAPED_UNICODE)
                    ]);
                    
                    $result = ['score' => $score, 'analysis' => $analysis];
                } else {
                    $errorMsg = 'Dosyadan metin çıkarılamadı.';
                    if (!empty($errorDetails)) {
                        $errorMsg .= ' Detay: ' . implode(', ', $errorDetails);
                    }
                    if (strlen($text) > 0 && strlen($text) <= 10) {
                        $errorMsg .= ' PDF çok az metin içeriyor veya görsel tabanlı olabilir.';
                    }
                    flash('danger', $errorMsg);
                }
            }
        } else {
            flash('danger', 'Geçersiz dosya türü veya boyutu.');
        }
    }
}

/**
 * PDF'den metin çıkarma fonksiyonu - ATS sistemleri gibi çoklu yöntem dener
 */
function extractPDFText($filepath) {
    $text = '';
    $errors = [];
    
    if (!file_exists($filepath)) {
        return ['text' => '', 'errors' => ['Dosya bulunamadı']];
    }
    
    // Method 1: Smalot PDF Parser (En yaygın kullanılan)
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filepath);
        $text = $pdf->getText();
        
        // Başarılı ve yeterli metin varsa döndür
        if (!empty(trim($text)) && strlen(trim($text)) > 50) {
            return ['text' => $text, 'errors' => []];
        }
        
        // Az metin çıktıysa, sayfa sayfa dene
        $pages = $pdf->getPages();
        $pageTexts = [];
        foreach ($pages as $page) {
            $pageText = $page->getText();
            if (!empty(trim($pageText))) {
                $pageTexts[] = $pageText;
            }
        }
        
        if (!empty($pageTexts)) {
            $text = implode("\n\n", $pageTexts);
            if (strlen(trim($text)) > 50) {
                return ['text' => $text, 'errors' => []];
            }
        }
        
        $errors[] = 'Parser metin bulamadı';
        
    } catch (Exception $e) {
        $errors[] = 'Parser hatası: ' . $e->getMessage();
    }
    
    // Method 2: Raw PDF content extraction (fallback)
    // Bazı PDF'ler için ham içerik okuma işe yarayabilir
    try {
        $content = file_get_contents($filepath);
        
        // PDF stream objelerinden metin çıkar
        if (preg_match_all('/\(([^)]+)\)/s', $content, $matches)) {
            $extractedText = implode(' ', $matches[1]);
            $extractedText = preg_replace('/[\x00-\x1F\x7F]/u', '', $extractedText);
            
            if (!empty(trim($extractedText)) && strlen(trim($extractedText)) > 50) {
                return ['text' => $extractedText, 'errors' => []];
            }
        }
        
        // BT/ET blokları arasındaki metni çıkar (PDF text objects)
        if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches)) {
            $extractedText = '';
            foreach ($matches[1] as $block) {
                // Tj ve TJ operatörlerinden metin çıkar
                if (preg_match_all('/\[([^\]]+)\]\s*TJ|\(([^)]+)\)\s*Tj/s', $block, $textMatches)) {
                    foreach ($textMatches[1] as $t) {
                        if (!empty($t)) {
                            $extractedText .= $t . ' ';
                        }
                    }
                    foreach ($textMatches[2] as $t) {
                        if (!empty($t)) {
                            $extractedText .= $t . ' ';
                        }
                    }
                }
            }
            
            $extractedText = preg_replace('/[\x00-\x1F\x7F]/u', '', $extractedText);
            if (!empty(trim($extractedText)) && strlen(trim($extractedText)) > 50) {
                return ['text' => $extractedText, 'errors' => []];
            }
        }
        
        $errors[] = 'Ham içerik okuma başarısız';
        
    } catch (Exception $e) {
        $errors[] = 'Ham okuma hatası: ' . $e->getMessage();
    }
    
    // Hiçbir yöntem işe yaramadıysa
    if (empty(trim($text))) {
        $errors[] = 'PDF görsel tabanlı veya şifreli olabilir';
    }
    
    return ['text' => $text, 'errors' => $errors];
}

function analyzeCVText($text) {
    $textLower = mb_strtolower($text);
    $analysis = [];
    
    // Contact Info
    $contactScore = 0;
    $contactFound = [];
    if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/', $textLower)) { $contactScore += 25; $contactFound[] = 'E-posta'; }
    if (preg_match('/[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,9}/', $text)) { $contactScore += 25; $contactFound[] = 'Telefon'; }
    if (preg_match('/linkedin/i', $text)) { $contactScore += 25; $contactFound[] = 'LinkedIn'; }
    if (preg_match('/(ankara|istanbul|izmir|location|address|şehir)/i', $text)) { $contactScore += 25; $contactFound[] = 'Konum'; }
    $analysis['contact'] = ['score' => $contactScore, 'found' => $contactFound, 'suggestions' => $contactScore < 100 ? ['Eksik iletişim bilgilerini ekleyin'] : []];
    
    // Sections
    $sectionScore = 0;
    $sectionsFound = [];
    $sectionKeywords = [
        'experience' => ['experience', 'deneyim', 'work', 'iş'],
        'education' => ['education', 'eğitim', 'öğrenim'],
        'skills' => ['skills', 'beceri', 'yetenek'],
        'summary' => ['summary', 'özet', 'profil', 'about']
    ];
    foreach ($sectionKeywords as $section => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($textLower, $kw) !== false) {
                $sectionScore += 25;
                $sectionsFound[] = ucfirst($section);
                break;
            }
        }
    }
    $analysis['sections'] = ['score' => min(100, $sectionScore), 'found' => $sectionsFound, 'suggestions' => $sectionScore < 100 ? ['Tüm temel bölümleri ekleyin (Deneyim, Eğitim, Beceriler, Özet)'] : []];
    
    // Keywords
    $techKeywords = ['php', 'java', 'python', 'javascript', 'sql', 'html', 'css', 'api', 'git', 'react', 'node'];
    $actionVerbs = ['developed', 'geliştirdim', 'managed', 'yönettim', 'led', 'created', 'oluşturdum', 'implemented'];
    $keywordScore = 0;
    $keywordsFound = [];
    foreach ($techKeywords as $kw) {
        if (strpos($textLower, $kw) !== false) { $keywordsFound[] = $kw; $keywordScore += 5; }
    }
    foreach ($actionVerbs as $kw) {
        if (strpos($textLower, $kw) !== false) { $keywordScore += 10; }
    }
    $analysis['keywords'] = ['score' => min(100, $keywordScore), 'found' => $keywordsFound, 'suggestions' => $keywordScore < 50 ? ['Daha fazla teknik beceri ve aksiyon fiili kullanın'] : []];
    
    // Length
    $wordCount = str_word_count($text);
    $lengthScore = 100;
    $lengthFeedback = 'İdeal uzunluk';
    if ($wordCount < 200) { $lengthScore = 40; $lengthFeedback = 'Çok kısa'; }
    elseif ($wordCount < 400) { $lengthScore = 70; $lengthFeedback = 'Biraz kısa'; }
    elseif ($wordCount > 1500) { $lengthScore = 60; $lengthFeedback = 'Çok uzun'; }
    $analysis['length'] = ['score' => $lengthScore, 'word_count' => $wordCount, 'feedback' => $lengthFeedback, 'suggestions' => $lengthScore < 100 ? ['Kelime sayısını 400-1000 arasında tutun'] : []];
    
    // Format
    $formatScore = 100;
    $formatSuggestions = [];
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $text)) { $formatScore -= 30; $formatSuggestions[] = 'Özel karakterleri temizleyin'; }
    $bulletCount = preg_match_all('/[•\-\*]/m', $text);
    if ($bulletCount < 5) { $formatScore -= 20; $formatSuggestions[] = 'Madde işaretleri kullanın'; }
    $analysis['format'] = ['score' => max(0, $formatScore), 'suggestions' => $formatSuggestions];
    
    return $analysis;
}

function calculateATSScore($analysis) {
    $weights = ['contact' => 20, 'sections' => 25, 'keywords' => 25, 'length' => 15, 'format' => 15];
    $total = 0;
    foreach ($weights as $key => $weight) {
        $total += ($analysis[$key]['score'] / 100) * $weight;
    }
    return round($total);
}

// Get history
$stmt = db()->prepare("SELECT * FROM ats_scans WHERE user_id = ? ORDER BY scanned_at DESC LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();

require_once 'header.php';
?>

<style>
.upload-area{border:3px dashed var(--border);border-radius:12px;padding:60px 40px;text-align:center;cursor:pointer;transition:.3s}
.upload-area:hover,.upload-area.dragover{border-color:var(--primary);background:var(--secondary)}
.upload-area i{font-size:4rem;color:var(--primary);margin-bottom:20px}
.score-circle{width:150px;height:150px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:bold;color:#fff;margin:0 auto 20px}
.score-circle.excellent{background:linear-gradient(135deg,#28a745,#20c997)}
.score-circle.good{background:linear-gradient(135deg,var(--primary),var(--primary-light))}
.score-circle.average{background:linear-gradient(135deg,#ffc107,#fd7e14)}
.score-circle.poor{background:linear-gradient(135deg,#dc3545,#e83e8c)}
.analysis-item{padding:15px;border-radius:8px;margin-bottom:10px}
.analysis-item.success{background:#d4edda}.analysis-item.warning{background:#fff3cd}.analysis-item.danger{background:#f8d7da}
.analysis-item h4{margin:0 0 5px;display:flex;justify-content:space-between}
.analysis-item p{margin:0;font-size:.9rem}
</style>

<div class="page-header">
<div class="container">
<h1><i class="fas fa-search"></i> ATS Tarayıcı</h1>
<p>CV'nizi yükleyin ve ATS uyumluluk skorunuzu öğrenin.</p>
</div>
</div>

<div class="container">
<div class="row">
<div class="col-6">
<div class="card">
<div class="card-header"><h3><i class="fas fa-upload"></i> CV Yükle</h3></div>
<div class="card-body">
<form method="POST" enctype="multipart/form-data" id="uploadForm">
<input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
<div class="upload-area" onclick="document.getElementById('cv_file').click()" id="dropArea">
<i class="fas fa-cloud-upload-alt"></i>
<h3>CV Dosyanızı Yükleyin</h3>
<p class="text-muted">PDF, DOC, DOCX veya TXT (Max 5MB)</p>
<input type="file" name="cv_file" id="cv_file" accept=".pdf,.doc,.docx,.txt" style="display:none" onchange="document.getElementById('uploadForm').submit()">
</div>
</form>
</div>
</div>

<?php if($history):?>
<div class="card mt-2">
<div class="card-header"><h3><i class="fas fa-history"></i> Geçmiş Taramalar</h3></div>
<div class="card-body" style="padding:0">
<table style="width:100%;border-collapse:collapse">
<?php foreach($history as $h):?>
<tr style="border-bottom:1px solid var(--border)">
<td style="padding:12px"><?=e($h['original_filename'])?></td>
<td style="padding:12px;text-align:center">
<span style="background:<?=$h['score']>=70?'var(--success)':($h['score']>=50?'var(--warning)':'var(--danger)')?>;color:#fff;padding:4px 12px;border-radius:15px;font-weight:600"><?=$h['score']?>%</span>
</td>
<td style="padding:12px;text-align:right;color:var(--text-muted);font-size:.85rem"><?=date('d.m.Y',strtotime($h['scanned_at']))?></td>
</tr>
<?php endforeach;?>
</table>
</div>
</div>
<?php endif;?>
</div>

<div class="col-6">
<?php if($result):?>
<div class="card">
<div class="card-header"><h3><i class="fas fa-chart-pie"></i> Analiz Sonucu</h3></div>
<div class="card-body">
<div class="text-center mb-2">
<div class="score-circle <?=$result['score']>=80?'excellent':($result['score']>=60?'good':($result['score']>=40?'average':'poor'))?>">
<?=$result['score']?>%
</div>
<h3><?=$result['score']>=80?'Mükemmel!':($result['score']>=60?'İyi':($result['score']>=40?'Ortalama':'Geliştirme Gerekli'))?></h3>
</div>

<?php foreach($result['analysis'] as $key => $data):?>
<div class="analysis-item <?=$data['score']>=70?'success':($data['score']>=40?'warning':'danger')?>">
<h4>
<span><?=ucfirst($key)?></span>
<span><?=$data['score']?>%</span>
</h4>
<?php if(isset($data['found']) && $data['found']):?>
<p><i class="fas fa-check"></i> <?=implode(', ', $data['found'])?></p>
<?php endif;?>
<?php if(isset($data['suggestions']) && $data['suggestions']):?>
<p style="margin-top:5px"><i class="fas fa-lightbulb"></i> <?=implode(' ', $data['suggestions'])?></p>
<?php endif;?>
</div>
<?php endforeach;?>
</div>
</div>
<?php else:?>
<div class="card">
<div class="card-body text-center" style="padding:60px">
<i class="fas fa-file-search" style="font-size:4rem;color:var(--text-muted);margin-bottom:20px"></i>
<h3>CV'nizi Analiz Edin</h3>
<p class="text-muted">Sol taraftan CV dosyanızı yükleyin ve ATS uyumluluk skorunuzu görün.</p>
</div>
</div>
<?php endif;?>
</div>
</div>
</div>

<script>
const dropArea = document.getElementById('dropArea');
['dragenter','dragover'].forEach(e => dropArea.addEventListener(e, () => dropArea.classList.add('dragover')));
['dragleave','drop'].forEach(e => dropArea.addEventListener(e, () => dropArea.classList.remove('dragover')));
dropArea.addEventListener('drop', e => {
    e.preventDefault();
    document.getElementById('cv_file').files = e.dataTransfer.files;
    document.getElementById('uploadForm').submit();
});
dropArea.addEventListener('dragover', e => e.preventDefault());
</script>

<?php require_once 'footer.php';?>
