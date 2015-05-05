<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class User{
	public $is_editor = false;
	public $post;

	public function __construct(){
		$this->post = Registry::getInstance()->request->post;
	}

	public function set(array $user){
		foreach ($user as $k => $v){
			$this->$k = $v;
		}
	}

	public function signup(){
		$this->username = $this->post['username'];
		$this->password = md5($this->post['password']);
		$array = array(
		'user' => array(
			'username' => $this->username,
			'password' => $this->password,
			'mastercode' => md5($this->post['mastercode']),
			'autologin' => 0,
		));
		$file = DIR_SITE . 'config.php';
		arr2file($array, $file);
		$this->login();
	}

	public function logout(){
		session_destroy();
		setcookie('user', '');
		setcookie('hash', '');
	}

	public function login(){
		if (strlen($this->post['username']) < 20 and strlen($this->post['password']) < 20){
			if ($this->post['username'] == $this->username and md5($this->post['password']) == $this->password){
				$_SESSION['user'] = $this->username; 
				$_SESSION['hash'] = md5($this->password . getip());
				$_SESSION['token'] = $this->token = token(12);
				elog($this->post['username'] . ' is logged on with a password');
				if (isset($this->post['remember']) and $this->autologin == 1){
					setcookie('user', $_SESSION['user'], time()+172800);
					setcookie('hash', $_SESSION['hash'], time()+172800);
				}
				setcookie('is_admin', '1', time()+604800, '/');
				return true;
			} else {
				elog("{$this->post['username']} is not logged on. {$this->post['password']} - wrong password");
			}
		}
		return false;
	}

	public function isLoggedIn(){
		// already logged in
		if (isset($_SESSION['user'], $_SESSION['hash'])){
			if ($_SESSION['user'] == $this->username and $_SESSION['hash'] == md5($this->password . getip())){
				$this->token = $_SESSION['token'];
				return true;
			} else {
				$this->logout();
			}
		// login by cookies
		} elseif ($this->autologin == 1 and isset($_COOKIE['user'], $_COOKIE['hash'])){
			if ($_COOKIE['user'] == $this->username and $_COOKIE['hash'] == md5($this->password . getip())){
				$_SESSION['user'] = $_COOKIE['user']; 
				$_SESSION['hash'] = $_COOKIE['hash'];
				$_SESSION['token'] = $this->token = token(12);
				elog($_COOKIE['user'] . ' is logged on with a cookie');
				return true;
			} else {
				elog($_COOKIE['user'] . ' is not logged on. Wrong cookie');
				$this->logout();
			}
		}
		return false;
	}

	public function hasToken(){
		return (isset($this->post['token']) and $this->post['token'] == $_SESSION['token']);
	}

	public function isAdmin(){
		if (isset($this->post['mastercode']) and md5($this->post['mastercode']) == $this->mastercode){
			$_SESSION['mastercode'] = 1;
		}
		return isset($_SESSION['mastercode']);
	}

	public function getMastercodeInput(){
		return isset($_SESSION['mastercode']) ? '' : '<div class="form-group"><input type="text" name="mastercode" class="form-control" placeholder="' . t('mastercode') . '" required></div>';
	}
}