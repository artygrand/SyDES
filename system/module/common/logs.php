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
		$table = '<table class="table table-hover table-condensed">';
		foreach ($lines as $line){
			$line = explode('|', $line);
			$table .= '<tr><td>' . implode('</td><td>', $line) . '</td></tr>';
		}
		$table .= '</table>';

		$this->response->data = array(
			'content' => $table,
			'footer_center' => H::pagination('', count($logs), $skip, 1),
			'meta_title' => t('logs'),
			'breadcrumbs' => H::breadcrumb(array(
			array('title' => t('logs'))
			))
		);;
	}
}