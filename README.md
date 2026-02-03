# CV Builder Pro - ATS Uyumlu CV Oluşturucu

## Özellikler

- ✅ ATS (Applicant Tracking System) uyumlu CV oluşturma
- ✅ 7 farklı renk teması
- ✅ Fotoğraf ekleme seçeneği
- ✅ PDF/Print çıktısı
- ✅ ATS Skor Analizi - CV yükle, skorunu öğren
- ✅ Kullanıcı kayıt/giriş sistemi
- ✅ Mobil uyumlu tasarım
- ✅ Türkçe arayüz

## Kurulum

### 1. Dosyaları Yükle

Tüm dosyaları `cv-builder.isimizcozum.com` subdomain'ine yükleyin.

### 2. Veritabanı Oluştur

phpMyAdmin veya MySQL komut satırından `install.sql` dosyasını çalıştırın:

```bash
mysql -u kullanici -p < install.sql
```

### 3. Konfigürasyon

`config.php` dosyasını düzenleyin:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cv_builder');
define('DB_USER', 'veritabani_kullanici');  // Değiştir
define('DB_PASS', 'veritabani_sifre');      // Değiştir

define('SITE_URL', 'https://cv-builder.isimizcozum.com'); // Domain'inizi yazın
```

### 4. Klasör İzinleri

```bash
chmod 755 uploads/
chmod 755 generated/
```

### 5. Composer Bağımlılıkları

PDF işleme için gerekli kütüphaneyi yükleyin:

```bash
composer install
```

## Git ile Hostinger'a Otomatik Dağıtım

### 1. Hostinger'da Git Ayarları

1. Hostinger hPanel'e giriş yapın
2. **Advanced** > **Git** bölümüne gidin
3. **Create Repository** butonuna tıklayın
4. Repository bilgilerini girin:
   - **Repository URL**: `https://github.com/dead1yghost/cv-builder.git`
   - **Branch**: `main`
   - **Target Path**: `/public_html` (veya subdomain path'i)
5. Deploy key'i kopyalayın

### 2. GitHub'da Deploy Key Ekleme

1. GitHub repo'nuza gidin: https://github.com/dead1yghost/cv-builder
2. **Settings** > **Deploy keys** > **Add deploy key**
3. Hostinger'dan kopyaladığınız key'i yapıştırın
4. **Allow write access** seçeneğini işaretleyin
5. Kaydedin

### 3. İlk Deployment

Hostinger Git panelinde **Pull** butonuna tıklayarak ilk deployment'ı yapın.

### 4. Otomatik Deployment (Webhook Aktif ✅)

Webhook kurulumu tamamlandı! Artık `main` branch'e her push yaptığınızda, Hostinger otomatik olarak değişiklikleri çekip deploy edecek.

Manuel deployment gerekirse Hostinger Git panelinde **Pull** butonunu kullanabilirsiniz.

## Geliştirme İş Akışı (Git Workflow)

Projede iki branch kullanılıyor:
- **`main`**: Production branch - Hostinger'a deploy edilir
- **`develop`**: Development branch - Yerel testler için

### Develop Branch'te Çalışma

```bash
# Develop branch'e geç
git checkout develop

# Değişikliklerini yap ve commit et
git add .
git commit -m "Yeni özellik eklendi"
git push origin develop

# Testler başarılı olduğunda main'e merge et
git checkout main
git merge develop
git push origin main
```

**Webhook sayesinde otomatik olarak Hostinger'a deploy edilecek!** ✅

### 5. Test Et

Tarayıcıdan `https://cv-builder.isimizcozum.com` adresine gidin.

## Dosya Yapısı

```
cv-builder/
├── config.php          # Veritabanı ve site ayarları
├── header.php          # Sayfa başlığı template
├── footer.php          # Sayfa altı template
├── index.php           # Ana sayfa
├── register.php        # Kayıt sayfası
├── login.php           # Giriş sayfası
├── logout.php          # Çıkış
├── dashboard.php       # Kullanıcı paneli
├── cv-create.php       # Yeni CV oluştur
├── cv-edit.php         # CV düzenle
├── cv-preview.php      # CV önizle/yazdır
├── cv-download.php     # CV indir
├── cv-delete.php       # CV sil
├── cv-list.php         # CV listesi
├── ats-scanner.php     # ATS skor analizi
├── install.sql         # Veritabanı şeması
├── .htaccess           # Apache ayarları
├── uploads/            # Kullanıcı yüklemeleri
└── generated/          # Oluşturulan dosyalar
```

## Kullanım

1. Kayıt olun veya giriş yapın
2. "Yeni CV Oluştur" butonuna tıklayın
3. Tema renginizi seçin
4. Kişisel bilgilerinizi, deneyimlerinizi, eğitiminizi ekleyin
5. "Önizle" ile kontrol edin
6. "PDF İndir" ile kaydedin

### ATS Tarayıcı

1. "ATS Tarayıcı" sayfasına gidin
2. Mevcut CV'nizi (PDF/DOC/DOCX) yükleyin
3. ATS uyumluluk skorunuzu ve iyileştirme önerilerini görün

## Gereksinimler

- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- Apache mod_rewrite
- GD Library (fotoğraf işleme için)

## Güvenlik

- CSRF koruması
- Prepared statements (SQL injection koruması)
- XSS koruması (htmlspecialchars)
- Şifre hashleme (password_hash)
- Dosya tipi kontrolü

## Lisans

Bu proje açık kaynak olarak sunulmaktadır. Kişisel ve ticari kullanıma serbesttir.

---

Sabri Cengiz için hazırlanmıştır. 🚀
