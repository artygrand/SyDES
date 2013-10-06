<?php $remeber = Admin::$config['admin']['autologin'] ? '<label><input type="checkbox" name="remember"> ' . lang('remember_me') . '</label>' : '';?>
<html>
	<head>
		<title>SyDES :: <?php echo lang('account_login');?></title>
		<style>
			html, body, form{height:100%;}
			body{background:#fff;margin:0;padding:0;text-align:center;font:normal 14px/20px Arial;color:#fff;}
			form{margin:0 auto;width:320px;padding:0 20px;background:#2C313A;overflow:hidden;}
			.text{margin:250px 0 20px;font-size:30px;}
			div>input{width:100%;padding:10px;margin-bottom:10px;border:none;}
			button{width:150px;padding:10px;background:#EA4848;cursor:pointer;border:none;font-size:16px;color:#fff;}
			button:hover{background:#F36767}
			.two{display:inline-block;width:50%;text-align:left;}
			.two.last{text-align:right;}
			.red{color:#EA4848;}
		</style>
	</head>
	<body>
	<!-- you shall not pass -->
		<form action="" method="post">
			<div class="text">S<span class="red">y</span>DES</div>
			<div><input type="text" size="20" name="username" placeholder="<?php echo lang('username');?>" required></div>
			<div><input type="password" size="20" name="password" placeholder="<?php echo lang('password');?>" required></div>
			<div class="two"><?php echo $remeber;?></div><div class="two last"><button type="submit"><?php echo lang('login');?></button></div>
		</form>
	<body>
</html>