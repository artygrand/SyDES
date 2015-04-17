<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ToolsController extends Controller{
	public $name = 'tools';

	public function index(){
		$data['content'] = 'Тут возможно будут вещи слева <br><br> Так что TODO' . $this->load->view('tools/form');
		$data['sidebar_left'] = 'импорт/экспорт <br><br> бекап и обновление <br><br> редиректы';

		$data['meta_title'] = t('module_' . $this->name);
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_' . $this->name))
		));

		$this->response->data = $data;
		$this->addToContextMenu('setup', array(
			'title' => t('module'),
			'link' => '?route=' . $this->name . '/config',
		));
		$this->response->script[] = 'assets/js/dtable.js';
	}
}