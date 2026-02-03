<?php
$pageTitle = 'Kayıt Ol';
require_once 'config.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($email) || empty($password) || empty($fullName)) {
            $error = 'Tüm zorunlu alanları doldurun.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi girin.';
        } elseif (strlen($password) < 6) {
            $error = 'Şifre en az 6 karakter olmalı.';
        } elseif ($password !== $password2) {
            $error = 'Şifreler eşleşmiyor.';
        } else {
            // Check if email exists
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Bu e-posta adresi zaten kayıtlı.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = db()->prepare("INSERT INTO users (email, password, full_name, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $fullName, $phone]);
                
                flash('success', 'Kayıt başarılı! Giriş yapabilirsiniz.');
                redirect('login.php');
            }
        }
    }
}

require_once 'header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Kayıt Ol</h1>
        <p>Ücretsiz hesap oluşturun ve profesyonel CV'nizi oluşturmaya başlayın.</p>
    </div>
</div>

<div class="container">
    <div class="row" style="justify-content: center;">
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="full_name" class="form-control" required 
                                   value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="Adınız Soyadınız">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">E-posta *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?= e($_POST['email'] ?? '') ?>" placeholder="ornek@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+90 5XX XXX XX XX">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Şifre * (min. 6 karakter)</label>
                            <input type="password" name="password" class="form-control" required 
                                   minlength="6" placeholder="••••••••">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Şifre Tekrar *</label>
                            <input type="password" name="password2" class="form-control" required 
                                   minlength="6" placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </button>
                    </form>
                    
                    <p class="text-center mt-2">
                        Zaten hesabınız var mı? <a href="login.php">Giriş Yapın</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
