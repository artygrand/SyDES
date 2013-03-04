<?php
if (isset($_POST['login']) and isset($_POST['password'])){
	if ($_POST['login'] === Core::$config['login'] and md5($_POST['password']) === Core::$config['pass']){
		//add a session
		$_SESSION['pass'] = md5(Core::$config['pass'] . getip());
		$_SESSION['member'] = Core::$config['login'];
		if (Core::$config['cookie'] == 1){
			//don't forgot me if you can
			setcookie('member', Core::$config['login'], time()+3600*24*2);
			setcookie('pass', md5(Core::$config['pass'] . getip()), time()+3600*24*2);
		}
		$Core -> logAccess($_POST['login'], '****', 'Access granted');
		header('Location:' . $_SERVER['REQUEST_URI']);
		
	}else{
		$Core -> logAccess($_POST['login'], $_POST['password'], 'Access denied');
	}
}
//you shall not pass
echo '<html>
	<head>
		<title>SyDES :: '.lang('log_in').'</title>
		<style>
			body{background:#e4d9cf;margin:0;padding:0;text-align:center;}
			form{margin:10px auto 0 auto;width:300px;padding:12px;border:1px solid #b79576;border-radius:10px;background:#E6D3C0;box-shadow: 0 0 0 1px #F7F0EC inset;}
			p{margin: 0;}
			p:nth-child(2n+1){margin:10px 0 0 0;}
			p:nth-child(5){margin-bottom:10px;}
			input, button{font-size:14px;width:280px;padding:10px;border-radius:5px;border:1px solid #BBB;box-shadow:0 1px 0 #EEE;}
			.text{margin:250px auto 0 auto;font-size:1.8em;}
			button{background:#D0BAAF;border:1px solid #b79576;cursor:pointer;box-shadow: 0 0 0 1px rgba(255, 255, 255, .4) inset;}
			.red{color:red;}
		</style>
	</head>
	<body>
		<div class="text">S<span class="red">y</span>DES</div>
		<form action="" method="post">
			<p><input type="text" size="20" name="login" placeholder="'.lang('login').'"></p>
			<p><input type="password" size="20" name="password" placeholder="'.lang('password').'"></p>
			<p><button type="submit">' . lang('signup') . '</button></p>
		</form>
	<body>
</html>';
?>