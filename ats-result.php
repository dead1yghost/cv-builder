<?php
$pageTitle = 'ATS Tarama Sonucu';
require_once 'config.php';
requireLogin();

// Get scan ID from URL
$scanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$scanId) {
    header('Location: ats-scanner.php');
    exit;
}

// Fetch scan details
$stmt = db()->prepare("SELECT * FROM ats_scans WHERE id = ? AND user_id = ?");
$stmt->execute([$scanId, $_SESSION['user_id']]);
$scan = $stmt->fetch();

if (!$scan) {
    flash('danger', 'Tarama bulunamadı.');
    header('Location: ats-scanner.php');
    exit;
}

$analysis = json_decode($scan['analysis_json'], true);
$isError = isset($analysis['error']) && $analysis['error'];

require_once 'header.php';
?>

<style>
.score-circle{width:200px;height:200px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:4rem;font-weight:bold;color:#fff;margin:0 auto 30px;box-shadow:0 10px 30px rgba(0,0,0,0.2)}
.score-circle.excellent{background:linear-gradient(135deg,#28a745,#20c997)}
.score-circle.good{background:linear-gradient(135deg,var(--primary),var(--primary-light))}
.score-circle.average{background:linear-gradient(135deg,#ffc107,#fd7e14)}
.score-circle.poor{background:linear-gradient(135deg,#dc3545,#e83e8c)}
.score-circle.error{background:linear-gradient(135deg,#6c757d,#495057)}
.analysis-section{background:#fff;border-radius:12px;padding:25px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
.analysis-item{padding:20px;border-radius:8px;margin-bottom:15px;border-left:4px solid}
.analysis-item.success{background:#d4edda;border-color:#28a745}
.analysis-item.warning{background:#fff3cd;border-color:#ffc107}
.analysis-item.danger{background:#f8d7da;border-color:#dc3545}
.analysis-item h4{margin:0 0 10px;display:flex;justify-content:space-between;align-items:center}
.analysis-item .score-badge{background:#fff;padding:5px 15px;border-radius:20px;font-size:1.1rem;font-weight:600}
.analysis-item p{margin:5px 0;font-size:.95rem}
.analysis-item ul{margin:10px 0 0;padding-left:20px}
.analysis-item li{margin:5px 0}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin:20px 0}
.info-card{background:#f8f9fa;padding:15px;border-radius:8px;text-align:center}
.info-card h5{margin:0 0 5px;color:var(--text-muted);font-size:.9rem;font-weight:500}
.info-card p{margin:0;font-size:1.2rem;font-weight:600;color:var(--primary-dark)}
</style>

<div class="page-header">
<div class="container">
<h1><i class="fas fa-chart-line"></i> ATS Tarama Sonucu</h1>
<p>Detaylı analiz raporu ve iyileştirme önerileri</p>
</div>
</div>

<div class="container">
<div class="row">
<div class="col-12">

<?php if($isError): ?>
<!-- Error State -->
<div class="card">
<div class="card-body text-center" style="padding:60px">
<div class="score-circle error">
<i class="fas fa-exclamation-triangle"></i>
</div>
<h2 style="color:var(--danger);margin-bottom:15px">Tarama Başarısız</h2>
<p style="font-size:1.1rem;color:var(--text-muted);margin-bottom:30px"><?=e($analysis['message'])?></p>
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:20px;margin:20px auto;max-width:600px;text-align:left">
<h4 style="margin:0 0 10px;color:#856404"><i class="fas fa-lightbulb"></i> Çözüm Önerileri</h4>
<ul style="margin:0;padding-left:20px;color:#856404">
<li>CV'nizi Word veya Google Docs'ta açıp yeniden PDF olarak kaydedin</li>
<li>DOCX formatında yükleyin</li>
<li><a href="pdf-help" style="color:#856404;text-decoration:underline;font-weight:600">Detaylı yardım sayfasını</a> inceleyin</li>
</ul>
</div>
<div class="d-flex gap-2" style="justify-content:center;margin-top:30px">
<a href="ats-scanner" class="btn btn-primary"><i class="fas fa-redo"></i> Tekrar Dene</a>
<a href="cv-create" class="btn btn-success"><i class="fas fa-plus"></i> Yeni CV Oluştur</a>
</div>
</div>
</div>

<?php else: ?>
<!-- Success State -->
<div class="card">
<div class="card-body text-center" style="padding:40px">
<div class="score-circle <?=$scan['score']>=80?'excellent':($scan['score']>=60?'good':($scan['score']>=40?'average':'poor'))?>">
<?=$scan['score']?>%
</div>
<h2 style="margin-bottom:10px"><?=$scan['score']>=80?'Mükemmel!':($scan['score']>=60?'İyi':($scan['score']>=40?'Ortalama':'Geliştirme Gerekli'))?></h2>
<p style="color:var(--text-muted);font-size:1.1rem">ATS Uyumluluk Skoru</p>

<div class="info-grid">
<div class="info-card">
<h5>Dosya Adı</h5>
<p><?=e($scan['original_filename'])?></p>
</div>
<div class="info-card">
<h5>Tarama Tarihi</h5>
<p><?=date('d.m.Y H:i', strtotime($scan['scanned_at']))?></p>
</div>
<div class="info-card">
<h5>Genel Skor</h5>
<p style="color:<?=$scan['score']>=70?'var(--success)':($scan['score']>=50?'var(--warning)':'var(--danger)')?>"><?=$scan['score']?>%</p>
</div>
</div>
</div>
</div>

<div class="analysis-section">
<h3 style="margin:0 0 20px"><i class="fas fa-clipboard-list"></i> Detaylı Analiz</h3>

<?php
$sectionTitles = [
    'contact' => 'İletişim Bilgileri',
    'sections' => 'CV Bölümleri',
    'keywords' => 'Anahtar Kelimeler',
    'length' => 'Uzunluk ve İçerik',
    'format' => 'Format ve Düzen'
];

foreach($analysis as $key => $data):
    if(!isset($data['score'])) continue;
    $scoreClass = $data['score']>=70?'success':($data['score']>=40?'warning':'danger');
?>
<div class="analysis-item <?=$scoreClass?>">
<h4>
<span><i class="fas fa-<?=$key=='contact'?'address-card':($key=='sections'?'list':($key=='keywords'?'key':($key=='length'?'ruler':'palette')))?>"></i> <?=$sectionTitles[$key]??ucfirst($key)?></span>
<span class="score-badge" style="color:<?=$data['score']>=70?'var(--success)':($data['score']>=40?'var(--warning)':'var(--danger)')?>"><?=$data['score']?>%</span>
</h4>

<?php if(isset($data['found']) && $data['found']): ?>
<p><strong><i class="fas fa-check-circle"></i> Bulunan:</strong> <?=implode(', ', $data['found'])?></p>
<?php endif; ?>

<?php if(isset($data['word_count'])): ?>
<p><strong><i class="fas fa-file-word"></i> Kelime Sayısı:</strong> <?=$data['word_count']?> - <?=$data['feedback']?></p>
<?php endif; ?>

<?php if(isset($data['suggestions']) && $data['suggestions']): ?>
<div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(0,0,0,0.1)">
<p style="margin-bottom:5px"><strong><i class="fas fa-lightbulb"></i> İyileştirme Önerileri:</strong></p>
<ul style="margin:0">
<?php foreach($data['suggestions'] as $suggestion): ?>
<li><?=$suggestion?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<div class="analysis-section">
<h3 style="margin:0 0 15px"><i class="fas fa-info-circle"></i> Skor Değerlendirmesi</h3>
<div style="background:#f8f9fa;padding:20px;border-radius:8px">
<div style="margin-bottom:15px">
<strong style="color:#28a745">80-100%:</strong> Mükemmel! CV'niz ATS sistemlerinden kolayca geçecektir.
</div>
<div style="margin-bottom:15px">
<strong style="color:var(--primary)">60-79%:</strong> İyi! Birkaç iyileştirme ile mükemmel olabilir.
</div>
<div style="margin-bottom:15px">
<strong style="color:#ffc107">40-59%:</strong> Ortalama. Önerileri uygulayarak skorunuzu artırın.
</div>
<div>
<strong style="color:#dc3545">0-39%:</strong> Geliştirme gerekli. CV'nizi yeniden yapılandırın.
</div>
</div>
</div>

<div class="d-flex gap-2" style="justify-content:center;margin:30px 0">
<a href="ats-scanner" class="btn btn-primary"><i class="fas fa-redo"></i> Yeni Tarama</a>
<a href="cv-create" class="btn btn-success"><i class="fas fa-edit"></i> CV'mi İyileştir</a>
<a href="dashboard" class="btn btn-outline"><i class="fas fa-home"></i> Dashboard</a>
</div>

<?php endif; ?>

</div>
</div>
</div>

<?php require_once 'footer.php'; ?>
