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
		$id = isset($this->request->get['id']) ? $this->request->get['id'] : 0;
		$menus = $config->get();
		if (!$menus){
			$menus = array();
		}
		$data['content'] = $this->load->view('constructors/menu', array(
			'menus' => $menus,
			'menu_id' => $id,
		));
		$data['form_url'] = '?route=constructors/menu/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('module_menu');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('title' => t('module_menu'))
		));

		$this->response->data = $data;
		$this->response->script[] = '/system/assets/js/jquery.nestedSortable.js';
	}

	public function save(){
		foreach($this->request->post['item']['level'] as $id => $level){
			$items[$id] = array(
				'level' => $level,
				'title' => $this->request->post['item']['title'][$id],
				'attr_title' => $this->request->post['item']['attr_title'][$id],
				'fullpath' => $this->request->post['item']['fullpath'][$id],
				'id' => $id,
			);
		}

		$menu = array(
			'title' => $this->request->post['title'],
			'items' => $items,
		);
		$menu_id = (int)$this->request->post['id'];

		$config = new Config('menu');
		$menus = $config->get();
		if ($menu_id == 0){
			if ($menus){
				$menus[] = $menu;
			} else {
				$menus[1] = $menu;
			}
		} else {
			$menus[$menu_id] = $menu;
		}

		$config->set($menus)->save();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/menu&id=' . $menu_id);
	}

	public function delete(){
		$id = (int)$this->request->get['id'];
		$config = new Config('menu');
		$menus = $config->get();
		if (isset($menus[$id])){
			$this->confirm(sprintf(t('confirm_want_to_delete'), t('module_menu') . ' ' . $menus[$id]['title']), '?route=constructors/menu&id=' . $id);

			unset($menus[$id]);
			$config->set($menus)->save();
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=constructors/menu');
	}
}