<!DOCTYPE html>
<html lang="<?php echo Admin::$lang; /*✓*/?>">
<head>
	<title><?php echo $title;?> :: SyDES</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet">
	<link href="template/css/structure.min.css" rel="stylesheet" media="screen">
	<link href="template/css/skin/<?php echo $skin?>.css" rel="stylesheet" media="screen" id="skin">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.3.0/respond.js"></script>
	<![endif]-->
	<link rel="icon" href="favicon.ico" type="image/x-icon">
<?php if ($cssfiles) echo addFiles('css', $cssfiles);?>
	<style>
	<?php echo $css;?>

	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script src="template/js/hoverintent.min.js"></script>
	<script src="template/js/checkboxrange.min.js"></script>
	<script src="template/js/combobox.min.js"></script>
	<script src="template/js/main.js"></script>
<?php if ($jsfiles) echo addFiles('js', $jsfiles);?>

	<script>
	var token = '<?php echo Admin::$token;?>'
	<?php echo $js;?>

	</script>
</head>
<body>
	<div id="menu" class="gradient">
		<div class="col-xs-12 col-sm-3"><h3 class="sitename"><a href=".." target="_blank" data-toggle="tooltip" data-placement="bottom" title="<?php echo lang('to_site');?>"><?php echo $siteName;?></a></h3></div>
		<div class="col-xs-6 col-sm-3">
			<h3><?php echo lang('content');?></h3>
			<?php echo $pages;?>
		</div>
		<div class="col-xs-6 col-sm-3">
			<h3><?php echo lang('modules');?></h3>
			<?php echo $modules;?>
		</div>
		<div class="col-xs-12 col-sm-3">
			<h3><?php echo lang('settings');?></h3>
			<ul class="list-unstyled row">
				<li class="col-xs-6 col-sm-12"><a href="?mod=config"><?php echo lang('configuration');?></a></li>
				<li class="col-xs-6 col-sm-12"><a href="?mod=templates"><?php echo lang('templates');?></a></li>
				<li class="col-xs-6 col-sm-12"><a href="?mod=iblocks"><?php echo lang('iblocks');?></a></li>
				<li class="col-xs-6 col-sm-12"><a href="?mod=logs"><?php echo lang('logs');?></a></li>
				<li class="col-xs-6 col-sm-12"><a href="?act=logout"><?php echo lang('exit');?></a></li>
			</ul>
		</div>
	</div>
	<div class="main">
		<div class="row undermenu">
			<div class="col-xs-12 col-sm-7">
<?php if ($breadcrumbs){ echo $breadcrumbs; } ?>
			</div>
			<div class="col-xs-12 col-sm-5 text-right">
<?php if ($siteSelect){ echo $siteSelect; } ?>
			</div>
		</div>
<<?php echo $opn;?> class="content">
	<div class="row">
		<?php if ($contentLeft){ ?><div class="col-sm-3 col-lg-2"><?php echo $contentLeft; ?></div><?php } ?>
		<?php if ($contentCenter){ ?><div class="col-sm-<?php echo $sm; ?> col-lg-<?php echo $lg; ?>"><?php echo $contentCenter; ?></div><?php } ?>
		<?php if ($contentRight){ ?><div class="col-sm-3 col-lg-2"><?php echo $contentRight; ?></div><?php } ?>
	</div>
</<?php echo $cls;?>>

	</div>
	<div class="footer">
		<div class="col-sm-3">
<?php if ($footerLeft){ ?>
			<div class="wrap"><?php echo $footerLeft; ?></div>
<?php } ?>
		</div>
		<div class="col-sm-6 text-center">
<?php if ($footerCenter){ ?>
			<div class="wrap"><?php echo $footerCenter; ?></div>
<?php } ?>
		</div>
		<div class="col-sm-3 text-right">
			<div class="wrap">
				<a href="#" class="ajaxmodal pull-left" data-url="?mod=config&act=modal_interface"><span class="glyphicon glyphicon-wrench"></span> <?php echo lang('interface');?></a>
				<a href="http://sydes.artygrand.ru" data-toggle="tooltip" title="<?php echo lang('license');?>">SyDES&nbsp;1.8</a>
			</div>
		</div>
	</div>
	<div id="modal-blank">
		<div class="modal fade" id="modal-bln" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{{title}}</h4>
					</div>
					<div class="modal-body">
						<form id="modal-form-bln" name="modal-form-bln" method="post" action="{{form_url}}">{{content}}</form>
					</div>
					<div class="modal-footer">
						<button type="button" id="modal-save-bln" class="btn btn-primary" data-dismiss="modal"><?php echo lang('save');?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="for-modal"></div>
	<div id="menuclick"></div>
	<ul id="notify"></ul>
</body>
</html>