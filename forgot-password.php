<?php
$pageTitle = 'Şifremi Unuttum';
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'E-posta adresi gereklidir.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } else {
        // Check if user exists
        $stmt = db()->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $stmt = db()->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            // Create reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
            
            // In production, send email here
            // For now, show the link (development mode)
            $success = "Şifre sıfırlama linki oluşturuldu. <br><br>
                       <strong>Geliştirme Modu:</strong><br>
                       <a href='$resetLink' style='color: var(--primary);'>$resetLink</a><br><br>
                       <small style='color: var(--text-muted);'>Üretim ortamında bu link e-posta ile gönderilecektir.</small>";
        } else {
            // Don't reveal if email exists or not (security)
            $success = "Eğer bu e-posta adresi sistemde kayıtlıysa, şifre sıfırlama linki gönderilecektir.";
        }
    }
}

require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-key"></i>
            <h2>Şifremi Unuttum</h2>
            <p>E-posta adresinizi girin, size şifre sıfırlama linki gönderelim.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> E-posta Adresi
                </label>
                <input type="email" name="email" class="form-control" required 
                       placeholder="ornek@email.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-paper-plane"></i> Sıfırlama Linki Gönder
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
