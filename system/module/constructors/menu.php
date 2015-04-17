<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class MenuController extends Controller{
	public $name = 'menu';
	
	public function index(){
		$data['sidebar_left'] = $this->getSideMenu('constructors/menu');
		$config = new Config('menu');
		$data['content'] = $this->load->view('constructors/menu', array('result' => $config->get()));
		$data['form_url'] = '?route=constructors/menu/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('module_menu');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('title' => t('module_menu'))
		));
		$this->response->data = $data;
		$this->response->script[] = '/system/module/pages/assets/jquery.nestedSortable.js';
	}

	public function save(){
		//$menu = $this->request->post['menu'];
		
		/*
		// do something
		$config = new Config('menu');
		$config->set($menu)->save();
		*/

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/menu');
	}
}