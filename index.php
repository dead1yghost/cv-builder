<?php
$pageTitle = 'Ana Sayfa';
require_once 'header.php';
?>

<style>
.hero {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}
.hero h1 { font-size: 2.8rem; margin-bottom: 20px; }
.hero p { font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto 30px; }
.hero-buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
.hero-buttons .btn { padding: 15px 35px; font-size: 1.1rem; }

.features { padding: 80px 0; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-top: 40px; }
.feature-card { background: white; padding: 40px 30px; border-radius: 12px; text-align: center; box-shadow: var(--shadow); transition: 0.3s; }
.feature-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
.feature-card i { font-size: 3rem; color: var(--primary); margin-bottom: 20px; }
.feature-card h3 { margin-bottom: 15px; color: var(--primary-dark); }
.feature-card p { color: var(--text-muted); }

.how-it-works { background: var(--secondary); padding: 80px 0; }
.steps { display: flex; justify-content: center; gap: 40px; margin-top: 40px; flex-wrap: wrap; }
.step { text-align: center; max-width: 250px; }
.step-number { width: 60px; height: 60px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 20px; }
.step h4 { margin-bottom: 10px; }

.cta { background: var(--primary-dark); color: white; padding: 60px 0; text-align: center; }
.cta h2 { margin-bottom: 20px; }
</style>

<section class="hero">
    <div class="container">
        <h1><i class="fas fa-file-alt"></i> ATS Uyumlu CV Oluşturucu</h1>
        <p>Profesyonel, ATS tarayıcı dostu CV'ler oluşturun. İş başvurularınızda öne çıkın!</p>
        <div class="hero-buttons">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> CV Oluştur
                </a>
                <a href="ats-scanner.php" class="btn btn-outline" style="background:white;">
                    <i class="fas fa-search"></i> ATS Skoru Öğren
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i> Ücretsiz Başla
                </a>
                <a href="login.php" class="btn btn-outline" style="background:white;">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="text-center" style="font-size:2rem; margin-bottom:10px;">Neden CV Builder Pro?</h2>
        <p class="text-center text-muted">Profesyonel CV oluşturmanın en kolay yolu</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-robot"></i>
                <h3>ATS Uyumlu</h3>
                <p>Otomatik başvuru takip sistemleri tarafından kolayca okunabilen CV'ler oluşturun.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-palette"></i>
                <h3>Özelleştirilebilir Temalar</h3>
                <p>7 farklı renk teması ile CV'nizi kişiselleştirin. Fotoğraf ekleyin veya çıkarın.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-file-pdf"></i>
                <h3>PDF & DOCX İndir</h3>
                <p>CV'nizi profesyonel PDF veya düzenlenebilir Word formatında indirin.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>ATS Skor Analizi</h3>
                <p>Mevcut CV'nizi yükleyin, ATS skorunu öğrenin ve iyileştirme önerileri alın.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Mobil Uyumlu</h3>
                <p>Telefonunuzdan veya tabletinizden kolayca CV'nizi düzenleyin.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-lock"></i>
                <h3>Güvenli & Gizli</h3>
                <p>Verileriniz güvenle saklanır. İstediğiniz zaman silebilirsiniz.</p>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2 class="text-center" style="font-size:2rem;">Nasıl Çalışır?</h2>
        
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h4>Kayıt Olun</h4>
                <p>Ücretsiz hesap oluşturun ve hemen başlayın.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h4>Bilgilerinizi Girin</h4>
                <p>Deneyim, eğitim ve becerilerinizi ekleyin.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h4>Temayı Seçin</h4>
                <p>Size uygun renk temasını seçin, fotoğraf ekleyin.</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h4>İndirin</h4>
                <p>CV'nizi PDF veya DOCX olarak indirin ve başvurun!</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container">
        <h2>Hemen Profesyonel CV'nizi Oluşturun!</h2>
        <p style="opacity:0.9; margin-bottom:25px;">Ücretsiz, hızlı ve kolay.</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Ücretsiz Kayıt Ol
            </a>
        <?php else: ?>
            <a href="cv-create.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Yeni CV Oluştur
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
