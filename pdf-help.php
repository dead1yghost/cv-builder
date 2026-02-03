<?php
$pageTitle = 'PDF Yardım';
require_once 'config.php';
require_once 'header.php';
?>

<style>
.help-section{background:#fff;border:1px solid var(--border);border-radius:12px;padding:30px;margin-bottom:20px}
.help-section h3{color:var(--primary);margin:0 0 15px}
.step-box{background:#f8f9fa;border-left:4px solid var(--primary);padding:15px;margin:15px 0;border-radius:4px}
.step-box h4{margin:0 0 10px;color:var(--primary)}
.comparison{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0}
.comparison-item{padding:20px;border-radius:8px;text-align:center}
.comparison-item.good{background:#d4edda;border:2px solid #28a745}
.comparison-item.bad{background:#f8d7da;border:2px solid #dc3545}
.comparison-item i{font-size:3rem;margin-bottom:10px}
.comparison-item.good i{color:#28a745}
.comparison-item.bad i{color:#dc3545}
</style>

<div class="page-header">
<div class="container">
<h1><i class="fas fa-question-circle"></i> PDF Dosyası Yardım</h1>
<p>Görsel tabanlı PDF sorununu nasıl çözersiniz?</p>
</div>
</div>

<div class="container">
<div class="help-section">
<h3><i class="fas fa-exclamation-triangle"></i> Sorun Nedir?</h3>
<p>PDF dosyanız <strong>"görsel tabanlı"</strong> (image-based) bir belgedir. Bu, CV'nizin bir tarayıcıdan geçirilmiş veya ekran görüntüsü olarak kaydedilmiş olduğu anlamına gelir.</p>
<p>Bu tür PDF'ler içinde gerçek metin bulunmaz, sadece resim vardır. ATS (Applicant Tracking System) yazılımları bu dosyaları okuyamaz ve <strong>başvurunuz otomatik olarak reddedilebilir</strong>.</p>

<div class="comparison">
<div class="comparison-item bad">
<i class="fas fa-times-circle"></i>
<h4>❌ Görsel Tabanlı PDF</h4>
<p>Taranmış belge, ekran görüntüsü, resim olarak kaydedilmiş</p>
<p style="margin-top:10px;font-size:.85rem;color:#721c24"><strong>ATS okuyamaz!</strong></p>
</div>
<div class="comparison-item good">
<i class="fas fa-check-circle"></i>
<h4>✅ Metin Tabanlı PDF</h4>
<p>Word/Google Docs'tan kaydedilmiş, metin seçilebilir</p>
<p style="margin-top:10px;font-size:.85rem;color:#155724"><strong>ATS okuyabilir!</strong></p>
</div>
</div>
</div>

<div class="help-section">
<h3><i class="fas fa-tools"></i> Çözüm Yöntemleri</h3>

<div class="step-box">
<h4>Yöntem 1: Word/Google Docs'tan Yeniden Kaydet (Önerilen)</h4>
<ol style="margin:10px 0 0;padding-left:20px">
<li>CV'nizi Microsoft Word veya Google Docs'ta açın</li>
<li><strong>Dosya → Farklı Kaydet → PDF</strong> seçeneğini kullanın</li>
<li>Yeni PDF dosyasını ATS Scanner'a yükleyin</li>
</ol>
<p style="margin:10px 0 0;color:var(--success)"><i class="fas fa-check"></i> Bu yöntem %100 ATS uyumlu PDF oluşturur</p>
</div>

<div class="step-box">
<h4>Yöntem 2: DOCX Formatında Yükle</h4>
<ol style="margin:10px 0 0;padding-left:20px">
<li>CV'nizi Word formatında (.docx) kaydedin</li>
<li>DOCX dosyasını direkt olarak ATS Scanner'a yükleyin</li>
</ol>
<p style="margin:10px 0 0;color:var(--success)"><i class="fas fa-check"></i> DOCX dosyaları her zaman metin içerir</p>
</div>

<div class="step-box">
<h4>Yöntem 3: Online PDF Dönüştürücü Kullan</h4>
<ol style="margin:10px 0 0;padding-left:20px">
<li>Görsel PDF'nizi bir online OCR aracına yükleyin (örn: <a href="https://www.onlineocr.net/" target="_blank">OnlineOCR.net</a>)</li>
<li>Metne dönüştürülmüş dosyayı Word olarak indirin</li>
<li>Word'den yeniden PDF olarak kaydedin</li>
</ol>
<p style="margin:10px 0 0;color:var(--warning)"><i class="fas fa-exclamation-circle"></i> OCR hataları olabilir, kontrol edin</p>
</div>

<div class="step-box">
<h4>Yöntem 4: CV'nizi Sıfırdan Oluşturun</h4>
<ol style="margin:10px 0 0;padding-left:20px">
<li>Sitemizin <a href="cv-create">CV Oluşturucu</a> özelliğini kullanın</li>
<li>Bilgilerinizi girin ve ATS uyumlu CV oluşturun</li>
<li>Otomatik olarak metin tabanlı PDF indirebilirsiniz</li>
</ol>
<p style="margin:10px 0 0;color:var(--success)"><i class="fas fa-check"></i> En kolay ve garantili yöntem</p>
</div>
</div>

<div class="help-section">
<h3><i class="fas fa-check-double"></i> PDF'nizin Metin Tabanlı Olduğunu Nasıl Anlarsınız?</h3>
<ol style="margin:10px 0 0;padding-left:20px;line-height:1.8">
<li>PDF'i bir PDF okuyucuda açın (Adobe Reader, Chrome, vb.)</li>
<li>Fareyle metni seçmeyi deneyin</li>
<li><strong>Metin seçilebiliyorsa</strong> → ✅ Metin tabanlı PDF (ATS okuyabilir)</li>
<li><strong>Metin seçilemiyorsa</strong> → ❌ Görsel tabanlı PDF (ATS okuyamaz)</li>
</ol>
</div>

<div class="help-section">
<h3><i class="fas fa-lightbulb"></i> ATS İçin En İyi Uygulamalar</h3>
<ul style="margin:10px 0 0;padding-left:20px;line-height:1.8">
<li>✅ CV'nizi her zaman Word/Google Docs'ta oluşturun</li>
<li>✅ Standart fontlar kullanın (Arial, Calibri, Times New Roman)</li>
<li>✅ Basit formatlar tercih edin (tablolar, sütunlar yerine)</li>
<li>✅ PDF olarak kaydetmeden önce metinleri kontrol edin</li>
<li>❌ Tarayıcıdan geçirmeyin</li>
<li>❌ Ekran görüntüsü olarak kaydetmeyin</li>
<li>❌ Resim olarak dışa aktarmayın</li>
</ul>
</div>

<div style="text-align:center;margin:30px 0">
<a href="ats-scanner" class="btn btn-primary"><i class="fas fa-arrow-left"></i> ATS Scanner'a Dön</a>
<a href="cv-create" class="btn btn-success"><i class="fas fa-plus"></i> Yeni CV Oluştur</a>
</div>
</div>

<?php require_once 'footer.php'; ?>
