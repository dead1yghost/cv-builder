<?php
// cv-preview.php - CV Preview/Print Page
require_once 'config.php';
requireLogin();

$cvId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM cvs WHERE id = ? AND user_id = ?");
$stmt->execute([$cvId, $_SESSION['user_id']]);
$cv = $stmt->fetch();
if (!$cv) die('CV bulunamadı');

$stmt = db()->prepare("SELECT * FROM cv_personal_info WHERE cv_id = ?");
$stmt->execute([$cvId]); $p = $stmt->fetch() ?: [];

$stmt = db()->prepare("SELECT * FROM cv_experience WHERE cv_id = ? ORDER BY start_date DESC");
$stmt->execute([$cvId]); $exps = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_education WHERE cv_id = ? ORDER BY start_date DESC");
$stmt->execute([$cvId]); $edus = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_skills WHERE cv_id = ?");
$stmt->execute([$cvId]); $skills = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_languages WHERE cv_id = ?");
$stmt->execute([$cvId]); $langs = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_projects WHERE cv_id = ?");
$stmt->execute([$cvId]); $projs = $stmt->fetchAll();

$stmt = db()->prepare("SELECT * FROM cv_interests WHERE cv_id = ?");
$stmt->execute([$cvId]); $ints = $stmt->fetchAll();

$colors = $THEME_COLORS[$cv['theme_color']] ?? $THEME_COLORS['teal'];
$primary = $colors['primary'];
$secondary = $colors['secondary'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title><?=e($p['full_name'] ?? 'CV')?> - CV</title>
<style>
@page{margin:10mm 15mm}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;font-size:10pt;line-height:1.4;color:#333}
.container{max-width:800px;margin:0 auto;padding:20px}
.header{border-bottom:2px solid <?=$primary?>;padding-bottom:15px;margin-bottom:20px}
.name{font-size:24pt;font-weight:bold;color:<?=$secondary?>}
.title{font-size:11pt;color:<?=$primary?>;margin:5px 0}
.contact{font-size:9pt;color:#666}
.contact span{margin-right:15px}
.two-col{display:flex;gap:30px}
.left{flex:2}.right{flex:1;min-width:180px}
.section{margin-bottom:20px}
.section-title{font-size:12pt;font-weight:bold;color:<?=$secondary?>;border-bottom:1.5px solid <?=$primary?>;padding-bottom:3px;margin-bottom:12px}
.entry{margin-bottom:15px}
.entry-title{font-weight:bold}
.entry-sub{color:<?=$primary?>;font-weight:bold;font-size:9pt}
.entry-date{color:#999;font-size:8pt;margin-bottom:5px}
.entry-desc{font-size:9pt;color:#444}
.entry-desc ul{margin-left:15px;margin-top:5px}
.skills-cat{font-weight:bold;font-size:9pt;margin-bottom:3px}
.skills-list{font-size:9pt;color:#666;margin-bottom:10px}
.summary{font-size:9pt;color:#444;line-height:1.5}
.photo{width:100px;height:100px;border-radius:50%;object-fit:cover;float:right;margin-left:20px}
.print-btn{position:fixed;top:20px;right:20px;background:<?=$primary?>;color:#fff;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-size:1rem;z-index:1000}
@media print{.print-btn{display:none}}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨️ Yazdır / PDF</button>
<div class="container">
<div class="header">
<?php if($cv['show_photo'] && $cv['photo_path']):?>
<img src="uploads/<?=e($cv['photo_path'])?>" class="photo" alt="Fotoğraf">
<?php endif;?>
<div class="name"><?=strtoupper(e($p['full_name'] ?? ''))?></div>
<?php if($p['title']):?><div class="title"><?=e($p['title'])?></div><?php endif;?>
<div class="contact">
<?php if($p['phone']):?><span>📞 <?=e($p['phone'])?></span><?php endif;?>
<?php if($p['email']):?><span>✉️ <?=e($p['email'])?></span><?php endif;?>
<?php if($p['city']):?><span>📍 <?=e($p['city'])?></span><?php endif;?>
<?php if($p['linkedin']):?><span>🔗 <?=e($p['linkedin'])?></span><?php endif;?>
</div>
</div>

<div class="two-col">
<div class="left">
<?php if($exps):?>
<div class="section">
<div class="section-title">EXPERIENCE</div>
<?php foreach($exps as $e):?>
<div class="entry">
<div class="entry-title"><?=e($e['job_title'])?></div>
<div class="entry-sub"><?=e($e['company'])?></div>
<div class="entry-date"><?=e($e['start_date'])?> - <?=$e['is_current']?'Present':e($e['end_date'])?><?php if($e['location']):?> | <?=e($e['location'])?><?php endif;?></div>
<?php if($e['description']):?><div class="entry-desc"><?=nl2br(e($e['description']))?></div><?php endif;?>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<?php if($projs):?>
<div class="section">
<div class="section-title">PROJECTS</div>
<?php foreach($projs as $pr):?>
<div class="entry">
<div class="entry-title"><?=e($pr['project_name'])?></div>
<?php if($pr['technologies']):?><div class="entry-sub"><?=e($pr['technologies'])?></div><?php endif;?>
<?php if($pr['url']):?><div class="entry-date"><?=e($pr['url'])?></div><?php endif;?>
<?php if($pr['description']):?><div class="entry-desc"><?=e($pr['description'])?></div><?php endif;?>
</div>
<?php endforeach;?>
</div>
<?php endif;?>
</div>

<div class="right">
<?php if($p['summary']):?>
<div class="section">
<div class="section-title">SUMMARY</div>
<div class="summary"><?=nl2br(e($p['summary']))?></div>
</div>
<?php endif;?>

<?php if($skills):?>
<div class="section">
<div class="section-title">SKILLS</div>
<?php 
$grouped = [];
foreach($skills as $s) $grouped[$s['category'] ?: 'General'][] = $s['skill_name'];
foreach($grouped as $cat => $list):?>
<div class="skills-cat"><?=e($cat)?></div>
<div class="skills-list"><?=e(implode(', ', $list))?></div>
<?php endforeach;?>
</div>
<?php endif;?>

<?php if($langs):?>
<div class="section">
<div class="section-title">LANGUAGES</div>
<?php foreach($langs as $l):?>
<div style="margin-bottom:8px"><strong style="font-size:9pt"><?=e($l['language_name'])?></strong><br><span style="font-size:8pt;color:#666"><?=e(ucfirst($l['proficiency']))?></span></div>
<?php endforeach;?>
</div>
<?php endif;?>

<?php if($edus):?>
<div class="section">
<div class="section-title">EDUCATION</div>
<?php foreach($edus as $ed):?>
<div class="entry">
<div class="entry-title" style="font-size:9pt"><?=e($ed['school'])?></div>
<div class="entry-date"><?=e(trim($ed['degree'].' '.$ed['field_of_study']))?></div>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<?php if($ints):?>
<div class="section">
<div class="section-title">INTERESTS</div>
<div style="font-size:9pt;color:#666"><?=e(implode(', ', array_column($ints, 'interest')))?></div>
</div>
<?php endif;?>

<div class="section">
<div class="section-title">PERSONAL INFO</div>
<div style="font-size:9pt">
<?php if($p['birth_year']):?><div><b>Birth:</b> <?=e($p['birth_year'])?></div><?php endif;?>
<?php if($p['nationality']):?><div><b>Nationality:</b> <?=e($p['nationality'])?></div><?php endif;?>
<?php if($p['military_status']):?><div><b>Military:</b> <?=e($p['military_status'])?></div><?php endif;?>
<?php if($p['driving_license']):?><div><b>License:</b> <?=e($p['driving_license'])?></div><?php endif;?>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
