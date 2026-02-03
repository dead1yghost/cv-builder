<?php
$pageTitle = "CV'lerim";
require_once 'config.php';
requireLogin();

$stmt = db()->prepare("
    SELECT c.*, p.full_name as cv_name, p.title as cv_title 
    FROM cvs c LEFT JOIN cv_personal_info p ON c.id = p.cv_id 
    WHERE c.user_id = ? ORDER BY c.updated_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cvs = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
<div class="container">
<h1><i class="fas fa-file-alt"></i> CV'lerim</h1>
<p>Tüm CV'lerinizi buradan yönetebilirsiniz.</p>
</div>
</div>

<div class="container">
<div style="display:flex;justify-content:flex-end;margin-bottom:20px">
<a href="cv-create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Yeni CV Oluştur</a>
</div>

<?php if(empty($cvs)):?>
<div class="card"><div class="card-body text-center" style="padding:60px">
<i class="fas fa-file-alt" style="font-size:4rem;color:var(--text-muted);margin-bottom:20px"></i>
<h3>Henüz CV oluşturmadınız</h3>
<p class="text-muted">İlk CV'nizi oluşturmak için butona tıklayın.</p>
<a href="cv-create.php" class="btn btn-primary btn-lg mt-2"><i class="fas fa-plus"></i> CV Oluştur</a>
</div></div>
<?php else:?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:25px">
<?php foreach($cvs as $cv):?>
<div class="card">
<div style="height:120px;background:linear-gradient(135deg,<?=e($THEME_COLORS[$cv['theme_color']]['primary']??'#2B7A78')?>22,<?=e($THEME_COLORS[$cv['theme_color']]['primary']??'#2B7A78')?>44);display:flex;align-items:center;justify-content:center">
<i class="fas fa-file-alt" style="font-size:3rem;color:<?=e($THEME_COLORS[$cv['theme_color']]['primary']??'#2B7A78')?>"></i>
</div>
<div class="card-body">
<h4 style="margin:0 0 5px"><?=e($cv['cv_name']?:$cv['title'])?></h4>
<p class="text-muted" style="font-size:.9rem;margin:0 0 15px"><?=e($cv['cv_title']?:'Başlık belirtilmemiş')?><br>
<small>Son güncelleme: <?=date('d.m.Y H:i',strtotime($cv['updated_at']))?></small></p>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="cv-edit.php?id=<?=$cv['id']?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Düzenle</a>
<a href="cv-preview.php?id=<?=$cv['id']?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-eye"></i></a>
<a href="cv-download.php?id=<?=$cv['id']?>&type=pdf" class="btn btn-sm btn-success"><i class="fas fa-download"></i></a>
<a href="cv-delete.php?id=<?=$cv['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="fas fa-trash"></i></a>
</div>
</div>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
</div>

<?php require_once 'footer.php';?>
