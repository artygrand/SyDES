<?php
/**
* SyDES :: user main class file
* @version 1.8
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
			return true;
		// login by cookies
		} elseif ($this->autologin == 1 and isset($_COOKIE['user'], $_COOKIE['hash'])){
			if ($_COOKIE['user'] != $this->username or $_COOKIE['hash'] != md5($this->password . getip())){
				Admin::log($_COOKIE['user'] . ': ' . lang('Access denied: wrong cookies'));
				$this->logout();
				return false;
			} else {
				$_SESSION['user'] = $_COOKIE['user']; 
				$_SESSION['hash'] = $_COOKIE['hash'];
				Admin::log($_COOKIE['user'] . ': ' . lang('Access granted: used cookies'));
				Admin::$token = token(12);
				$_SESSION['token'] = Admin::$token;
				return true;
			}
		// login by password
		} elseif (isset($_POST['username'], $_POST['password'])){
			if ($_POST['username'] == $this->username and md5($_POST['password']) == $this->password){
				$_SESSION['user'] = $this->username;
				$_SESSION['hash'] = md5($this->password . getip());
				if (isset($_POST['remember']) and $this->autologin == 1){
					setcookie('user', $_SESSION['user'], time()+172800);
					setcookie('hash', $_SESSION['hash'], time()+172800);
				}
				Admin::log($_POST['username'] . ': ' . lang('Access granted: used password'));
				Admin::$token = token(12);
				$_SESSION['token'] = Admin::$token;
				return true;
			} else {
				Admin::log($_POST['username'] . ': ' . $_POST['password'] . ': ' . lang('Access denied: wrong password'));
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
		$form = render('template/loginform.php');
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
}
?>