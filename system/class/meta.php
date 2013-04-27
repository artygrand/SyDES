<?php
/**
* Work with meta data for each page and global system settings
* May use with other modules.
*/
class Meta{
	public $magic = array(
		'image' => array('img','image','photo','preview','pic','picture'),
		'date' => array('date','date_start','date_end'),
		'pdf' => array('pdf'),
		'file' => array('file'),
		'folder' => array('folder'),
		'flash' => array('flash','swf')
	);
	function __construct($caller){
		$this -> format = '
				<div id="meta_%1$d">
					<div>%2$s</div>
					<input type="text" class="meta_input %5$s" value="%3$s" data-id="%1$d" data-mod="%4$s" ><span onclick="meta_del(%1$d, \'%4$s\')" title="' . lang('delete') . '" class="small button">✖</span>
				</div>'; 
		$this -> table = $caller . '_meta';
		$this -> module = $caller;
		$this -> jsarray = json_encode($this -> magic);
	}
	
	public function getPlugin($pageId){
		if (!issetTable($this -> table)){ // create table on first access
			$cols = array('page_id' => array('type' => 'TEXT'), 'key' => array('type' => 'TEXT'), 'value' => array('type' => 'TEXT'));
			createTable($this -> table, $cols);
		}
		$metaData = '<div class="title spoiler"><span class="help" title="' . lang('meta_tip') . '">' . lang('meta_data') . '</span></div><div id="meta">
				' . $this -> existingKeys() . '
				<input type="text" value="" id="key" placeholder="' . lang('meta_key') . '" class="full">
				<input type="text" value="" id="value" placeholder="' . lang('meta_value') . '" class="full">
				<div class="centered">
					<span class="small button" onclick="meta_add(' . $pageId . ', \'' . $this -> module . '\')">▼ ' . lang('add') . '</span>
				</div>';
		$metaData .= $this -> getMetaBySomeId($pageId, true);
		$metaData .= '</div>';
		$add = str_replace('%arr%', $this -> jsarray, file_get_contents('modules/pages/pages_meta_jsfunc.js'));
		
		return $metaData.$add;

	}

	public function existingKeys(){
		$stmt = Core::$db -> query("SELECT key FROM {$this -> table} GROUP BY key ORDER BY key");
		$keys = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		array_unshift($keys, array('key' => lang('exist_keys')));
		return getSelect($keys, 'key', false, 'id="keys" class="full"');
	}

	public function getMetaBySomeId($id, $all = false){
		$metaData = '';
		$what = $all ? 'page_id' : 'id';
		$stmt = Core::$db -> query("SELECT * FROM {$this -> table} WHERE $what = $id");
		$meta = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		if (!$meta) return $metaData;
		foreach($meta as $metaItem){
			$m = '';
			foreach($this -> magic as $tag => $val){
				if(in_array($metaItem['key'], $val)){
					$m = $tag;
					break;
				}
			}
			$metaData .= sprintf($this -> format, $metaItem['id'], $metaItem['key'], $metaItem['value'], $this -> module, $m);
		}
		return $metaData;
	}

	public function add($id, $key, $value){
		$key = properUri(trim($key));
		$value = trim($value);
		if(!$value or !$key) return false;
		$stmt = Core::$db -> prepare("INSERT INTO {$this -> table} (page_id, key, value) VALUES (:id, :key, :value)");  
		$stmt -> execute(array('id' => $id, 'key' => $key, 'value' => $value));
		return $this -> getMetaBySomeId(Core::$db -> lastInsertId());
	}
	
	public function update($id, $value){
		$value = trim($value);
		if(!$value) exit;
		$stmt = Core::$db -> prepare("UPDATE {$this -> table} SET value = :value WHERE id = :id");
		$stmt -> execute(array('id' => $id, 'value' => $value));
	}
	
	public function delete($id){
		Core::$db -> exec("DELETE FROM {$this -> table} WHERE id = $id");
	}
	
	public function deleteByPageId($ids){
		Core::$db -> exec("DELETE FROM {$this -> table} WHERE page_id IN ($ids)");
	}
}
?>