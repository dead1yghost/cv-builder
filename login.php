<?php
$pageTitle = 'Giriş Yap';
require_once 'config.php';

if (isLoggedIn()) redirect('dashboard');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'E-posta ve şifre gerekli.';
        } else {
            $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login
                $stmt = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                flash('success', 'Hoş geldiniz, ' . $user['full_name'] . '!');
                redirect('dashboard');
            } else {
                $error = 'E-posta veya şifre hatalı.';
            }
        }
    }
}

require_once 'header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-sign-in-alt"></i> Giriş Yap</h1>
        <p>Hesabınıza giriş yapın ve CV'lerinizi yönetin.</p>
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
                            <label class="form-label">E-posta</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?= e($_POST['email'] ?? '') ?>" placeholder="ornek@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Şifre</label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </button>
                    </form>
                    
                    <p class="text-center mt-2">
                        Hesabınız yok mu? <a href="register">Kayıt Olun</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
