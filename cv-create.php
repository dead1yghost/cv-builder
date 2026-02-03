<?php
$pageTitle = 'Yeni CV Oluştur';
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $title = trim($_POST['title'] ?? 'Yeni CV');
    $themeColor = $_POST['theme_color'] ?? 'teal';
    
    try {
        db()->beginTransaction();
        
        // Create CV
        $stmt = db()->prepare("INSERT INTO cvs (user_id, title, theme_color) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $themeColor]);
        $cvId = db()->lastInsertId();
        
        // Create empty personal info
        $user = currentUser();
        $stmt = db()->prepare("INSERT INTO cv_personal_info (cv_id, full_name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cvId, $user['full_name'], $user['email'], $user['phone']]);
        
        db()->commit();
        
        flash('success', 'CV oluşturuldu! Şimdi bilgilerinizi ekleyin.');
        redirect("cv-edit.php?id=$cvId");
    } catch (Exception $e) {
        db()->rollBack();
        flash('danger', 'CV oluşturulurken bir hata oluştu.');
    }
}

require_once 'header.php';
?>

<style>
.color-picker { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px; }
.color-option { width: 50px; height: 50px; border-radius: 50%; cursor: pointer; border: 4px solid transparent; transition: 0.3s; position: relative; }
.color-option:hover { transform: scale(1.1); }
.color-option.selected { border-color: var(--text-dark); }
.color-option.selected::after { content: '✓'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 1.2rem; }
.color-label { display: block; text-align: center; font-size: 0.8rem; margin-top: 5px; color: var(--text-muted); }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Yeni CV Oluştur</h1>
        <p>CV'niz için temel ayarları belirleyin.</p>
    </div>
</div>

<div class="container">
    <div class="row" style="justify-content: center;">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-cog"></i> CV Ayarları</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="form-group">
                            <label class="form-label">CV Başlığı (sadece sizin göreceğiniz)</label>
                            <input type="text" name="title" class="form-control" 
                                   value="Yeni CV" placeholder="Örn: Yazılım Mühendisi CV">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tema Rengi</label>
                            <div class="color-picker">
                                <?php foreach ($THEME_COLORS as $key => $color): ?>
                                <div>
                                    <div class="color-option <?= $key === 'green' ? 'selected' : '' ?>" 
                                         style="background: <?= $color['primary'] ?>;"
                                         data-color="<?= $key ?>"
                                         onclick="selectColor('<?= $key ?>')"></div>
                                    <span class="color-label"><?= $color['name'] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="theme_color" id="theme_color" value="green">
                        </div>
                        
                        <div class="d-flex gap-2" style="margin-top:30px;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Devam Et
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary btn-lg">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectColor(color) {
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
    document.querySelector('[data-color="' + color + '"]').classList.add('selected');
    document.getElementById('theme_color').value = color;
}
</script>

<?php require_once 'footer.php'; ?>
