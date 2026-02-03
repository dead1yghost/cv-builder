<?php
$pageTitle = 'Şifremi Unuttum';
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
            $resetLink = SITE_URL . "/reset-password?token=" . $token;
            
            // Send email if enabled
            if (MAIL_ENABLED) {
                $emailSubject = 'Şifre Sıfırlama Talebi - ' . SITE_NAME;
                $emailBody = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #2B7A78; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                        .button { display: inline-block; background: #2B7A78; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>' . SITE_NAME . '</h1>
                        </div>
                        <div class="content">
                            <h2>Merhaba ' . htmlspecialchars($user['full_name']) . ',</h2>
                            <p>Şifre sıfırlama talebinde bulundunuz. Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>
                            <p style="text-align: center;">
                                <a href="' . $resetLink . '" class="button">Şifremi Sıfırla</a>
                            </p>
                            <p>Veya aşağıdaki linki tarayıcınıza kopyalayın:</p>
                            <p style="word-break: break-all; background: white; padding: 10px; border-radius: 3px;">' . $resetLink . '</p>
                            <p><strong>Önemli:</strong> Bu link 1 saat geçerlidir.</p>
                            <p>Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>
                        </div>
                        <div class="footer">
                            <p>&copy; ' . date('Y') . ' ' . SITE_NAME . ' - Tüm hakları saklıdır.</p>
                        </div>
                    </div>
                </body>
                </html>
                ';
                
                $mailSent = sendMail($email, $emailSubject, $emailBody);
                
                if ($mailSent) {
                    $success = "Şifre sıfırlama linki e-posta adresinize gönderildi. Lütfen gelen kutunuzu kontrol edin.";
                } else {
                    $success = "Şifre sıfırlama linki oluşturuldu ancak e-posta gönderilemedi. <br><br>
                               <strong>Geliştirme Modu:</strong><br>
                               <a href='$resetLink' style='color: var(--primary);'>$resetLink</a>";
                }
            } else {
                // Development mode - show link
                $success = "Şifre sıfırlama linki oluşturuldu. <br><br>
                           <strong>Geliştirme Modu:</strong><br>
                           <a href='$resetLink' style='color: var(--primary);'>$resetLink</a><br><br>
                           <small style='color: var(--text-muted);'>E-posta gönderimi devre dışı. Config.php'den MAIL_ENABLED'ı aktif edin.</small>";
            }
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
