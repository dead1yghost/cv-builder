<?php
/**
 * CV Builder Pro - Configuration Example
 * Bu dosyayı config.php olarak kopyalayın ve kendi bilgilerinizle doldurun
 */

// Error reporting (production'da kapat)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database - BURAYA KENDİ BİLGİLERİNİ YAZ
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME', 'CV Builder Pro');
define('SITE_URL', 'https://yourdomain.com'); // Kendi domain'ini yaz

// Paths
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('GENERATED_PATH', ROOT_PATH . '/generated/');

// Limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_PHOTO_SIZE', 2 * 1024 * 1024);  // 2MB

// Features
define('ENABLE_PASSWORD_RESET', true); // Şifre sıfırlama özelliğini aktif/pasif yap

// Rate Limiting (Bot Protection)
define('RATE_LIMIT_ENABLED', true); // Rate limiting aktif/pasif
define('RATE_LIMIT_MAX_ATTEMPTS', 3); // Maksimum deneme sayısı
define('RATE_LIMIT_WINDOW', 15); // Dakika cinsinden süre penceresi
define('RATE_LIMIT_COOLDOWN', 60); // Limit aşıldığında bekleme süresi (dakika)

// Email Configuration (Hostinger SMTP)
// Şifre sıfırlama için e-posta gönderimi
define('MAIL_ENABLED', false); // E-posta gönderimi aktif/pasif (üretimde true yapın)
define('MAIL_HOST', 'smtp.hostinger.com'); // SMTP sunucusu
define('MAIL_PORT', 587); // SMTP port (587 veya 465)
define('MAIL_USERNAME', 'noreply@yourdomain.com'); // E-posta adresiniz
define('MAIL_PASSWORD', 'your-email-password'); // E-posta şifreniz
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com'); // Gönderen e-posta
define('MAIL_FROM_NAME', 'CV Builder Pro'); // Gönderen adı
define('MAIL_ENCRYPTION', 'tls'); // 'tls' veya 'ssl'

// Theme Colors
$THEME_COLORS = [
    'teal'   => ['primary' => '#2B7A78', 'secondary' => '#17252A', 'name' => 'Teal'],
    'blue'   => ['primary' => '#2563EB', 'secondary' => '#1E3A8A', 'name' => 'Mavi'],
    'green'  => ['primary' => '#059669', 'secondary' => '#064E3B', 'name' => 'Yeşil'],
    'purple' => ['primary' => '#7C3AED', 'secondary' => '#4C1D95', 'name' => 'Mor'],
    'red'    => ['primary' => '#DC2626', 'secondary' => '#7F1D1D', 'name' => 'Kırmızı'],
    'orange' => ['primary' => '#EA580C', 'secondary' => '#7C2D12', 'name' => 'Turuncu'],
    'gray'   => ['primary' => '#4B5563', 'secondary' => '#1F2937', 'name' => 'Gri'],
];

// Database Connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası. Lütfen config.php dosyasını kontrol edin.");
        }
    }
    return $pdo;
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function flash($type = null, $message = null) {
    if ($type && $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    } elseif (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check() {
    $token = $_POST['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Send email using SMTP configuration
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @return bool Success status
 */
function sendMail($to, $subject, $message) {
    if (!MAIL_ENABLED) {
        return false;
    }
    
    // Configure SMTP settings for mail()
    ini_set('SMTP', MAIL_HOST);
    ini_set('smtp_port', MAIL_PORT);
    
    // Email headers
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>';
    $headers[] = 'Reply-To: ' . MAIL_FROM_EMAIL;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Send email
    $success = mail($to, $subject, $message, implode("\r\n", $headers));
    
    return $success;
}

/**
 * Get user's IP address
 * @return string IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check if rate limit is exceeded for password reset
 * @param string $email Email address
 * @return array ['allowed' => bool, 'message' => string, 'wait_time' => int]
 */
function checkPasswordResetRateLimit($email) {
    if (!RATE_LIMIT_ENABLED) {
        return ['allowed' => true, 'message' => '', 'wait_time' => 0];
    }
    
    $ip = getUserIP();
    $now = date('Y-m-d H:i:s');
    
    // Clean old attempts (older than cooldown period)
    $cleanupTime = date('Y-m-d H:i:s', strtotime('-' . RATE_LIMIT_COOLDOWN . ' minutes'));
    $stmt = db()->prepare("DELETE FROM password_reset_attempts WHERE attempt_time < ?");
    $stmt->execute([$cleanupTime]);
    
    // Check attempts in the time window
    $windowStart = date('Y-m-d H:i:s', strtotime('-' . RATE_LIMIT_WINDOW . ' minutes'));
    
    // Check by IP
    $stmt = db()->prepare("SELECT COUNT(*) as count, MIN(attempt_time) as first_attempt 
                          FROM password_reset_attempts 
                          WHERE ip_address = ? AND attempt_time > ?");
    $stmt->execute([$ip, $windowStart]);
    $ipAttempts = $stmt->fetch();
    
    // Check by email
    $stmt = db()->prepare("SELECT COUNT(*) as count, MIN(attempt_time) as first_attempt 
                          FROM password_reset_attempts 
                          WHERE email = ? AND attempt_time > ?");
    $stmt->execute([$email, $windowStart]);
    $emailAttempts = $stmt->fetch();
    
    $maxAttempts = max($ipAttempts['count'], $emailAttempts['count']);
    
    if ($maxAttempts >= RATE_LIMIT_MAX_ATTEMPTS) {
        $firstAttempt = min($ipAttempts['first_attempt'], $emailAttempts['first_attempt']);
        $waitUntil = strtotime($firstAttempt) + (RATE_LIMIT_WINDOW * 60);
        $waitMinutes = ceil(($waitUntil - time()) / 60);
        
        return [
            'allowed' => false,
            'message' => "Çok fazla deneme yaptınız. Lütfen $waitMinutes dakika sonra tekrar deneyin.",
            'wait_time' => $waitMinutes
        ];
    }
    
    return ['allowed' => true, 'message' => '', 'wait_time' => 0];
}

/**
 * Record password reset attempt
 * @param string $email Email address
 */
function recordPasswordResetAttempt($email) {
    if (!RATE_LIMIT_ENABLED) {
        return;
    }
    
    $ip = getUserIP();
    $now = date('Y-m-d H:i:s');
    
    $stmt = db()->prepare("INSERT INTO password_reset_attempts (ip_address, email, attempt_time) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $email, $now]);
}

// Dizinleri oluştur
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
if (!file_exists(GENERATED_PATH)) mkdir(GENERATED_PATH, 0755, true);
