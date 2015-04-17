<!DOCTYPE html>
<html lang="<?=$language;?>">
<head>
	<title><?=$meta_title;?> <?=$site_name;?> :: SyDES</title>
	<meta charset="utf-8">
	<meta name="generator" content="SyDES">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
	<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" rel="stylesheet">
	<link href="/system/assets/css/structure.css" rel="stylesheet" media="screen">
	<link href="/system/assets/css/skin.<?=$skin?>.css" rel="stylesheet" media="screen" id="skin">

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<link rel="icon" href="favicon.ico" type="image/x-icon">

<? foreach($styles as $file){ ?>
	<link href="<?=$file;?>" rel="stylesheet" media="screen">
<? } ?>

	<script>var token = '<?=$token;?>'</script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="/vendor/ckfinder/ckfinder.js"></script>
	<script src="/system/assets/js/utils.min.js"></script>
	<script src="/system/assets/js/main.js"></script>

<? foreach($scripts as $file){ ?>
	<script src="<?=$file;?>"></script>
<? } ?>

</head>
<body class="menu-<?=$menu_pos;?>">
	<div id="menu" class="gradient">
	<div class="menu-wrapper">
		<div class="menu-section">
			<div><a href="<?=$base;?>" target="_blank" data-toggle="tooltip" data-placement="right" title="<?=t('tip_to_site');?>"><?=$site_name;?></a></div>
		</div>
		<div class="menu-section">
			<div><?=t('content');?></div>
			<?=$page_types;?>
		</div>
		<div class="menu-section">
			<div><?=t('modules');?></div>
			<?=$modules;?>
		</div>
<? foreach($menu_sections as $section){ ?>
		<div class="menu-section">
			<div><?=$section['title'];?></div>
			<?=$section['list'];?>
		</div>
<? } ?>
		<div class="menu-section">
			<div><?=t('system');?></div>
			<ul class="list-unstyled">
				<li><a href="#" onclick="CKFinder.popup({basePath:'../vendor/ckfinder/', selectActionFunction:toBuffer})"><?=t('file_manager');?></a></li>
				<li><a href="?route=config"><?=t('settings');?></a></li>
				<li><a href="?route=constructors"><?=t('module_constructors');?></a></li>
				<li><a href="?route=tools"><?=t('module_tools');?></a></li>
				<li><a href="?route=templates"><?=t('templates');?></a></li>
				<li><a href="?route=iblocks"><?=t('iblocks');?></a></li>
				<li><a href="?route=common/logs"><?=t('logs');?></a></li>
				<li><a href="?route=user/logout"><?=t('exit');?></a></li>
			</ul>
		</div>
	</div>
	</div>
	<div id="main" class="container-fluid">
		<div class="row top">
			<div class="col-sm-7"><?=$breadcrumbs;?></div>
			<div class="col-sm-5 text-right">
				<div class="context-menu">
				<? foreach($context_menu as $item){ ?>
					<? if ($item['link']){ ?>
					<a href="<?=$item['link'];?>"><?=$item['title'];?></a>
					<? } else { ?>
					<div class="btn-group">
						<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown"><?=$item['title']?> <span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
						<? foreach($item['children'] as $child){ ?>
							<li><?=$child?></li>
						<? } ?>
						</ul>
					</div>
					<? } ?>
				<? } ?>
				</div>
			</div>
		</div>

		<div id="alerts">
		<? foreach($alerts as $a){ ?>
			<div class="alert alert-<?=$a['status']?> alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button><?=$a['message']?></div>
		<? } ?>
		</div>

		<div class="row" id="workarea">
		<? if ($form_url){ ?><form name="main-form" method="post" enctype="multipart/form-data" action="<?=$form_url;?>"><? } ?>
			<? if ($sidebar_left){ ?><div class="col-sm-3 col-lg-2 sidebar-left"><?=$sidebar_left;?></div><? } ?>
			<? if ($content){ ?><div class="col-sm-<?=$col_sm;?> col-lg-<?=$col_lg;?> content"><?=$content;?></div><? } ?>
			<? if ($sidebar_right){ ?><div class="col-sm-3 col-lg-2 sidebar-right"><?=$sidebar_right;?></div><? } ?>
		<? if ($form_url){ ?></form><? } ?>
		</div>
	</div>
	<div id="footer" class="container-fluid">
		<div class="row">
			<div class="col-sm-3">
				<?=$footer_left;?>
			</div>
			<div class="col-sm-6 text-center">
				<?=$footer_center;?>
			</div>
			<div class="col-sm-3 text-right">
				<a href="http://sydes.ru" data-toggle="tooltip" title="<?=t('tip_license');?>">SyDES&nbsp;<?=$version;?></a>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content"></div>
		</div>
	</div>

	<div id="menuclick"></div>
	<ul id="notify"></ul>
</body>
</html>