<?php
/**
 * PDF Text Extraction Test Tool
 * PDF'den metin çıkarma işlemini test etmek için
 */

require_once 'config.php';
requireLogin();

$result = null;
$debugInfo = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    
    if ($file['error'] === 0 && strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'pdf') {
        $filepath = UPLOAD_PATH . 'test_' . time() . '.pdf';
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Include the extraction function
            require_once 'ats-scanner.php';
            
            $extractResult = extractPDFText($filepath);
            
            $result = [
                'text' => $extractResult['text'],
                'text_length' => strlen($extractResult['text']),
                'text_length_trimmed' => strlen(trim($extractResult['text'])),
                'word_count' => str_word_count($extractResult['text']),
                'errors' => $extractResult['errors'],
                'file_size' => filesize($filepath),
                'file_name' => $file['name']
            ];
            
            // Dosyayı sil
            @unlink($filepath);
        }
    }
}

require_once 'header.php';
?>

<style>
.debug-box{background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:20px;margin:20px 0;font-family:monospace;font-size:14px}
.debug-box pre{margin:0;white-space:pre-wrap;word-wrap:break-word;max-height:400px;overflow-y:auto}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin:20px 0}
.stat-card{background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:15px;text-align:center}
.stat-card h3{margin:0 0 5px;font-size:2rem;color:var(--primary)}
.stat-card p{margin:0;color:var(--text-muted);font-size:.9rem}
.error-list{background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:15px;margin:15px 0}
.error-list li{margin:5px 0}
</style>

<div class="page-header">
<div class="container">
<h1><i class="fas fa-flask"></i> PDF Metin Çıkarma Test</h1>
<p>PDF dosyanızdan metin çıkarma işlemini test edin ve detaylı sonuçları görün.</p>
</div>
</div>

<div class="container">
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-header"><h3><i class="fas fa-upload"></i> PDF Yükle ve Test Et</h3></div>
<div class="card-body">
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
<div style="margin-bottom:15px">
<label><strong>PDF Dosyası Seçin:</strong></label>
<input type="file" name="test_file" accept=".pdf" required style="display:block;margin-top:10px">
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Test Et</button>
</form>
</div>
</div>

<?php if($result): ?>
<div class="card mt-2">
<div class="card-header"><h3><i class="fas fa-chart-bar"></i> Test Sonuçları</h3></div>
<div class="card-body">

<div class="stat-grid">
<div class="stat-card">
<h3><?=$result['text_length']?></h3>
<p>Toplam Karakter</p>
</div>
<div class="stat-card">
<h3><?=$result['text_length_trimmed']?></h3>
<p>Temiz Karakter</p>
</div>
<div class="stat-card">
<h3><?=$result['word_count']?></h3>
<p>Kelime Sayısı</p>
</div>
<div class="stat-card">
<h3><?=number_format($result['file_size']/1024, 1)?> KB</h3>
<p>Dosya Boyutu</p>
</div>
</div>

<?php if(!empty($result['errors'])): ?>
<div class="error-list">
<h4 style="margin:0 0 10px"><i class="fas fa-exclamation-triangle"></i> Hatalar / Uyarılar:</h4>
<ul style="margin:0;padding-left:20px">
<?php foreach($result['errors'] as $error): ?>
<li><?=e($error)?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<h4 style="margin-top:20px"><i class="fas fa-file-alt"></i> Çıkarılan Metin:</h4>
<div class="debug-box">
<?php if(!empty(trim($result['text']))): ?>
<pre><?=e(substr($result['text'], 0, 5000))?><?=strlen($result['text'])>5000?' ... (ilk 5000 karakter gösteriliyor)':''?></pre>
<?php else: ?>
<p style="color:#dc3545;margin:0"><strong>❌ Metin çıkarılamadı!</strong></p>
<p style="margin:10px 0 0">Olası nedenler:</p>
<ul style="margin:5px 0 0;padding-left:20px">
<li>PDF görsel tabanlı (taranmış belge)</li>
<li>PDF şifreli veya korumalı</li>
<li>PDF bozuk veya desteklenmeyen formatta</li>
</ul>
<?php endif; ?>
</div>

<h4 style="margin-top:20px"><i class="fas fa-info-circle"></i> Öneriler:</h4>
<div style="background:#e7f3ff;border:1px solid #2196F3;border-radius:8px;padding:15px">
<?php if($result['text_length_trimmed'] > 100): ?>
<p style="margin:0;color:#0d47a1"><i class="fas fa-check-circle"></i> <strong>Başarılı!</strong> PDF'den yeterli metin çıkarıldı. ATS Scanner çalışacaktır.</p>
<?php elseif($result['text_length_trimmed'] > 0): ?>
<p style="margin:0;color:#f57c00"><i class="fas fa-exclamation-circle"></i> <strong>Uyarı:</strong> Çok az metin çıkarıldı. PDF'niz görsel tabanlı olabilir.</p>
<p style="margin:10px 0 0">Çözüm: PDF'i Word'e dönüştürüp tekrar PDF olarak kaydedin veya DOCX formatında yükleyin.</p>
<?php else: ?>
<p style="margin:0;color:#d32f2f"><i class="fas fa-times-circle"></i> <strong>Başarısız!</strong> Hiç metin çıkarılamadı.</p>
<p style="margin:10px 0 0">Çözüm: CV'nizi Word/Google Docs'ta açıp yeniden PDF olarak kaydedin.</p>
<?php endif; ?>
</div>

</div>
</div>
<?php endif; ?>

</div>
</div>
</div>

<?php require_once 'footer.php'; ?>
