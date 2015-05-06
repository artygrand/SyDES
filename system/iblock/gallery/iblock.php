<?php
/**
* Infoblock: Simple gallery
* Shows list of images from folder, 
* works with lightbox/shadowbox(lightbox[gal])
*
* Usage:
* {iblock:gallery?folder=%dir/subdir%} = dir in /upload/images folder
* {iblock:gallery?perpage=%num%} = limited with paginator
* {iblock:gallery?width=%num%&height=%num%} = sizes of preview
*/

$defaults = array(
	'width' => 150,
	'height' => 150,
	'perpage' => 50,
	'folder' => '',
);
$args = array_merge($defaults, $args);

if (!$args['folder']){
	if (!isset($page['folder'])){
		return;
	}
	$args['folder'] = $page['folder'];
}
if (!is_dir('upload/images/' . $args['folder'])){
	return;
}

$files = glob('upload/images/' . $args['folder'] . '/{*.jpg,*.JPG,*.jpeg,*.gif,*.png}', GLOB_BRACE);
if (empty($files)){
	echo "Folder {$args['folder']} is empty";
	return;
}

$count = count($files);
$skip = (!empty($_GET['skip']) && $_GET['skip'] > 0) ? (int)$_GET['skip'] : 0;
$files = array_slice($files, $skip, $args['perpage']);
?>

<div class="gallery">
<?php foreach ($files as $img){ ?>
	<a href="<?=$img;?>" rel="lightbox[<?=$args['folder'];?>]"><img src="/cache/img/<?=$args['width'];?>_<?=$args['height'];?>_c/<?=$img;?>" alt=""></a>
<?php } ?>
</div>

<?=H::pagination($page['fullpath'], $count, $skip, $args['perpage']);?>