<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ConstructorsController extends Controller{
	public $name = 'constructors';
	
	public function index(){
		$data['content'] = 'Слайдеры<br><br>баннеры<br><br>Формы<br><br>Сетка';
		$data['sidebar_left'] = $this->getSideMenu('constructors');

		$data['meta_title'] = t('module_constructors');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_constructors'))
		));

		$this->response->data = $data;
	}

}