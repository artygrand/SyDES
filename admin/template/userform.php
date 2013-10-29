<?php
$remeber = $autologin ? '<label><input type="checkbox" name="remember"> ' . lang('remember_me') . '</label>' : ''; /*✓*/
?>
<html>
	<head>
		<title>SyDES :: <?php echo $title;?></title>
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
		</style>
	</head>
	<body>
	<!-- you shall not pass -->
		<form action="" method="post">
			<div class="text">S<span class="red">y</span>DES</div>
<?php
$bagServer = Admin::checkSysReq();
if (!empty($bagServer[0])){
	echo lang('server_not_supported');
	echo "<ul>{$bagServer[0]}</ul>";
} elseif (!empty($bagServer[1])){
	echo lang('need_writable');
	echo "<ul>{$bagServer[1]}</ul>";
	?>
	<div><button type="submit"><?php echo lang('refresh_page')?></button></div>
<?php
} else {
			?>
			<div><input type="text" size="20" name="username" placeholder="<?php echo lang('username');?>" required></div>
			<div><input type="password" size="20" name="password" placeholder="<?php echo lang('password');?>" required></div>
			<?php if($register){?>
			<div><input type="text" size="20" name="mastercode" placeholder="<?php echo lang('mastercode');?>" required></div>
			<div><input type="text" size="20" name="sitename" placeholder="<?php echo lang('site_name');?>" required></div>
			<?php }?>
			<div class="two"><?php echo $remeber;?></div><div class="two last"><button type="submit"><?php echo $button;?></button></div>
<?php }?>
		</form>
	<body>
</html>