<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Module{
	public static $allowed4html = array('view', 'edit', 'save', 'delete');
	public static $allowed4ajax = array();
	public static $allowed4demo = array('view', 'edit');
	public $structure; // child module's table structure

	function __construct(){
		if (isset($this -> name)){
			$this -> loadModel();
		}
	}

	public function createTable($files = false){
		if (!$files){
			return;
		}
		Admin::$db -> beginTransaction();
		foreach($files as $file){
			$data = file_get_contents($file);
			$data = preg_split('/;(\s*)/', $data);
			foreach ($data as $row){
				$row = trim($row);
				if (!empty($row)){
					Admin::$db -> exec($row);
				}
			}
		}
		Admin::$db -> commit();
	}

	public function createTableByArray($table, $structure){
		$a = 'id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT';
		foreach($structure as $name => $col){
			$a .= ", {$name} {$col['type']}";
		}
		Admin::$db -> exec("CREATE TABLE {$table} ({$a})");
	}

	public function loadModel($module = false){
		$module = $module ? $module : $this -> name;
		$file  = "module/{$module}/model.php";
		if (file_exists($file)){
			include_once($file);
			$model = "{$module}Model";
			$this -> $model = new $model();
		}
	}
	
	// basic module functions below
	public function view(){
		if (!issetTable($this->name)){
			$this->createTableByArray($this->name, $this->structure);
			redirect('?mod=' . $this->name, lang('installed'), 'success');
		}

		$p['contentCenter'] = '<table class="table table-striped table-hover"><thead><tr><th style="width:30px;">ID</th>';
		$cols = 0;
		foreach($this->structure as $h){
			if($h['visible'] == false) continue;
			$p['contentCenter'] .= '<th>' . $h['title'] . '</th>';
			$cols++;
		}
		$p['contentCenter'] .= '<th style="width:150px;">' . lang('actions') . '</th></tr></thead>';
		$stmt = Admin::$db -> query("SELECT * FROM {$this -> name} ORDER BY id DESC");
		$rows = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		if($rows){
			foreach($rows as $row){
				$p['contentCenter'] .= '<tr><td>' . $row['id'] . '</td>';
					foreach($this->structure as $name => $col){
						if($col['visible'] == false) continue;
						if($col['tag'] == 'select'){
							$p['contentCenter'] .= '<td>' . implode(', ', unserialize($row[$name])) . '</td>';
						} elseif ($col['tag'] == 'textarea'){
							$p['contentCenter'] .= '<td>' . mb_substr(strip_tags($row[$name]), 0, 50, 'utf-8') . '</td>';
						} else {
							$p['contentCenter'] .= '<td>' . $row[$name] . '</td>';
						}
					}
				$p['contentCenter'] .= '<td><a href="?mod=' . $this -> name . '&act=edit&id=' . $row['id'] . '">' . lang('edit') . '</a> <a href="?mod=' . $this -> name . '&act=delete&id=' . $row['id'] . '">' . lang('delete') . '</a></td></tr>' . PHP_EOL;
			}
		} else {
			$p['contentCenter'] .= '<tr><td colspan="' . ($cols+2) . '">' . lang('empty') . '</td></tr>';
		}
		$p['contentCenter'] .= '</table>';
		$crumbs[] = array('title' => lang('module_name'));
		$p['title'] = lang('module_name');
		$p['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $p;
	}
	
	public function edit(){
		if (!isset($_GET['id'])){
			foreach($this -> structure as $name => &$col){
				$col['val'] = '';
			}
			$stmt = Admin::$db -> query("SELECT MAX(id) FROM {$this -> name}");
			$id = $stmt -> fetchColumn() + 1;
		} else {
			$id = (int)$_GET['id'];
			$stmt = Admin::$db -> query("SELECT * FROM {$this -> name} WHERE id = " . $id);
			$rows = $stmt -> fetch(PDO::FETCH_ASSOC);
			if (!$rows) throw new Exception(lang('no_value'));
			foreach($this -> structure as $name => &$col){
				if($this -> structure[$name]['tag'] == 'select'){
					$col['val'] = unserialize($rows[$name]);
				} else {
					$col['val'] = $rows[$name];
				}
			}
		}

		$p['contentCenter'] = getForm($this -> structure);
		$p['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/database.db');
		
		$crumbs[] = array('url' => '?mod=' . $this -> name, 'title' => lang('module_name'));
		$crumbs[] = array('title' => lang('editor'));
		$p['breadcrumbs'] = getBreadcrumbs($crumbs);
		$p['title'] = lang('editor') . ' ' . lang('module_name');
		$p['form_url'] = "?mod={$this -> name}&act=save&id={$id}";
		$p['jsfiles'][] = 'ckfinder/ckfinder.js';
		return $p;
	}

	public function save(){
		if (!(int)$_GET['id']) throw new Exception(lang('no_value'));
		$id = (int)$_GET['id'];
		$tableKeys = array_keys($this -> structure);
		$data = array($id);
		foreach ($_POST as $key => $value){
			if(in_array($key, $tableKeys)){
				if($this -> structure[$key]['tag'] == 'select'){
					$data[] = serialize((array)$value);
				} else {
					$data[] = $value;
				}
			}
		}
		$count = count($this -> structure) + 1;
		$count = str_pad('?', ($count*2)-1, ',?');
		$stmt = Admin::$db -> prepare("INSERT OR REPLACE INTO {$this -> name} VALUES ({$count})");
		if (!$stmt -> execute($data)) throw new Exception(lang('error_not_saved'));
		redirect('?mod=' . $this->name, lang('saved'), 'success');
	}
	
	public function delete(){
		Admin::$db -> exec("DELETE FROM {$this -> name} WHERE id = " . (int)$_GET['id']);
		redirect('?mod=' . $this -> name, lang('deleted'), 'success');
	}
	
	public function dropTable($tbl_name){
		Admin::$db -> exec("DROP TABLE IF EXISTS {$tbl_name}");
	}
}
?>