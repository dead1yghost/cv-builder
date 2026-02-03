<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'CV Builder Pro') ?> - ATS Uyumlu CV Oluşturucu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --primary: #2B7A78;
        --primary-dark: #17252A;
        --primary-light: #3AAFA9;
        --secondary: #DEF2F1;
        --text-dark: #17252A;
        --text-light: #FEFFFF;
        --text-muted: #6c757d;
        --bg-light: #f8f9fa;
        --border: #dee2e6;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #ffc107;
        --shadow: 0 2px 10px rgba(0,0,0,0.1);
        --radius: 8px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-dark); line-height: 1.6; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    
    /* Navbar */
    .navbar { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: var(--shadow); }
    .navbar .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .navbar-brand { color: var(--text-light); font-size: 1.4rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
    .navbar-brand:hover { color: var(--secondary); }
    .navbar-nav { display: flex; list-style: none; gap: 10px; align-items: center; flex-wrap: wrap; }
    .navbar-nav a { color: var(--text-light); text-decoration: none; padding: 8px 16px; border-radius: var(--radius); transition: 0.3s; }
    .navbar-nav a:hover { background: rgba(255,255,255,0.15); }
    .navbar-nav .btn-nav { background: var(--primary-light); }
    
    /* Buttons */
    .btn { display: inline-block; padding: 10px 20px; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 500; cursor: pointer; text-decoration: none; text-align: center; transition: 0.3s; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
    .btn-secondary { background: var(--secondary); color: var(--primary-dark); }
    .btn-success { background: var(--success); color: white; }
    .btn-danger { background: var(--danger); color: white; }
    .btn-outline { background: transparent; border: 2px solid var(--primary); color: var(--primary); }
    .btn-outline:hover { background: var(--primary); color: white; }
    .btn-lg { padding: 15px 30px; font-size: 1.1rem; }
    .btn-sm { padding: 6px 12px; font-size: 0.875rem; }
    .btn-block { display: block; width: 100%; }
    
    /* Cards */
    .card { background: white; border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; }
    .card-header { background: var(--secondary); padding: 20px; border-bottom: 1px solid var(--border); }
    .card-header h3 { margin: 0; color: var(--primary-dark); }
    .card-body { padding: 25px; }
    
    /* Forms */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 500; }
    .form-control { width: 100%; padding: 12px 15px; border: 2px solid var(--border); border-radius: var(--radius); font-size: 1rem; transition: 0.3s; }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(43,122,120,0.2); }
    textarea.form-control { min-height: 100px; resize: vertical; }
    
    /* Alerts */
    .alert { padding: 15px 20px; border-radius: var(--radius); margin-bottom: 20px; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    
    /* Page Header */
    .page-header { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; padding: 40px 0; margin-bottom: 30px; }
    .page-header h1 { margin: 0 0 10px; font-size: 2rem; }
    .page-header p { margin: 0; opacity: 0.9; }
    
    /* Grid */
    .row { display: flex; flex-wrap: wrap; margin: -15px; }
    .col { padding: 15px; }
    .col-6 { width: 50%; }
    .col-4 { width: 33.333%; }
    .col-8 { width: 66.666%; }
    .col-12 { width: 100%; }
    @media (max-width: 768px) { .col-6, .col-4, .col-8 { width: 100%; } }
    
    /* Utilities */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-muted); }
    .mt-2 { margin-top: 20px; }
    .mb-2 { margin-bottom: 20px; }
    .d-flex { display: flex; }
    .gap-2 { gap: 20px; }
    .flex-wrap { flex-wrap: wrap; }
    
    /* Footer */
    .footer { background: var(--primary-dark); color: var(--text-light); padding: 30px 0; margin-top: 60px; text-align: center; }
    .footer a { color: var(--secondary); }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">
                <i class="fas fa-file-alt"></i> CV Builder Pro
            </a>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="cv-list"><i class="fas fa-file-alt"></i> CV'lerim</a></li>
                    <li><a href="ats-scanner"><i class="fas fa-search"></i> ATS Tarayıcı</a></li>
                    <li><a href="logout"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                <?php else: ?>
                    <li><a href="login"><i class="fas fa-sign-in-alt"></i> Giriş</a></li>
                    <li><a href="register" class="btn-nav"><i class="fas fa-user-plus"></i> Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <main>
    <?php if ($flash = flash()): ?>
        <div class="container" style="margin-top:20px;">
            <div class="alert alert-<?= $flash['type'] ?>" id="flashMessage">
                <button type="button" style="float:right;background:none;border:none;font-size:1.5rem;cursor:pointer;opacity:0.6;line-height:1" onclick="this.parentElement.parentElement.remove()">&times;</button>
                <?= $flash['message'] ?>
            </div>
        </div>
    <?php endif; ?>
