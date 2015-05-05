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
		
		$modules = str_replace(array(DIR_MODULE . 'constructors/', '.php'), '', glob(DIR_MODULE . 'constructors/*.php'));
		$modules = array_diff($modules, array('index'));
		foreach($modules as $module){
			$result = $this->run($module);
			if (!empty($result)){
				$constructors[] = $result;
			}
		}
		$data['content'] = '<div class="row"><div class="col-sm-6">' . implode('</div><div class="col-sm-6">', $constructors) . '</div></div>';

		$data['meta_title'] = t('module_constructors');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_constructors'))
		));

		$this->response->data = $data;
	}

	private function run($module){
		include_once DIR_MODULE . 'constructors/' . $module . '.php';
		$this->load->language('module_' . $module);
		$class = ucfirst($module) . 'Controller';
		call_user_func(array(new $class(), 'index'));
		if (!isset($this->response->data['content'])){
			return;
		}
		$content = $this->response->data['content'];
		$this->response->data = array();
		return $content;
	}
}