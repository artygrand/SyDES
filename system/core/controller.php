<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

abstract class Controller extends HasRegistry{
	public static $front = array();

	protected function createRouteAlias($alias, $route, $params = ''){
		$this->db->exec("INSERT INTO routes (alias, route, params) VALUES ('{$alias}', '{$route}', '{$params}')");
	}

	protected function updateRouteAlias($alias, $route, $params = ''){
		$this->db->exec("UPDATE routes SET route = '{$route}', params = '{$params}' WHERE alias = '{$alias}'");
	}

	protected function deleteRouteAlias($alias){
		$this->db->exec("DELETE FROM routes WHERE alias = '{$alias}'");
	}

	protected function execSqlFiles($files){
		$this->db->beginTransaction();
		foreach((array)$files as $file){
			$data = preg_split('/;(\s*)/', file_get_contents($file));
			foreach ($data as $row){
				$row = trim($row);
				if (!empty($row)){
					$this->db->exec($row);
				}
			}
		}
		$this->db->commit();
	}

	protected function createTableByArray($table, $structure){
		$a = 'id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT';
		foreach($structure as $name => $col){
			$a .= ", {$name} {$col['def']}";
		}
		$a .= ", status INTEGER NOT NULL default '1'";
		$this->db->exec("CREATE TABLE {$table} ({$a})");
	}

	protected function confirm($message, $return_url){
		if (!isset($this->request->post['confirm'])){
			$this->response->data['content'] = $this->load->view('common/confirm', array(
				'message' => $message,
				'return_url' => $return_url
			));
			throw new BaseException(t('need_confirmation'), 'info');
		}
	}

	protected function addContextMenu($key, $title, $link = false){
		$this->response->context[$key] = array(
			'title' => $title,
			'link' => $link,
			'children' => array(),
		);
	}

	protected function addToContextMenu($key, $child){
		if (!isset($child['modal'])){
			$child['modal'] = false;
		}
		$this->response->context[$key]['children'][] = $child;
	}

	protected function getSideMenu($current, $exclude = array()){
		$parts = explode('/', $current);
		$module = $parts[0];

		$modules = str_replace(array(DIR_MODULE . $module . '/', '.php'), '', glob(DIR_MODULE . $module . '/*.php'));
		$modules = array_diff($modules, $exclude, array('index'));

		$this->load->language('module_' . $module);
		$links['?route=' . $module] = t('module_' . $module);
		foreach($modules as $mod){
			$this->load->language('module_' . $mod);
			$links['?route=' . $module . '/' . $mod] = t('module_' . $mod);
		}
		return H::listLinks($links, '?route=' . $current, 'class="nav nav-tabs-left"');
	}

	protected function registerModule($in_menu = false, $quick_add = false){
		$config = $this->config_site;
		$config['modules'][$this->name] = array(
			'in_menu' => $in_menu,
			'quick_add' => $quick_add
		);
		arr2file($config, DIR_SITE . $this->site . '/config.php');
	}

	protected function unregisterModule(){
		$config = $this->config_site;
		unset($config['modules'][$this->name]);
		arr2file($config, DIR_SITE . $this->site . '/config.php');
	}
}