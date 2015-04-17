<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ProfileController extends Controller{
	public $name = 'profile';
	
	public function index(){
		$button = '<button type="submit" class="btn btn-primary btn-block">' . t('save') . '</button>';
		$data['content'] = $this->load->view('config/profile-form', array('autologin' => $this->user->autologin));
		$data['sidebar_left'] = $this->getSideMenu('config/profile', array('interface'));
		$data['sidebar_right'] = H::saveButton(DIR_SITE . 'config.php', $button) . $this->user->getMastercodeInput();
		$data['form_url'] = "?route=config/profile/save";
		$data['meta_title'] = t('module_profile');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=config', 'title' => t('settings')),
			array('title' => t('module_profile'))
		));

		$this->response->data = $data;
	}

	public function save(){
		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=config/profile');
		}

		$config = $this->config_admin;
		if ($this->request->post['newusername'] != ''){
			$config['user']['username'] = $this->request->post['newusername'];
		}
		if ($this->request->post['newpassword'] != ''){
			$config['user']['password'] = md5($this->request->post['newpassword']);
		}
		if ($this->request->post['newmastercode'] != ''){
			$config['user']['mastercode'] = md5($this->request->post['newmastercode']);
		}
		$config['user']['autologin'] = $this->request->post['autologin'];

		arr2file($config, DIR_SITE . 'config.php');

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=config/profile');
	}
}