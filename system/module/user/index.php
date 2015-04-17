<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class UserController extends Controller{
	public $name = 'user';

	public function signup(){
		if (file_exists(DIR_SITE . 'config.php')){
			throw new BaseException(t('error_user_already_created'));
		}

		if (isset($this->request->post['username'], $this->request->post['password'], $this->request->post['mastercode'])){
			$this->user->signup();
			$this->response->redirect(ADMIN . '/?route=config/sites/add');
		} else {
			$data = array('signup' => true, 'autologin' => false, 'title' => t('signup'), 'button' => t('create_account'));
			die($this->load->view('user/form', $data));
		}
	}

	public function login(){
		if (!isset($this->request->post['username'], $this->request->post['password']) or !$this->user->login()){
			$data = array('signup' => false, 'autologin' => $this->user->autologin, 'title' => t('signin'), 'button' => t('login'));
			die($this->load->view('user/form', $data));
		}
		$this->response->redirect();
	}

	public function logout(){
		$this->user->logout();
		$this->response->redirect();
	}
}