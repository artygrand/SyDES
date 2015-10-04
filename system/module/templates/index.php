<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class TemplatesController extends Controller{
	public $name = 'templates';

	public function index(){
		$model = $this->load->model('templates', false);
		$model->prepare();

		$model->createLayouts();
		$data = array();
		$data['content'] = $this->load->view('templates/index', array(
			'files' => $model->getFiles(),
			'layouts' => $model->settings['layouts'],
			'modules' => $model->getOverrides('module'),
			'iblocks' => $model->getOverrides('iblock'),
			'iblock_list' => str_replace(array(DIR_IBLOCK, '/default.php'), '', glob(DIR_IBLOCK . '*/default.php')),
			'template' => '&tpl=' . $model->template
		));

		$data['sidebar_left'] = $data['sidebar_right'] = ' ';
		$templates = glob(DIR_TEMPLATE . '*');
		if (count($templates) > 1){
			$links = array();
			foreach ($templates as $t){
				if (file_exists($t . '/manifest.ini')){
					$theme = parse_ini_file($t . '/manifest.ini', true);
					$theme_name = $theme['theme']['name'];
				} else {
					$theme_name = 'unknown theme';
				}
				$links['?route=templates&tpl=' . str_replace(DIR_TEMPLATE, '', $t)] = $theme_name;
			}
			$data['sidebar_left'] = H::listLinks($links, '?route=templates&tpl=' . $model->template, 'class="nav nav-tabs-left"');
		}

		$data['meta_title'] = t('templates');
		$crumbs = array(
			array('title' => t('templates'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);

		$this->response->addHeader('X-XSS-Protection: 0');
		$this->response->data = $data;
	}
}