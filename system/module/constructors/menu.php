<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class MenuController extends Controller{
	public $name = 'menu';

	public function __construct(){
		parent::__construct();

		if ($this->section == 'admin'){
			if (count($this->config_site['locales']) > 1){
				$this->addContextMenu('locale', $this->locale);
				foreach ($this->config_site['locales'] as $locale){
					$this->addToContextMenu('locale', array(
						'title' => $locale,
						'link' => '?route=constructors/menu&locale=' . $locale
					));
				}
			}
		}
	}

	public function index(){
		$config = new Config('menu');
		$menus = $config->get();
		if (!$menus){
			$menus = array();
		}

		$this->response->data = array(
			'content' => $this->load->view('constructors/menu-list', array(
				'menus' => $menus,
				'locale' => $this->locale,
			)),
			'sidebar_left' => $this->getSideMenu('constructors/menu'),
			'meta_title' => t('module_menu'),
			'breadcrumbs' => H::breadcrumb(array(
				array('url' => '?route=constructors', 'title' => t('module_constructors')),
				array('title' => t('module_menu'))
			)),
		);
	}

	public function edit(){
		$data = array();
		$data['sidebar_left'] = $this->getSideMenu('constructors/menu');
		$config = new Config('menu');
		$id = isset($this->request->get['id']) ? $this->request->get['id'] : 0;

		$menu = $config->get($id.':'.$this->locale);

		$data['content'] = $this->load->view('constructors/menu-form', array(
			'menu' => $menu,
			'menu_id' => $id,
		));
		$data['form_url'] = '?route=constructors/menu/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('module_menu');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('url' => '?route=constructors/menu', 'title' => t('module_menu')),
			array('title' => t('editing'))
		));

		$this->response->data = $data;
		$this->response->script[] = '/system/assets/js/jquery.nestedSortable.js';
		$this->response->script[] = '/system/module/constructors/assets/menu.js';

		$this->response->addJsL10n(array(
			'add_one_item' => t('add_one_item'),
		));
	}

	public function add(){
		$menu = array(
			'title' => $this->request->post['title'],
			'items' => array(),
		);
		$config = new Config('menu');
		$menus = $config->get();

		if ($menus){
			$count = count($menus)+1;
			$menus[$count.':'.$this->locale] = $menu;
		} else {
			$menus['1:'.$this->locale] = $menu;
		}

		$config->set($menus)->save();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/menu/edit&id=' . count($menus));
	}

	public function save(){
		$items = array();
		foreach ($this->request->post['item']['level'] as $id => $level){
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
		$menus[$menu_id.':'.$this->locale] = $menu;
		$config->set($menus)->save();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/menu/edit&id=' . $menu_id);
	}

	public function delete(){
		$id = (int)$this->request->get['id'];
		$config = new Config('menu');
		$menus = $config->get();
		if (isset($menus[$id])){
			$this->confirm(sprintf(t('confirm_want_to_delete'), t('module_menu') . ' ' . $menus[$id]['title']), '?route=constructors/menu');

			unset($menus[$id]);
			$config->set($menus)->save();
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=constructors/menu');
	}

	public function cloneit(){
		$id = (int)$this->request->get['id'];

		$config = new Config('menu');
		$menus = $config->get();
		$menus[] = $menus[$id];
		$config->set($menus)->save();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/menu');
	}
}