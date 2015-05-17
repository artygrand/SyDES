<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SampleController extends Controller{
	public $name = 'sample';

	public function install(){
		/*
		Create tables, files and folders
		*/
		$this->registerModule(true);
		$this->response->notify(t('installed'));
		$this->response->redirect('?route=config/modules');
	}

	public function uninstall(){
		/*
		Drop tables, delete files and folders
		*/
		$this->unregisterModule();
		$this->response->notify(t('uninstalled'));
		$this->response->redirect('?route=config/modules');
	}

	public function config(){
		if (IS_POST){
			/*
			save settings
			*/
		}

		$data = array();
		$data['content'] = $this->load->view($this->name . '/form', array(
			'opt1' => 'Text content',
			'opt2' => '',
			'opt3' => 0,
		));
		$data['sidebar_left'] = $data['sidebar_right'] = ' ';

		$data['meta_title'] = t('settings');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=' . $this->name, 'title' => t('module_' . $this->name)),
			array('title' => t('settings'))
		));

		$this->response->data = $data;
	}

	public function index(){
		$data = array();
		$data['content'] = 'Module content';
		$data['sidebar_left'] = $data['sidebar_right'] = ' ';

		$data['meta_title'] = t('module_' . $this->name);
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_' . $this->name))
		));

		$this->response->data = $data;
		$this->addToContextMenu('setup', array(
			'title' => t('module'),
			'link' => '?route=' . $this->name . '/config',
		));
	}
}