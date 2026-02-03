<?php
$pageTitle = 'Dashboard';
require_once 'config.php';
requireLogin();

$user = currentUser();

// Get user's CVs
$stmt = db()->prepare("
    SELECT c.*, p.full_name as cv_name, p.title as cv_title 
    FROM cvs c 
    LEFT JOIN cv_personal_info p ON c.id = p.cv_id 
    WHERE c.user_id = ? 
    ORDER BY c.updated_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cvs = $stmt->fetchAll();

// Get recent ATS scans
$stmt = db()->prepare("SELECT * FROM ats_scans WHERE user_id = ? ORDER BY scanned_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$scans = $stmt->fetchAll();

require_once 'header.php';
?>

<style>
.dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: var(--shadow); text-align: center; }
.stat-card i { font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; }
.stat-card h3 { font-size: 2rem; margin-bottom: 5px; color: var(--primary-dark); }
.stat-card p { color: var(--text-muted); margin: 0; }

.cv-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
.cv-card { background: white; border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; transition: 0.3s; }
.cv-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.cv-preview { height: 150px; background: linear-gradient(135deg, var(--secondary), #c4e3e2); display: flex; align-items: center; justify-content: center; }
.cv-preview i { font-size: 4rem; color: var(--primary); opacity: 0.5; }
.cv-info { padding: 20px; }
.cv-info h4 { margin: 0 0 5px; }
.cv-info p { color: var(--text-muted); font-size: 0.9rem; margin: 0 0 15px; }
.cv-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.new-cv-card { border: 3px dashed var(--border); background: transparent; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 250px; cursor: pointer; transition: 0.3s; text-decoration: none; color: var(--text-muted); }
.new-cv-card:hover { border-color: var(--primary); background: var(--secondary); color: var(--primary); }
.new-cv-card i { font-size: 3rem; margin-bottom: 15px; }

.section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.section-title h2 { margin: 0; }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-th-large"></i> Merhaba, <?= e($user['full_name']) ?>!</h1>
        <p>CV'lerinizi yönetin ve ATS skorunuzu öğrenin.</p>
    </div>
</div>

<div class="container">
    <!-- Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-file-alt"></i>
            <h3><?= count($cvs) ?></h3>
            <p>Toplam CV</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-search"></i>
            <h3><?= count($scans) ?></h3>
            <p>ATS Taraması</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-star"></i>
            <h3><?= $scans ? max(array_column($scans, 'score')) : 0 ?></h3>
            <p>En Yüksek Skor</p>
        </div>
    </div>
    
    <!-- CVs -->
    <div class="section-title">
        <h2><i class="fas fa-file-alt"></i> CV'lerim</h2>
        <a href="cv-create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Yeni CV</a>
    </div>
    
    <div class="cv-grid">
        <!-- New CV Card -->
        <a href="cv-create.php" class="new-cv-card card">
            <i class="fas fa-plus-circle"></i>
            <span style="font-size:1.1rem;">Yeni CV Oluştur</span>
        </a>
        
        <?php foreach ($cvs as $cv): ?>
        <div class="cv-card">
            <div class="cv-preview" style="background: linear-gradient(135deg, <?= e($THEME_COLORS[$cv['theme_color']]['primary'] ?? '#2B7A78') ?>22, <?= e($THEME_COLORS[$cv['theme_color']]['primary'] ?? '#2B7A78') ?>44);">
                <i class="fas fa-file-alt" style="color: <?= e($THEME_COLORS[$cv['theme_color']]['primary'] ?? '#2B7A78') ?>;"></i>
            </div>
            <div class="cv-info">
                <h4><?= e($cv['cv_name'] ?: $cv['title']) ?></h4>
                <p>
                    <?= e($cv['cv_title'] ?: 'Başlık belirtilmemiş') ?><br>
                    <small>Son güncelleme: <?= date('d.m.Y H:i', strtotime($cv['updated_at'])) ?></small>
                </p>
                <div class="cv-actions">
                    <a href="cv-edit.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <a href="cv-preview.php?id=<?= $cv['id'] ?>" class="btn btn-sm btn-secondary" target="_blank">
                        <i class="fas fa-eye"></i> Önizle
                    </a>
                    <a href="cv-download.php?id=<?= $cv['id'] ?>&type=pdf" class="btn btn-sm btn-success">
                        <i class="fas fa-download"></i> PDF
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Recent Scans -->
    <?php if ($scans): ?>
    <div class="section-title mt-2" style="margin-top:40px;">
        <h2><i class="fas fa-search"></i> Son ATS Taramaları</h2>
        <a href="ats-scanner.php" class="btn btn-outline"><i class="fas fa-plus"></i> Yeni Tarama</a>
    </div>
    
    <div class="card">
        <div class="card-body" style="padding:0;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg-light);">
                        <th style="padding:15px; text-align:left;">Dosya</th>
                        <th style="padding:15px; text-align:center;">Skor</th>
                        <th style="padding:15px; text-align:left;">Tarih</th>
                        <th style="padding:15px; text-align:right;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scans as $scan): ?>
                    <tr style="border-top:1px solid var(--border);">
                        <td style="padding:15px;"><?= e($scan['original_filename']) ?></td>
                        <td style="padding:15px; text-align:center;">
                            <span style="background:<?= $scan['score'] >= 70 ? 'var(--success)' : ($scan['score'] >= 50 ? 'var(--warning)' : 'var(--danger)') ?>; color:white; padding:5px 15px; border-radius:20px; font-weight:bold;">
                                <?= $scan['score'] ?>%
                            </span>
                        </td>
                        <td style="padding:15px;"><?= date('d.m.Y H:i', strtotime($scan['scanned_at'])) ?></td>
                        <td style="padding:15px; text-align:right;">
                            <a href="ats-result.php?id=<?= $scan['id'] ?>" class="btn btn-sm btn-outline">Detay</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
