<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ErrorController extends Controller{
	public static $front = array('e404','e403','e503');

	public function common(){
		$this->response->data['meta_title'] = t('error_something_went_wrong');
	}

	public function e404(){
		$this->response->status = '404';

		$layouts = include DIR_TEMPLATE . $this->config_site['template'] . '/layouts.php';
		$layout = isset($layouts['404']) ? '404' : 'page';
		$this->response->data = array(
			'layout' => $layout,
			'title' => '404',
			'content' => '<p>' . t('text_page_not_found') . '</p>',
			'meta_title' => t('title_page_not_found'),
			'id' => 0,
			'parent_id' => 0,
			'position' => '',
		);
	}

	public function e403(){
		$this->response->status = '403';

		$layouts = include DIR_TEMPLATE . $this->config_site['template'] . '/layouts.php';
		$layout = isset($layouts['403']) ? '403' : 'page';
		$this->response->data = array(
			'layout' => $layout,
			'title' => '403',
			'content' => '<p>' . t('text_forbidden') . '</p>',
			'meta_title' => t('title_forbidden'),
			'id' => 0,
			'parent_id' => 0,
			'position' => '',
		);
	}

	public function e503(){
		$this->response->status = '503';
		$this->response->addHeader('Retry-After: 3964800');

		$layouts = include DIR_TEMPLATE . $this->config_site['template'] . '/layouts.php';
		$layout = isset($layouts['503']) ? '503' : 'page';
		$this->response->data = array(
			'layout' => $layout,
			'title' => t('title_maintenance_mode'),
			'content' => '<p>' . t('text_maintenance_mode') . '</p>',
			'meta_title' => t('title_maintenance_mode'),
			'id' => 0,
			'parent_id' => 0,
			'position' => '',
		);
	}

	public function csrf(){
		header('Content-Type: application/json');
		die(json_encode(array('error' => t('unauthorized_request'))));
	}
}