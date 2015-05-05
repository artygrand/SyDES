<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class LogsController extends Controller{
	public $name = 'logs';

	public function index(){
		$logs = glob(DIR_SITE . '*.log');
		$logs = array_reverse($logs);
		$skip = isset($this->request->get['skip']) ? $this->request->get['skip'] : 0;
		$lines = file($logs[$skip], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$lines = array_reverse($lines);
		$str = '<table class="table table-hover table-condensed">';
		foreach ($lines as $line){
			$line = explode('|', $line);
			$str .= '<tr><td>' . implode('</td><td>', $line) . '</td></tr>';
		}
		$str .= '</table>';
		
		$crumbs[] = array('title' => t('logs'));
		$data['content'] = $str;
		$data['footer_center'] = H::pagination('', count($logs), $skip, 1);
		$data['meta_title'] = t('logs');
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$this->response->data = $data;
	}
}