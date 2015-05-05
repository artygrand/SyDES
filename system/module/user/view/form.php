<html>
	<head>
		<title><?=$title;?> :: SyDES</title>
		<style>
			html, body, form{height:100%;}
			body{background:#fff;margin:0;padding:0;text-align:center;font:normal 14px/20px Arial;color:#fff;}
			form{margin:0 auto;width:320px;padding:0 20px;background:#2C313A;overflow:hidden;}
			.text{margin:250px 0 20px;font-size:30px;}
			div>input{width:100%;padding:10px;margin-bottom:10px;border:none;}
			button{min-width:150px;padding:10px;background:#EA4848;cursor:pointer;border:none;font-size:16px;color:#fff;}
			button:hover{background:#F36767}
			.two{display:inline-block;width:50%;text-align:left;}
			.two.last{text-align:right;}
			.red{color:#EA4848;}
			ul{text-align:left;}
			label{cursor:pointer;}
		</style>
	</head>
	<body>
	<!-- you shall not pass -->
		<form action="" method="post">
			<div class="text">S<span class="red">y</span>DES</div>
<?php $errors = checkServer();
if (!empty($errors[0])){
	echo t('server_not_supported');
	echo "<ul>{$errors[0]}</ul>";
} elseif (!empty($errors[1])){
	echo t('need_writable');
	echo "<ul>{$errors[1]}</ul>";
	?>
	<div><button type="submit"><?=t('refresh_page')?></button></div>
<?php } else { ?>
			<div><input type="text" size="20" name="username" placeholder="<?=t('username');?>" required></div>
			<div><input type="password" size="20" name="password" placeholder="<?=t('password');?>" required></div>
			<?php if($signup){ ?>
			<div><input type="text" size="20" name="mastercode" placeholder="<?=t('mastercode');?>" required></div>
			<?php } ?>
			<div class="two"><?php if($autologin){?>
			<label><input type="checkbox" name="remember"> <?=t('remember_me')?></label>
			<?php } ?></div><div class="two last"><button type="submit"><?=$button;?></button></div>
<?php } ?>
		</form>
	<body>
</html>