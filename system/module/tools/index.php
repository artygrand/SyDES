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
		$data['content'] = t('select_in_sidebar');
		$data['sidebar_left'] = $this->getSideMenu('tools');

		$data['meta_title'] = t('module_' . $this->name);
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_' . $this->name))
		));

		$this->response->data = $data;
	}
}