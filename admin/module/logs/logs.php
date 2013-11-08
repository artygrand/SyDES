<?php
/**
* SyDES :: box module for configure templates and layouts
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Logs extends Module{
	public $name = 'logs';
	public static $allowed4html = array('view');
	public static $allowed4ajax = array();
	public static $allowed4demo = array('view');

	public function view(){
		$logs = glob(SITE_DIR . '*.log');
		$logs = array_reverse($logs);
		$skip = isset($_GET['skip']) ? $_GET['skip'] : 0;
		$lines = file($logs[$skip], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$lines = array_reverse($lines);
		$str = '<table class="table table-hover">';
		foreach($lines as $line){
			$line = explode('|', $line);
			$str .= '<tr><td>' . implode('</td><td>', $line) . '</td></tr>';
		}
		$str .= '</table>';
		
		$crumbs[] = array('title' => lang('logs'));
		$r['contentCenter'] = $str;
		$r['footerCenter'] = getPaginator('?mod=logs&act=view', count($logs), $skip, 1);
		$r['title'] = lang('logs');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}
}
?>