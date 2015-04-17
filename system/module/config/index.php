<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ConfigController extends Controller{
	public $name = 'config';
	
	public function index(){
		$data['sidebar_left'] = $this->getSideMenu('config', array('interface'));
		$config = new Config('front');
		$data['content'] = $this->load->view('config/index', array('data' => $config->get()));
		$data['form_url'] = '?route=config/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('settings');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('settings'))
		));

		$this->response->data = $data;
	}

	public function save(){
		$input = $this->request->post['config'];
		if ($input['new_key']['key'][0] != ''){
			foreach($input['new_key']['key'] as $i => $key){
				if ($key == '' or $input['new_key']['value'][$i] == '') continue;
				$input[$key] = $input['new_key']['value'][$i];
			}
		}
		unset($input['new_key']);
		$config = new Config('front');
		$config->set($input)->save();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=config');
	}
}