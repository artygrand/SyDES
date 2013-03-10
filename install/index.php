<?php
if (isset($_POST['login']) and isset($_POST['password']) and isset($_POST['code'])){
	@set_time_limit(180);
	require_once '../system/common.php';
	$conf = array(
		'base' => getBase(dirname($_SERVER['PHP_SELF'])),
		'work' => 1,
		'say' => '<!DOCTYPE html>
<html>
 <head>
  <style>
   body {background:#e4d9cf; margin:0; padding:0; height:100%; color:#bf8f5f;text-shadow: 0 1px 2px white;}
   #text {position:absolute; top:50%; left:50%; margin:-51px 0 0 -157px; font-size:1.8em; text-align:center;}
  </style>
 <head>
 <body>
  <div id="text">Нам очень жаль, но<br>сайт временно отключен<br>Приходите позднее</div>
 <body>
</html>',
		'template' => 'default',
		'admin_ip' => array(getip()),
		'locale' => array('ru'),
		'login' => $_POST['login'],
		'pass' => md5($_POST['password']),
		'cookie' => 1,
		'master_code' => md5($_POST['code']),
		'final_template' => array('item', 'article'),
		'need_cache' => 1
	);
	file_put_contents('../system/config.db', serialize($conf));

	$_SESSION['pass'] = md5($conf['pass'] . getip()); 
	$_SESSION['member'] = $_POST['login'];
	setcookie('member', $conf['login'], time()+3600*24*2, '/');
	setcookie('pass', md5($conf['pass'] . getip()), time()+3600*24*2, '/');

	
	if ($_GET['db_driver'] == 'mysql'){
		$port = !empty($_POST['db_port']) ? ';port=' . $_POST['db_port'] : '';
		$db_dsn = 'mysql:dbname='. $_POST['db_name'] . ';host=' . $_POST['db_server'] . $port;
		$user = $_POST['db_user'];
		$pass = $_POST['db_pass'];
	} else {
		$root = substr($_SERVER['DOCUMENT_ROOT'], -1) == '/' ? $_SERVER['DOCUMENT_ROOT'] . 'system/database.db' : $_SERVER['DOCUMENT_ROOT'] . '/system/database.db';
		$db_dsn = 'sqlite:' . $root;
		$user = NULL;
		$pass = NULL;
	}
	$db = false;
	try{
		$db = new PDO($db_dsn, $user, $pass);
		$db->exec('SET NAMES "utf8"');
		$db->exec('SET time_zone = "'. date_default_timezone_get() .'"');
	}
	catch (Exception $e) {die($e->getMessage());}

	$prefix = isset($_POST['table_prefix']) ? $_POST['table_prefix'] : '';
	$schema = file_get_contents('schema_' . $_GET['db_driver'] . '.sql');
	$schema = str_replace('PREFIX_', $prefix, $schema);
	$schema = preg_split('/;(\s*)/', $schema);
	$db -> beginTransaction();
	foreach ($schema as $create_table_sql){
		$create_table_sql = trim($create_table_sql);
		if (!empty($create_table_sql)){
			$db -> exec($create_table_sql);
		}
	}
	
	$dump = file_get_contents('dump.sql');
	$dump = str_replace('prefix_', $prefix, $dump);
	$dump = preg_split('/;(\s*)/', $dump);

	foreach ($dump as $insert_sql){
		$insert_sql = trim($insert_sql);
		if (!empty($insert_sql)){
			$db -> exec($insert_sql);
		}
	}
	$db -> commit();
	header("Location: ../admin/?mod=pages");
} else {
	$langs = array('en', 'ru');
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	$lang = isset($_GET['lang']) ? htmlentities(strtolower($_GET['lang'])): $lang;
	if (!in_array($lang, $langs)) $lang = 'en';
	$l['en'] = array(
		'installation' => 'Installation',
		'login' => 'Login',
		'password' => 'Password',
		'master_code' => 'Master code (very hard)',
		'create' => 'Create',
		'create_admin' => 'Create the admin account',
		'attention' => 'Attention!',
		'chmod' => 'Set chmod to 777 to this folders',
		'pdo_not_supported' => 'PDO is not supported',
		'pdo_sqlite_not_supported' => 'PDO SQLite is not supported',
		'pdo_mysql_not_supported' => 'PDO MySQL is not supported',
		'mod_rewrite_not_supported' => 'mod_rewrite is not supported',
		'install_is_impossible' => 'Installation is impossible<br>configure your server',
		'json_not_supported' => 'json is not supported',
		'other_in_upload' => 'and all folders in "upload" folder'
	);
	$l['ru'] = array(
		'installation' => 'Установка',
		'login' => 'Логин',
		'password' => 'Пароль',
		'master_code' => 'Код разработчика (сложный)',
		'create' => 'Создать',
		'create_admin' => 'Создание нового пользователя',
		'attention' => 'Внимание!',
		'chmod' => 'Установите chmod 777 следующим папкам',
		'pdo_not_supported' => 'PDO не поддерживается',
		'pdo_sqlite_not_supported' => 'PDO SQLite не поддерживается',
		'pdo_mysql_not_supported' => 'PDO MySQL не поддерживается',
		'mod_rewrite_not_supported' => 'mod_rewrite не поддерживается',
		'install_is_impossible' => 'Установка не возможна.<br>Настройте сервер',
		'json_not_supported' => 'json не поддерживается',
		'other_in_upload' => 'а так же все папки в upload'
	);
	
	$files = array('../system', '../system/cache', '../system/iblocks', '../templates', '../upload');
	$wr = '';
	foreach ($files as $file){
		if(!is_writable($file)){
			$wr .= '<li>' . $file . '</li>';
		}
	}
	if ($wr){
		$wr .= '<li>' . $l[$lang]['other_in_upload'] . '</li>';
	}
	
	$req_pdo = class_exists('PDO');
	$pdo_drv = $req_pdo ? PDO::getAvailableDrivers(): array();
	$req_mysql = in_array('mysql', $pdo_drv);
	$req_sqlite = in_array('sqlite', $pdo_drv);
	$req_json = function_exists('json_encode');
	$req_rewrite = in_array('mod_rewrite', apache_get_modules()) ? true: false;

	$page = file_get_contents('template.html');
	
	$errors = '';
	if(!$req_pdo){
		$errors .= '<li>' . $l[$lang]['pdo_not_supported'] . '</li>';
	}
	if(!$req_mysql){
		$errors .= '<li>' . $l[$lang]['pdo_mysql_not_supported'] . '</li>';
	}
	if(!$req_sqlite){
		$errors .= '<li>' . $l[$lang]['pdo_sqlite_not_supported'] . '</li>';
	}
	if(!$req_json){
		$errors .= '<li>' . $l[$lang]['json_not_supported'] . '</li>';
	}
	if(!$req_rewrite){
		$errors .= '<li>' . $l[$lang]['mod_rewrite_not_supported'] . '</li>';
	}
	if ($errors) {
		$page = str_replace('[errors]', $errors, $page);
		$page = str_replace('[status]', $l[$lang]['attention'], $page);
		$page = str_replace('[tip]', $l[$lang]['install_is_impossible'], $page);
		$page = str_replace('[display]', 'none', $page);
	} elseif ($wr){
		$page = str_replace('[errors]', $wr, $page);
		$page = str_replace('[status]', $l[$lang]['attention'], $page);
		$page = str_replace('[tip]', $l[$lang]['chmod'], $page);
		$page = str_replace('[display]', 'none', $page);
	} else {
		$page = str_replace('[errors]', '', $page);
		$page = str_replace('[status]', $l[$lang]['create_admin'], $page);
		$page = str_replace('[tip]', '', $page);
		$page = str_replace('[display]', 'block', $page);
	}
	$search = array('[installation]', '[login]', '[password]', '[master_code]', '[create]');
	$replace = array($l[$lang]['installation'], $l[$lang]['login'], $l[$lang]['password'], $l[$lang]['master_code'], $l[$lang]['create']);
	$page = str_replace($search, $replace, $page);
	echo $page;
}
?>