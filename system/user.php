<?php
/**
* SyDES :: user main class file
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class User{
	public function __construct(){
		$this->username = Admin::$config['admin']['username'];
		$this->password = Admin::$config['admin']['password'];
		$this->autologin = Admin::$config['admin']['autologin'];
	}
	
	/**
	* Exit from account
	* @return void
	*/
	public function logout(){
		session_destroy();
		setcookie('user', '');
		setcookie('hash', '');
	}
	
	/**
	* Check authorisation and create session if cookies is defined
	* @return boolean
	*/
	public function isAuthorized(){
		// already logged
		if (isset($_SESSION['user'], $_SESSION['hash']) and $_SESSION['user'] == $this->username and $_SESSION['hash'] == md5($this->password . getip())){
			Admin::$token = $_SESSION['token'];
			return true;
		// login by cookies
		} elseif ($this->autologin == 1 and isset($_COOKIE['user'], $_COOKIE['hash'])){
			if ($_COOKIE['user'] != $this->username or $_COOKIE['hash'] != md5($this->password . getip())){
				Admin::log("{$_COOKIE['user']} is not logged. Wrong cookie");
				$this->logout();
				return false;
			} else {
				$_SESSION['user'] = $_COOKIE['user']; 
				$_SESSION['hash'] = $_COOKIE['hash'];
				Admin::log("{$_COOKIE['user']} is logged by cookie");
				Admin::$token = token(12);
				$_SESSION['token'] = Admin::$token;
				return true;
			}
		// login by password
		} elseif (isset($_POST['username'], $_POST['password']) and strlen($_POST['username']) < 18 and strlen($_POST['password']) < 18){
			if ($_POST['username'] == $this->username and md5($_POST['password']) == $this->password){
				$_SESSION['user'] = $this->username;
				$_SESSION['hash'] = md5($this->password . getip());
				if (isset($_POST['remember']) and $this->autologin == 1){
					setcookie('user', $_SESSION['user'], time()+172800);
					setcookie('hash', $_SESSION['hash'], time()+172800);
				}
				Admin::log("{$_POST['username']} is logged by password");
				Admin::$token = token(12);
				$_SESSION['token'] = Admin::$token;
				return true;
			} else {
				Admin::log("{$_POST['username']} is not logged. {$_POST['password']} - wrong password");
				return false;
			}
		// login as demo-user
		} else {
			return DEMO;
		}
	}

	/**
	* Gets and prints login form
	* @return string
	*/
	public function showLoginForm(){
		$form = render('template/userform.php', array('register' => false, 'autologin' => Admin::$config['admin']['autologin'], 'title' => lang('account_login'), 'button' => lang('login')));
		return $form;
	}
	
	/**
	* Check for ajax token
	* @return boolean
	*/
	public function hasPermission(){
		if (Admin::$mode == 'ajax'){
			if (isset($_GET['ajax'])){
				return ($_GET['ajax'] == $_SESSION['token']);
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	public static function createUser(){
		if (isset($_POST['username'], $_POST['password'], $_POST['mastercode'])){
			$password = md5($_POST['password']);
			Admin::$config['admin'] = array(
				'username' => $_POST['username'],
				'password' => $password ,
				'mastercode' => md5($_POST['mastercode']),
				'autologin' => false,
				'admin_ip' => array(getip())
			);
			Admin::saveConf();
			chmod(SITE_DIR . 'baseconfig.db', 0777);

			$_SESSION['user'] = $_POST['username']; 
			$_SESSION['hash'] = md5($password . getip());
			Admin::$token = token(12);
			$_SESSION['token'] = Admin::$token;
		} else {
			echo render('template/userform.php', array('register' => true, 'autologin' => false, 'title' => lang('signup'), 'button' => lang('signup_now')));
			die;
		}
	}
	
	public static function isMasterActive(){
		if(isset($_POST['mastercode']) and md5($_POST['mastercode']) == Admin::$config['admin']['mastercode']) $_SESSION['master'] = 1;
		return isset($_SESSION['master']);
	}
	
	public static function getMasterInput(){
		return isset($_SESSION['master']) ? '' : '<div class="form-group"><input type="text" name="mastercode" class="form-control" placeholder="' . lang('mastercode') . '" required></div>';
	}
}
?>