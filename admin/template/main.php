<!DOCTYPE html>
<html lang="<?php echo Admin::$lang;?>">
<head>
	<title><?php echo $title;?> :: <?php echo lang('admin');?> SyDES</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href="template/css/bootstrap.min.css" rel="stylesheet" media="screen">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.3.0/respond.js"></script>
	<![endif]-->
	<link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
	<div class="container">
		<h1><?php echo $title;?></h1>
		<?php echo $code;?>
		

	</div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
	<script src="template/js/bootstrap.min.js"></script>
</body>
</html>