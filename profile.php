<?php
$pageTitle = 'Profil';
require_once 'config.php';
requireLogin();

$user = currentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($fullName) || empty($email)) {
            $error = 'Ad Soyad ve E-posta zorunludur.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi girin.';
        } else {
            // Check if email is already used by another user
            $stmt = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.';
            } else {
                $stmt = db()->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $phone, $_SESSION['user_id']]);
                
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                
                flash('success', 'Profil bilgileriniz güncellendi.');
                redirect('profile');
            }
        }
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPassword2 = $_POST['new_password2'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'Tüm şifre alanlarını doldurun.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Yeni şifre en az 6 karakter olmalı.';
        } elseif ($newPassword !== $newPassword2) {
            $error = 'Yeni şifreler eşleşmiyor.';
        } else {
            // Verify current password
            $stmt = db()->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch();
            
            if (!password_verify($currentPassword, $userData['password'])) {
                $error = 'Mevcut şifreniz hatalı.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = db()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                flash('success', 'Şifreniz başarıyla değiştirildi.');
                redirect('profile');
            }
        }
    }
}

require_once 'header.php';
?>

<style>
.profile-container { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px; }
@media(max-width: 768px) { .profile-container { grid-template-columns: 1fr; } }
.info-box { background: var(--secondary); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.info-box h4 { margin: 0 0 10px; color: var(--primary-dark); }
.info-box p { margin: 5px 0; color: var(--text-muted); }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user-circle"></i> Profil</h1>
        <p>Hesap bilgilerinizi yönetin ve şifrenizi değiştirin.</p>
    </div>
</div>

<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
        <!-- Profile Information -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Profil Bilgileri</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label class="form-label">Ad Soyad *</label>
                            <input type="text" name="full_name" class="form-control" required 
                                   value="<?= e($user['full_name']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">E-posta *</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?= e($user['email']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Telefon</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= e($user['phone'] ?? '') ?>" 
                                   placeholder="+90 5XX XXX XX XX">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Bilgileri Güncelle
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="info-box" style="margin-top: 20px;">
                <h4><i class="fas fa-info-circle"></i> Hesap Bilgileri</h4>
                <p><strong>Kayıt Tarihi:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                <?php if ($user['last_login']): ?>
                <p><strong>Son Giriş:</strong> <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Change Password -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-lock"></i> Şifre Değiştir</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label class="form-label">Mevcut Şifre *</label>
                            <input type="password" name="current_password" class="form-control" required 
                                   placeholder="••••••••">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Yeni Şifre * (min. 6 karakter)</label>
                            <input type="password" name="new_password" class="form-control" required 
                                   minlength="6" placeholder="••••••••">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Yeni Şifre Tekrar *</label>
                            <input type="password" name="new_password2" class="form-control" required 
                                   minlength="6" placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-key"></i> Şifreyi Değiştir
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Güvenlik</h3>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-muted); margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i> Güvenliğiniz için düzenli olarak şifrenizi değiştirmenizi öneririz.
                    </p>
                    <p style="color: var(--text-muted); margin: 0;">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i> Şifreniz güvenli bir şekilde şifrelenerek saklanır.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
