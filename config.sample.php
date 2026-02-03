<?php
/**
 * CV Builder Pro - Configuration
 * Düzenle: Veritabanı bilgilerini kendi sunucuna göre ayarla
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
define('DB_USER', 'your_database_user');  // Değiştir
define('DB_PASS', 'your_database_password');      // Değiştir
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME', 'CV Builder Pro');
define('SITE_URL', 'https://your-domain.com'); // Kendi domain'ini yaz

// Paths
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('GENERATED_PATH', ROOT_PATH . '/generated/');

// Limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_PHOTO_SIZE', 2 * 1024 * 1024);  // 2MB

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

// Dizinleri oluştur
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
if (!file_exists(GENERATED_PATH)) mkdir(GENERATED_PATH, 0755, true);
