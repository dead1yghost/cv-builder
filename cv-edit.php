<?php
$pageTitle = 'CV Düzenle';
require_once 'config.php';
requireLogin();

$cvId = (int)($_GET['id'] ?? 0);
if (!$cvId) redirect('dashboard.php');

$stmt = db()->prepare("SELECT * FROM cvs WHERE id = ? AND user_id = ?");
$stmt->execute([$cvId, $_SESSION['user_id']]);
$cv = $stmt->fetch();
if (!$cv) { flash('danger', 'CV bulunamadı.'); redirect('dashboard.php'); }

// Get CV data
$stmt = db()->prepare("SELECT * FROM cv_personal_info WHERE cv_id = ?");
$stmt->execute([$cvId]); $personal = $stmt->fetch() ?: [];

$stmt = db()->prepare("SELECT * FROM cv_experience WHERE cv_id = ? ORDER BY start_date DESC");
$stmt->execute([$cvId]); $experiences = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_education WHERE cv_id = ? ORDER BY start_date DESC");
$stmt->execute([$cvId]); $educations = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_skills WHERE cv_id = ?");
$stmt->execute([$cvId]); $skills = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_languages WHERE cv_id = ?");
$stmt->execute([$cvId]); $languages = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_projects WHERE cv_id = ?");
$stmt->execute([$cvId]); $projects = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_interests WHERE cv_id = ?");
$stmt->execute([$cvId]); $interests = $stmt->fetchAll();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_personal') {
        $stmt = db()->prepare("UPDATE cv_personal_info SET full_name=?, title=?, email=?, phone=?, city=?, birth_year=?, linkedin=?, summary=?, nationality=?, military_status=?, driving_license=? WHERE cv_id=?");
        $stmt->execute([$_POST['full_name'],$_POST['title'],$_POST['email'],$_POST['phone'],$_POST['city'],$_POST['birth_year']?:null,$_POST['linkedin'],$_POST['summary'],$_POST['nationality'],$_POST['military_status'],$_POST['driving_license'],$cvId]);
        flash('success','Kaydedildi!'); redirect("cv-edit?id=$cvId");
    }
    if ($action === 'save_settings') {
        $stmt = db()->prepare("UPDATE cvs SET title=?, theme_color=?, show_photo=? WHERE id=?");
        $stmt->execute([$_POST['title'],$_POST['theme_color'],isset($_POST['show_photo'])?1:0,$cvId]);
        if(isset($_FILES['photo'])&&$_FILES['photo']['error']===0){
            $ext=pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION);
            $fn="photo_{$cvId}_".time().".$ext";
            move_uploaded_file($_FILES['photo']['tmp_name'],UPLOAD_PATH.$fn);
            db()->prepare("UPDATE cvs SET photo_path=? WHERE id=?")->execute([$fn,$cvId]);
        }
        flash('success','Ayarlar kaydedildi!'); redirect("cv-edit.php?id=$cvId&tab=settings");
    }
    if ($action === 'add_experience') {
        $stmt = db()->prepare("INSERT INTO cv_experience (cv_id,job_title,company,location,start_date,end_date,is_current,description) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$cvId,$_POST['job_title'],$_POST['company'],$_POST['location'],$_POST['start_date']?:null,$_POST['end_date']?:null,isset($_POST['is_current'])?1:0,$_POST['description']]);
        redirect("cv-edit.php?id=$cvId&tab=experience");
    }
    if ($action === 'delete_experience') {
        db()->prepare("DELETE FROM cv_experience WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=experience");
    }
    if ($action === 'add_education') {
        $stmt = db()->prepare("INSERT INTO cv_education (cv_id,school,degree,field_of_study,start_date,end_date) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$cvId,$_POST['school'],$_POST['degree'],$_POST['field_of_study'],$_POST['start_date']?:null,$_POST['end_date']?:null]);
        redirect("cv-edit.php?id=$cvId&tab=education");
    }
    if ($action === 'delete_education') {
        db()->prepare("DELETE FROM cv_education WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=education");
    }
    if ($action === 'add_skill') {
        db()->prepare("INSERT INTO cv_skills (cv_id,category,skill_name) VALUES (?,?,?)")->execute([$cvId,$_POST['category'],$_POST['skill_name']]);
        redirect("cv-edit.php?id=$cvId&tab=skills");
    }
    if ($action === 'delete_skill') {
        db()->prepare("DELETE FROM cv_skills WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=skills");
    }
    if ($action === 'add_language') {
        db()->prepare("INSERT INTO cv_languages (cv_id,language_name,proficiency) VALUES (?,?,?)")->execute([$cvId,$_POST['language_name'],$_POST['proficiency']]);
        redirect("cv-edit.php?id=$cvId&tab=languages");
    }
    if ($action === 'delete_language') {
        db()->prepare("DELETE FROM cv_languages WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=languages");
    }
    if ($action === 'add_project') {
        db()->prepare("INSERT INTO cv_projects (cv_id,project_name,technologies,url,description) VALUES (?,?,?,?,?)")->execute([$cvId,$_POST['project_name'],$_POST['technologies'],$_POST['url'],$_POST['description']]);
        redirect("cv-edit.php?id=$cvId&tab=projects");
    }
    if ($action === 'delete_project') {
        db()->prepare("DELETE FROM cv_projects WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=projects");
    }
    if ($action === 'add_interest') {
        db()->prepare("INSERT INTO cv_interests (cv_id,interest) VALUES (?,?)")->execute([$cvId,$_POST['interest']]);
        redirect("cv-edit.php?id=$cvId&tab=interests");
    }
    if ($action === 'delete_interest') {
        db()->prepare("DELETE FROM cv_interests WHERE id=? AND cv_id=?")->execute([$_POST['item_id'],$cvId]);
        redirect("cv-edit.php?id=$cvId&tab=interests");
    }
}

$tab = $_GET['tab'] ?? 'personal';
require_once 'header.php';
?>
<style>
.editor{display:grid;grid-template-columns:220px 1fr;gap:25px;margin-top:20px}
.sidebar{background:#fff;border-radius:12px;box-shadow:var(--shadow);overflow:hidden;position:sticky;top:90px;height:fit-content}
.sidebar a{display:flex;align-items:center;gap:10px;padding:14px 18px;text-decoration:none;color:var(--text-dark);border-left:4px solid transparent}
.sidebar a:hover{background:var(--bg-light)}
.sidebar a.active{background:var(--secondary);border-left-color:var(--primary);font-weight:600;color:var(--primary)}
.tab{display:none}.tab.active{display:block}
.item{background:var(--bg-light);padding:15px 20px;border-radius:8px;margin-bottom:12px;position:relative}
.item h4{margin:0 0 5px}.item p{margin:0;color:var(--text-muted);font-size:.9rem}
.item .del{position:absolute;top:10px;right:10px;background:var(--danger);color:#fff;border:none;width:28px;height:28px;border-radius:50%;cursor:pointer}
.tags{display:flex;flex-wrap:wrap;gap:8px}
.tag{background:var(--secondary);padding:6px 14px;border-radius:20px;display:flex;align-items:center;gap:6px;font-size:.9rem}
.tag button{background:none;border:none;color:var(--danger);cursor:pointer;font-size:1.1rem}
.colors{display:flex;gap:10px;flex-wrap:wrap}
.colors div{width:40px;height:40px;border-radius:50%;cursor:pointer;border:3px solid transparent}
.colors div.sel{border-color:#333}
@media(max-width:768px){.editor{grid-template-columns:1fr}.sidebar{display:flex;flex-wrap:wrap}.sidebar a{flex:1;min-width:100px;border-left:none;border-bottom:3px solid transparent;justify-content:center}.sidebar a.active{border-bottom-color:var(--primary)}}
</style>

<div class="page-header">
<div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px">
<div><h1><i class="fas fa-edit"></i> <?=e($cv['title'])?></h1></div>
<div style="display:flex;gap:10px">
<a href="cv-preview.php?id=<?=$cvId?>" class="btn btn-secondary" target="_blank"><i class="fas fa-eye"></i> Önizle</a>
<a href="cv-download.php?id=<?=$cvId?>&type=pdf" class="btn btn-success"><i class="fas fa-download"></i> PDF</a>
</div>
</div>
</div>

<div class="container">
<div class="editor">
<nav class="sidebar">
<a href="?id=<?=$cvId?>&tab=personal" class="<?=$tab==='personal'?'active':''?>"><i class="fas fa-user"></i> Kişisel</a>
<a href="?id=<?=$cvId?>&tab=experience" class="<?=$tab==='experience'?'active':''?>"><i class="fas fa-briefcase"></i> Deneyim</a>
<a href="?id=<?=$cvId?>&tab=education" class="<?=$tab==='education'?'active':''?>"><i class="fas fa-graduation-cap"></i> Eğitim</a>
<a href="?id=<?=$cvId?>&tab=skills" class="<?=$tab==='skills'?'active':''?>"><i class="fas fa-tools"></i> Beceriler</a>
<a href="?id=<?=$cvId?>&tab=languages" class="<?=$tab==='languages'?'active':''?>"><i class="fas fa-language"></i> Diller</a>
<a href="?id=<?=$cvId?>&tab=projects" class="<?=$tab==='projects'?'active':''?>"><i class="fas fa-project-diagram"></i> Projeler</a>
<a href="?id=<?=$cvId?>&tab=interests" class="<?=$tab==='interests'?'active':''?>"><i class="fas fa-heart"></i> İlgi Alanları</a>
<a href="?id=<?=$cvId?>&tab=settings" class="<?=$tab==='settings'?'active':''?>"><i class="fas fa-cog"></i> Ayarlar</a>
</nav>

<div class="main">
<!-- Personal -->
<div class="tab <?=$tab==='personal'?'active':''?>">
<div class="card"><div class="card-header"><h3>Kişisel Bilgiler</h3></div><div class="card-body">
<form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="save_personal">
<div class="row">
<div class="col-6"><div class="form-group"><label class="form-label">Ad Soyad *</label><input type="text" name="full_name" class="form-control" required value="<?=e($personal['full_name']??'')?>"></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Ünvan</label><input type="text" name="title" class="form-control" value="<?=e($personal['title']??'')?>" placeholder="Senior Developer"></div></div>
</div>
<div class="row">
<div class="col-6"><div class="form-group"><label class="form-label">E-posta</label><input type="email" name="email" class="form-control" value="<?=e($personal['email']??'')?>"></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Telefon</label><input type="tel" name="phone" class="form-control" value="<?=e($personal['phone']??'')?>"></div></div>
</div>
<div class="row">
<div class="col-6"><div class="form-group"><label class="form-label">Şehir</label><input type="text" name="city" class="form-control" value="<?=e($personal['city']??'')?>"></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Doğum Yılı</label><input type="number" name="birth_year" class="form-control" value="<?=e($personal['birth_year']??'')?>"></div></div>
</div>
<div class="form-group"><label class="form-label">LinkedIn</label><input type="text" name="linkedin" class="form-control" value="<?=e($personal['linkedin']??'')?>"></div>
<div class="form-group"><label class="form-label">Özet</label><textarea name="summary" class="form-control" rows="4"><?=e($personal['summary']??'')?></textarea></div>
<div class="row">
<div class="col-4"><div class="form-group"><label class="form-label">Uyruk</label><input type="text" name="nationality" class="form-control" value="<?=e($personal['nationality']??'')?>"></div></div>
<div class="col-4"><div class="form-group"><label class="form-label">Askerlik</label><input type="text" name="military_status" class="form-control" value="<?=e($personal['military_status']??'')?>"></div></div>
<div class="col-4"><div class="form-group"><label class="form-label">Ehliyet</label><input type="text" name="driving_license" class="form-control" value="<?=e($personal['driving_license']??'')?>"></div></div>
</div>
<button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Kaydet</button>
</form>
</div></div>
</div>

<!-- Experience -->
<div class="tab <?=$tab==='experience'?'active':''?>">
<div class="card"><div class="card-header"><h3>İş Deneyimi</h3></div><div class="card-body">
<?php foreach($experiences as $e): ?>
<div class="item">
<form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_experience"><input type="hidden" name="item_id" value="<?=$e['id']?>"><button type="submit" class="del" onclick="return confirm('Sil?')"><i class="fas fa-times"></i></button></form>
<h4><?=e($e['job_title'])?></h4><p><b><?=e($e['company'])?></b> - <?=e($e['location'])?></p><p><?=e($e['start_date'])?> - <?=$e['is_current']?'Devam':e($e['end_date'])?></p>
</div>
<?php endforeach; ?>
<hr style="margin:25px 0"><h4><i class="fas fa-plus"></i> Yeni Ekle</h4>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_experience">
<div class="row"><div class="col-6"><div class="form-group"><label class="form-label">Pozisyon *</label><input type="text" name="job_title" class="form-control" required></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Şirket *</label><input type="text" name="company" class="form-control" required></div></div></div>
<div class="row"><div class="col-4"><div class="form-group"><label class="form-label">Konum</label><input type="text" name="location" class="form-control"></div></div>
<div class="col-4"><div class="form-group"><label class="form-label">Başlangıç</label><input type="date" name="start_date" class="form-control"></div></div>
<div class="col-4"><div class="form-group"><label class="form-label">Bitiş</label><input type="date" name="end_date" class="form-control"><label style="margin-top:5px"><input type="checkbox" name="is_current"> Devam ediyor</label></div></div></div>
<div class="form-group"><label class="form-label">Açıklama</label><textarea name="description" class="form-control" rows="3"></textarea></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Education -->
<div class="tab <?=$tab==='education'?'active':''?>">
<div class="card"><div class="card-header"><h3>Eğitim</h3></div><div class="card-body">
<?php foreach($educations as $e): ?>
<div class="item">
<form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_education"><input type="hidden" name="item_id" value="<?=$e['id']?>"><button type="submit" class="del"><i class="fas fa-times"></i></button></form>
<h4><?=e($e['school'])?></h4><p><?=e($e['degree'])?> - <?=e($e['field_of_study'])?></p>
</div>
<?php endforeach; ?>
<hr style="margin:25px 0"><h4><i class="fas fa-plus"></i> Yeni Ekle</h4>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_education">
<div class="form-group"><label class="form-label">Okul *</label><input type="text" name="school" class="form-control" required></div>
<div class="row"><div class="col-6"><div class="form-group"><label class="form-label">Derece</label><input type="text" name="degree" class="form-control"></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Bölüm</label><input type="text" name="field_of_study" class="form-control"></div></div></div>
<div class="row"><div class="col-6"><div class="form-group"><label class="form-label">Başlangıç</label><input type="date" name="start_date" class="form-control"></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">Bitiş</label><input type="date" name="end_date" class="form-control"></div></div></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Skills -->
<div class="tab <?=$tab==='skills'?'active':''?>">
<div class="card"><div class="card-header"><h3>Beceriler</h3></div><div class="card-body">
<div class="tags mb-2">
<?php foreach($skills as $s): ?>
<span class="tag"><?=e($s['skill_name'])?> <small>(<?=e($s['category']?:'Genel')?>)</small>
<form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_skill"><input type="hidden" name="item_id" value="<?=$s['id']?>"><button type="submit">&times;</button></form></span>
<?php endforeach; ?>
</div>
<form method="POST" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
<input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_skill">
<div class="form-group" style="margin:0"><label class="form-label">Kategori</label><input type="text" name="category" class="form-control" style="width:140px" placeholder="Backend"></div>
<div class="form-group" style="margin:0"><label class="form-label">Beceri *</label><input type="text" name="skill_name" class="form-control" required style="width:180px" placeholder="PHP"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Languages -->
<div class="tab <?=$tab==='languages'?'active':''?>">
<div class="card"><div class="card-header"><h3>Diller</h3></div><div class="card-body">
<?php foreach($languages as $l): ?>
<div class="item" style="display:flex;justify-content:space-between;align-items:center">
<div><h4 style="margin:0"><?=e($l['language_name'])?></h4><p><?=e(ucfirst($l['proficiency']))?></p></div>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_language"><input type="hidden" name="item_id" value="<?=$l['id']?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</div>
<?php endforeach; ?>
<form method="POST" style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;align-items:flex-end">
<input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_language">
<div class="form-group" style="margin:0"><label class="form-label">Dil *</label><input type="text" name="language_name" class="form-control" required placeholder="İngilizce"></div>
<div class="form-group" style="margin:0"><label class="form-label">Seviye</label><select name="proficiency" class="form-control"><option value="basic">Başlangıç</option><option value="intermediate" selected>Orta</option><option value="advanced">İleri</option><option value="native">Anadil</option></select></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Projects -->
<div class="tab <?=$tab==='projects'?'active':''?>">
<div class="card"><div class="card-header"><h3>Projeler</h3></div><div class="card-body">
<?php foreach($projects as $p): ?>
<div class="item">
<form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_project"><input type="hidden" name="item_id" value="<?=$p['id']?>"><button type="submit" class="del"><i class="fas fa-times"></i></button></form>
<h4><?=e($p['project_name'])?></h4><p><?=e($p['technologies'])?></p><?php if($p['url']):?><p><a href="<?=e($p['url'])?>" target="_blank"><?=e($p['url'])?></a></p><?php endif;?>
</div>
<?php endforeach; ?>
<hr style="margin:25px 0"><h4><i class="fas fa-plus"></i> Yeni Ekle</h4>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_project">
<div class="row"><div class="col-6"><div class="form-group"><label class="form-label">Proje Adı *</label><input type="text" name="project_name" class="form-control" required></div></div>
<div class="col-6"><div class="form-group"><label class="form-label">URL</label><input type="text" name="url" class="form-control"></div></div></div>
<div class="form-group"><label class="form-label">Teknolojiler</label><input type="text" name="technologies" class="form-control" placeholder="PHP, MySQL, JS"></div>
<div class="form-group"><label class="form-label">Açıklama</label><textarea name="description" class="form-control" rows="2"></textarea></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Interests -->
<div class="tab <?=$tab==='interests'?'active':''?>">
<div class="card"><div class="card-header"><h3>İlgi Alanları</h3></div><div class="card-body">
<div class="tags mb-2">
<?php foreach($interests as $i): ?>
<span class="tag"><?=e($i['interest'])?>
<form method="POST" style="display:inline"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="delete_interest"><input type="hidden" name="item_id" value="<?=$i['id']?>"><button type="submit">&times;</button></form></span>
<?php endforeach; ?>
</div>
<form method="POST" style="display:flex;gap:10px;align-items:flex-end">
<input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="add_interest">
<div class="form-group" style="margin:0;flex:1"><label class="form-label">İlgi Alanı</label><input type="text" name="interest" class="form-control" required placeholder="Fotoğrafçılık"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ekle</button>
</form>
</div></div>
</div>

<!-- Settings -->
<div class="tab <?=$tab==='settings'?'active':''?>">
<div class="card"><div class="card-header"><h3>CV Ayarları</h3></div><div class="card-body">
<form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?=csrf_token()?>"><input type="hidden" name="action" value="save_settings">
<div class="form-group"><label class="form-label">CV Başlığı</label><input type="text" name="title" class="form-control" value="<?=e($cv['title'])?>"></div>
<div class="form-group"><label class="form-label">Tema Rengi</label>
<div class="colors">
<?php foreach($THEME_COLORS as $k=>$c): ?>
<div style="background:<?=$c['primary']?>" class="<?=$cv['theme_color']===$k?'sel':''?>" data-c="<?=$k?>" onclick="selC('<?=$k?>')"></div>
<?php endforeach; ?>
</div>
<input type="hidden" name="theme_color" id="tc" value="<?=e($cv['theme_color'])?>">
</div>
<div class="form-group"><label><input type="checkbox" name="show_photo" <?=$cv['show_photo']?'checked':''?>> Fotoğraf göster</label></div>
<div class="form-group"><label class="form-label">Fotoğraf</label>
<?php if($cv['photo_path']):?><div style="margin-bottom:10px"><img src="uploads/<?=e($cv['photo_path'])?>" style="max-width:120px;border-radius:8px"></div><?php endif;?>
<input type="file" name="photo" class="form-control" accept="image/*">
</div>
<button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Kaydet</button>
</form>
<hr style="margin:30px 0">
<a href="cv-delete?id=<?=$cvId?>" class="btn btn-danger" onclick="return confirm('CV silinsin mi?')"><i class="fas fa-trash"></i> CV'yi Sil</a>
</div></div>
</div>

</div>
</div>
</div>
<script>function selC(c){document.querySelectorAll('.colors div').forEach(e=>e.classList.remove('sel'));document.querySelector('[data-c="'+c+'"]').classList.add('sel');document.getElementById('tc').value=c;}</script>
<?php require_once 'footer.php'; ?>
