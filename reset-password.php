<?php
$pageTitle = 'Şifre Sıfırla';
require_once 'config.php';

// Check if password reset is enabled
if (!ENABLE_PASSWORD_RESET) {
    flash('danger', 'Şifre sıfırlama özelliği şu anda devre dışı.');
    redirect('login');
}

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard');
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$validToken = false;

// Verify token
if ($token) {
    $stmt = db()->prepare("SELECT id, full_name, email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $validToken = true;
    } else {
        $error = 'Geçersiz veya süresi dolmuş sıfırlama linki.';
    }
} else {
    $error = 'Sıfırlama token\'ı bulunamadı.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check() && $validToken) {
    $newPassword = $_POST['new_password'] ?? '';
    $newPassword2 = $_POST['new_password2'] ?? '';
    
    if (empty($newPassword)) {
        $error = 'Yeni şifre gereklidir.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($newPassword !== $newPassword2) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        // Update password and clear reset token
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = db()->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        flash('success', 'Şifreniz başarıyla sıfırlandı. Şimdi giriş yapabilirsiniz.');
        redirect('login');
    }
}

require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-lock"></i>
            <h2>Şifre Sıfırla</h2>
            <?php if ($validToken): ?>
                <p>Merhaba <strong><?= e($user['full_name']) ?></strong>, yeni şifrenizi belirleyin.</p>
            <?php endif; ?>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($validToken && !$success): ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key"></i> Yeni Şifre (min. 6 karakter)
                </label>
                <input type="password" name="new_password" class="form-control" required 
                       minlength="6" placeholder="••••••••">
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key"></i> Yeni Şifre Tekrar
                </label>
                <input type="password" name="new_password2" class="form-control" required 
                       minlength="6" placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-check"></i> Şifreyi Sıfırla
            </button>
        </form>
        <?php endif; ?>
        
        <div class="auth-footer">
            <p>
                <a href="login"><i class="fas fa-arrow-left"></i> Giriş sayfasına dön</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
