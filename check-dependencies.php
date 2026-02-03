<?php
/**
 * Dependency Checker
 * Bu dosyayı sunucuda çalıştırarak composer bağımlılıklarını kontrol edin
 */

// Güvenlik: Sadece giriş yapmış kullanıcılar erişebilir
require_once 'config.php';
requireLogin();

header('Content-Type: text/plain; charset=utf-8');

echo "=== CV Builder - Dependency Check ===\n\n";

// 1. Composer autoload kontrolü
$autoloadPath = __DIR__ . '/vendor/autoload.php';
echo "1. Composer Autoload: ";
if (file_exists($autoloadPath)) {
    echo "✅ MEVCUT\n\n";
} else {
    echo "❌ BULUNAMADI\n";
    echo "   Çözüm: SSH ile 'composer install' çalıştırın\n\n";
}

// 2. PDF Parser kütüphanesi kontrolü
echo "2. PDF Parser (Smalot\\PdfParser): ";
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    
    if (class_exists('\\Smalot\\PdfParser\\Parser')) {
        echo "✅ YÜKLENDİ\n";
        
        // Version kontrolü
        $composerLock = __DIR__ . '/composer.lock';
        if (file_exists($composerLock)) {
            $lockData = json_decode(file_get_contents($composerLock), true);
            foreach ($lockData['packages'] ?? [] as $package) {
                if ($package['name'] === 'smalot/pdfparser') {
                    echo "   Version: " . $package['version'] . "\n";
                    break;
                }
            }
        }
        echo "\n";
    } else {
        echo "❌ YÜKLENEMEDİ\n";
        echo "   Autoload var ama sınıf bulunamadı\n\n";
    }
} else {
    echo "⏭️  ATLANADI (autoload yok)\n\n";
}

// 3. Vendor klasörü içeriği
echo "3. Vendor Klasörü: ";
$vendorDir = __DIR__ . '/vendor';
if (is_dir($vendorDir)) {
    echo "✅ MEVCUT\n";
    
    // Smalot klasörü kontrolü
    $smalotDir = $vendorDir . '/smalot';
    if (is_dir($smalotDir)) {
        echo "   └─ smalot/: ✅\n";
        
        $pdfparserDir = $smalotDir . '/pdfparser';
        if (is_dir($pdfparserDir)) {
            echo "      └─ pdfparser/: ✅\n";
        } else {
            echo "      └─ pdfparser/: ❌\n";
        }
    } else {
        echo "   └─ smalot/: ❌\n";
    }
    echo "\n";
} else {
    echo "❌ BULUNAMADI\n\n";
}

// 4. PHP Extensions kontrolü
echo "4. Gerekli PHP Extensions:\n";
$requiredExtensions = ['zip', 'mbstring', 'json'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "   - $ext: " . ($loaded ? "✅" : "❌") . "\n";
}
echo "\n";

// 5. Exec fonksiyonu kontrolü
echo "5. exec() Fonksiyonu: ";
if (function_exists('exec')) {
    echo "✅ KULLANILABILIR (ama kullanmıyoruz)\n\n";
} else {
    echo "❌ DEVRE DIŞI (sorun değil, PHP parser kullanıyoruz)\n\n";
}

// 6. Dosya izinleri
echo "6. Klasör İzinleri:\n";
$dirs = [
    'uploads' => __DIR__ . '/uploads',
    'generated' => __DIR__ . '/generated',
];

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path);
        echo "   - $name/: " . ($writable ? "✅" : "❌") . " (chmod: $perms)\n";
    } else {
        echo "   - $name/: ❌ Klasör yok\n";
    }
}

echo "\n=== Kontrol Tamamlandı ===\n";
echo "\nEğer PDF Parser yüklü değilse:\n";
echo "SSH ile: composer install\n";
