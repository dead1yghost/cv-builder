# E-posta Yapılandırması (Hostinger SMTP)

## 📧 Şifre Sıfırlama İçin E-posta Ayarları

Bu proje, şifre sıfırlama özelliği için e-posta gönderimi desteklemektedir.

## ⚙️ Yapılandırma

`config.php` dosyasında aşağıdaki ayarları yapın:

### 1. Şifre Sıfırlama Özelliğini Aktif Edin

```php
define('ENABLE_PASSWORD_RESET', true); // Şifre sıfırlama özelliğini aç/kapat
```

### 2. E-posta Ayarlarını Yapılandırın

```php
// Email Configuration (Hostinger SMTP)
define('MAIL_ENABLED', true); // E-posta gönderimi aktif
define('MAIL_HOST', 'smtp.hostinger.com'); // Hostinger SMTP sunucusu
define('MAIL_PORT', 587); // TLS için 587, SSL için 465
define('MAIL_USERNAME', 'noreply@yourdomain.com'); // E-posta adresiniz
define('MAIL_PASSWORD', 'your-email-password'); // E-posta şifreniz
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com'); // Gönderen e-posta
define('MAIL_FROM_NAME', 'CV Builder Pro'); // Gönderen adı
define('MAIL_ENCRYPTION', 'tls'); // 'tls' veya 'ssl'
```

## 🔧 Hostinger E-posta Hesabı Oluşturma

1. **Hostinger Panel'e giriş yapın**
2. **E-postalar** bölümüne gidin
3. **Yeni E-posta Hesabı Oluştur**
4. E-posta adresi oluşturun (örn: `noreply@yourdomain.com`)
5. Güçlü bir şifre belirleyin
6. SMTP bilgilerini not alın:
   - **SMTP Sunucusu:** smtp.hostinger.com
   - **Port:** 587 (TLS) veya 465 (SSL)
   - **Kullanıcı Adı:** Tam e-posta adresiniz
   - **Şifre:** Belirlediğiniz şifre

## 🚀 Kullanım Modları

### Geliştirme Modu (Development)
```php
define('MAIL_ENABLED', false);
```
- E-posta gönderilmez
- Şifre sıfırlama linki ekranda gösterilir
- Test için idealdir

### Üretim Modu (Production)
```php
define('MAIL_ENABLED', true);
```
- Gerçek e-postalar gönderilir
- Kullanıcılar e-posta alır
- Canlı ortam için gereklidir

## 📝 Özellik Kontrolü

### Şifre Sıfırlama Kapalı
```php
define('ENABLE_PASSWORD_RESET', false);
```
- Login sayfasında "Şifremi Unuttum" linki görünmez
- `forgot-password.php` ve `reset-password.php` erişilemez
- Kullanıcılar şifre sıfırlayamaz

### Şifre Sıfırlama Açık
```php
define('ENABLE_PASSWORD_RESET', true);
```
- Login sayfasında "Şifremi Unuttum" linki görünür
- Kullanıcılar şifre sıfırlama talebinde bulunabilir
- E-posta gönderimi `MAIL_ENABLED` ayarına bağlıdır

## 🔐 Güvenlik

- Şifre sıfırlama linkleri **1 saat** geçerlidir
- Her link sadece **bir kez** kullanılabilir
- Tokenler **64 karakter** uzunluğunda ve güvenlidir
- E-posta şifreleri `config.php`'de saklanır (güvenli tutun!)

## 🧪 Test Etme

1. **Geliştirme modunda test:**
   - `MAIL_ENABLED = false` yapın
   - Şifre sıfırlama talebinde bulunun
   - Ekranda çıkan linke tıklayın

2. **Üretim modunda test:**
   - `MAIL_ENABLED = true` yapın
   - Kendi e-postanızla test edin
   - Gelen kutunuzu kontrol edin

## ⚠️ Sorun Giderme

### E-posta gönderilmiyor
- SMTP bilgilerini kontrol edin
- E-posta şifresinin doğru olduğundan emin olun
- Hostinger'da e-posta hesabının aktif olduğunu doğrulayın
- Port numarasını kontrol edin (587 veya 465)

### "Şifremi Unuttum" linki görünmüyor
- `ENABLE_PASSWORD_RESET` değerinin `true` olduğunu kontrol edin

### Token geçersiz hatası
- Linkin 1 saat içinde kullanıldığından emin olun
- Token daha önce kullanılmış olabilir

## 📊 Veritabanı

Şifre sıfırlama için `users` tablosuna eklenen kolonlar:

```sql
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(64) NULL,
ADD COLUMN reset_token_expiry DATETIME NULL;
```

Bu SQL'i `add_reset_columns.sql` dosyasından import edebilirsiniz.

## 💡 İpuçları

- **Üretimde** mutlaka `MAIL_ENABLED = true` yapın
- **Geliştirmede** `MAIL_ENABLED = false` ile test edin
- E-posta şifrelerini **asla** Git'e commit etmeyin
- `config.php` dosyasını `.gitignore`'a ekleyin
- Güvenlik için `noreply@` veya `no-reply@` e-posta kullanın
