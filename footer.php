    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> CV Builder Pro - ATS Uyumlu CV Oluşturucu</p>
            <p style="opacity:0.7; margin-top:10px; font-size:0.9rem;">
                <a href="#">Gizlilik Politikası</a> | 
                <a href="#">Kullanım Şartları</a> | 
                <a href="#">İletişim</a>
            </p>
        </div>
    </footer>
    
    <script>
    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Bekleyin...';
            }
        });
    });
    
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    </script>
</body>
</html>
