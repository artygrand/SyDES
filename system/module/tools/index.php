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
		$this->response->data = array(
			'content' => t('select_in_sidebar'),
			'sidebar_left' => $this->getSideMenu('tools'),
			'meta_title' => t('module_' . $this->name),
			'breadcrumbs' => H::breadcrumb(array(
				array('title' => t('module_' . $this->name))
			)),
		);
	}
}