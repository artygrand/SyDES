<?php
class Module{
	public static $allowedActions;
	public static $allowedAjaxActions;
	public $tableStructure;
	
	function __construct(){
		// manage demo access
		if (DEMO) {
			static::$allowedActions = array('view', 'edit');
			static::$allowedAjaxActions = array();
		}
		// merge language arrays from module
		global $l;
		if (isset($this -> ml) and !is_null($l)){
			if (array_key_exists(Core::$language, $this -> ml)) {
				$l = array_merge($l, $this -> ml[Core::$language]);
			} else {
				$l = array_merge($l, $this -> ml['en']);
			}
		}

	}
	
	/**
	* Sets the name of module from classname
	* for creating linked table and links for browser
	* @return void
	*/
	public function setModuleName(){
		$this -> name = strtolower(get_class($this));
	}
	
	/**
	* Register module in system
	* Sets id for adding module settings
	* @return void
	*/
	public function register(){
		$stmt = Core::$db -> query("SELECT value FROM config_meta WHERE key = '{$this -> name}' AND page_id = 2");
		$mod_id = $stmt -> fetchColumn();
		if(!$mod_id){
			$stmt = Core::$db -> query("SELECT MAX(value) FROM config_meta WHERE page_id = 2");
			$value = $stmt -> fetchColumn() + 1;
			if ($value < 11) $value = 11;
			Core::$db -> exec("INSERT INTO config_meta (page_id, key, value) VALUES ('2', '{$this -> name}', '{$value}')");
		} 
	}
	
	/**
	* Basic installation method
	* Override this, if needed
	*/
	public function install(){
		$this -> setModuleName();
		// create table and add settings, if needed. Or just
		$this -> register();
	}
	
	public function view(){
		if (!issetTable($this -> name)){
			createTable($this -> name, $this -> tableStructure);
			$p['redirect']['url'] = '?mod=' . $this -> name;
			$p['redirect']['message'] = lang('module_installed');
			return $p;
		}
		$p['content'] = '<table class="table full zebra highlight"><thead><tr><th>ID</th>';
		foreach($this -> tableStructure as $h){
			if($h['visible'] == false) continue;
			$p['content'] .= '<th>' . $h['title'] . '</th>';
		}
		$p['content'] .= '<th>' . lang('actions') . '</th></tr></thead>';
		$stmt = Core::$db -> query("SELECT * FROM {$this -> name} ORDER BY id");
		$rows = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		if($rows){
			foreach($rows as $row){
				$p['content'] .= '<tr><td style="width:30px;">' . $row['id'] . '</td>';
					foreach($this -> tableStructure as $name => $col){
						if($col['visible'] == false) continue;
						if($this -> tableStructure[$name]['tag'] == 'select'){
							$col['val'] = $p['content'] .= '<td>' . implode(', ', unserialize($row[$name])) . '</td>';
						} else {
							$p['content'] .= '<td>' . $row[$name] . '</td>';
						}
					}
				$p['content'] .= '<td style="width:150px;"><a href="?mod=' . $this -> name . '&act=edit&id=' . $row['id'] . '">' . lang('edit') . '</a> <a href="?mod=' . $this -> name . '&act=delete&id=' . $row['id'] . '">' . lang('delete') . '</a></td></tr>' . PHP_EOL;
			}
		}
		$p['content'] .= '</table>';
		$p['breadcrumbs'] = static::$nativeName[Core::$language] . ' &gt; <span>' . lang('view') . '</span>';
		return $p;
	}
	
	public function edit(){
		if (!isset($_GET['id'])){
			foreach($this -> tableStructure as $name => &$col){
				$col['val'] = '';
			}
			$stmt = Core::$db -> query("SELECT MAX(id) FROM {$this -> name}");
			$id = $stmt -> fetchColumn() + 1;
		} else {
			$id = (int)$_GET['id'];
			$stmt = Core::$db -> query("SELECT * FROM {$this -> name} WHERE id = " . $id);
			$rows = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			if (!$rows) throw new Exception(lang('no_value'));
			foreach($this -> tableStructure as $name => &$col){
				if($this -> tableStructure[$name]['tag'] == 'select'){
					$col['val'] = unserialize($rows[0][$name]);
				} else {
					$col['val'] = $rows[0][$name];
				}
			}
		}

		$p['content'] = '<form action="?mod=' . $this -> name . '&act=save&id=' . $id . '" method="post"><table class="full form"><tr><td>' . PHP_EOL;
		$p['content'] .= getForm($this -> tableStructure);
		$p['content'] .= '</td><td>' . getSaveButton(DB_NAME) . '</td></tr></table></form>' . PHP_EOL;
		$p['breadcrumbs'] = static::$nativeName[Core::$language] . ' &gt; <span>' . lang('editor') . '</span>';
		return $p;
	}

	public function save(){
		if((int)$_GET['id']){
			$tableKeys = array_keys($this -> tableStructure);
			$data = array((int)$_GET['id']);
			foreach ($_POST as $key => $value){
				if(in_array($key, $tableKeys)){
					if($this -> tableStructure[$key]['tag'] == 'select'){
						$data[] = serialize($value);
					} else {
						$data[] = $value;
					}
				}
			}
			$count = count($this -> tableStructure) + 1;
			$count = str_pad('?', ($count*2)-1, ',?');
			$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO {$this -> name} VALUES ({$count})");
			if (!$stmt -> execute($data)) throw new Exception(lang('error_not_saved'));
			$p['redirect']['url'] = '?mod=' . $this -> name . '&act=edit&id=' . (int)$_GET['id'];
			$p['redirect']['message'] = lang('saved');
			return $p;
		}
	}
	
	public function delete(){
		Core::$db -> exec("DELETE FROM {$this -> name} WHERE id = " . (int)$_GET['id']);
		$p['redirect'] = 1;
		return $p;
	}
	
	/*public function addcolumn(){
		Core::$db -> exec("ALTER TABLE {$this -> name} ADD {$_GET['column-def']}");
	}
	
	public function drop(){
		Core::$db -> exec("DROP TABLE {$this -> name}");
	}*/
}
?>