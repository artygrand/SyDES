<?php
/**
* Work with meta data for each page and global system settings
* May use with other modules.
*/
class Meta{
	public $magic = array(
		'image' => array('img','image','photo','preview','pic','picture', 'картинка'),
		'date' => array('date','date_start','date_end'),
		'pdf' => array('pdf'),
		'file' => array('file'),
		'folder' => array('folder'),
		'flash' => array('flash','swf')
	);
	function __construct($caller){
		$this->format = '
<div class="form-group" id="meta_%1$d">
	<label>%2$s</label>
	<div class="input-group">
		<input type="text" class="form-control meta_input %5$s input-sm" value="%3$s" data-id="%1$d" data-mod="%4$s">
		<span class="input-group-btn">
		<button class="btn btn-primary btn-sm" type="button" onclick="meta_del(%1$d, \'%4$s\')" title="' . lang('delete') . '"><span class="glyphicon glyphicon-remove"></span></button>
		</span>
	</div>
</div>'; 
		$this->table = $caller . '_meta';
		$this->module = $caller;
		$this->jsarray = json_encode($this->magic);
	}

	public function getPlugin($pageId){
		if (!issetTable($this->table)){ // create table on first access
			$cols = array('page_id' => array('type' => 'INTEGER NOT NULL'), 'key' => array('type' => 'TEXT'), 'value' => array('type' => 'TEXT'));
			createTable($this->table, $cols);
		}
		$metaData = '
		<div id="meta" class="form-group"><label class="help" title="' . lang('meta_tip') . '">' . lang('meta_data') . '</label>
		' . $this->existingKeys() . '
		<input type="text" value="" id="key" placeholder="' . lang('meta_key') . '" class="form-control">
		<input type="text" value="" id="value" placeholder="' . lang('meta_value') . '" class="form-control">
		<div class="text-center">
			<button class="btn btn-primary btn-sm" type="button" onclick="meta_add(' . $pageId . ', \'' . $this->module . '\')"><span class="glyphicon glyphicon-arrow-down"></span> ' . lang('add') . '</button>
		</div></div>';
		$metaData .= $this->getMetaById($pageId, true);
		$metaData .= '</div>';
		$add = str_replace('%arr%', $this->jsarray, file_get_contents(SYS_DIR . 'plugin/meta.js'));
		
		return $metaData.$add;

	}

	public function existingKeys(){
		$stmt = Admin::$db->query("SELECT key FROM {$this->table} GROUP BY key ORDER BY key");
		$keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
		array_unshift($keys, array('key' => lang('exist_keys')));
		foreach($keys as $k){
			$ks[$k['key']] = $k['key'];
		}
		return getSelect($ks, false, 'id="keys" class="form-control"');
	}

	public function getMetaById($id, $all = false){
		$metaData = '';
		$what = $all ? 'page_id' : 'id';
		$stmt = Admin::$db->query("SELECT * FROM {$this->table} WHERE $what = $id");
		$meta = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$meta) return $metaData;
		foreach($meta as $metaItem){
			$m = '';
			foreach($this->magic as $tag => $val){
				if(in_array($metaItem['key'], $val)){
					$m = $tag;
					break;
				}
			}
			$metaData .= sprintf($this->format, $metaItem['id'], $metaItem['key'], $metaItem['value'], $this->module, $m);
		}
		return $metaData;
	}

	public function add($id, $key, $value){
		$key = trim($key);
		$value = trim($value);
		if(!$value or !$key) return false;
		$stmt = Admin::$db->prepare("INSERT INTO {$this->table} (page_id, key, value) VALUES (:id, :key, :value)");  
		$stmt->execute(array('id' => $id, 'key' => $key, 'value' => str_replace('"','&quot;',$value)));
		return $this->getMetaById(Admin::$db->lastInsertId());
	}
	
	public function update($id, $value){
		$value = trim($value);
		if(!$value) exit;
		$stmt = Admin::$db->prepare("UPDATE {$this->table} SET value = :value WHERE id = :id");
		$stmt->execute(array('id' => $id, 'value' => str_replace('"','&quot;',$value)));
	}
	
	public function delete($id){
		Admin::$db->exec("DELETE FROM {$this->table} WHERE id = $id");
	}
}
?>