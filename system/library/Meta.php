<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Meta extends HasRegistry{
	private $table;

	public function __construct($module){
		parent::__construct();
		$this->table = $module . '_meta';
		if (!$this->issetTable($this->table)){
			$this->db->exec("CREATE TABLE {$this->table} (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, page_id INTEGER NOT NULL, key TEXT, value TEXT, UNIQUE (page_id,key))");
		}
	}

	public function get($page_id, $key = false){
		if ($key){
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE page_id = :page_id AND key = :key");
			$stmt->execute(array('page_id' => $page_id, 'key' => $key));
		} else {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE page_id = :page_id");
			$stmt->execute(array('page_id' => $page_id));
		}
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function add($page_id, $key, $value){
		if ($key == '' or $value == '') return;
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO {$this->table} (page_id, key, value) VALUES (:page_id, :key, :value)");  
		$stmt->execute(array('page_id' => $page_id, 'key' => $key, 'value' => $value));
		return $this->db->lastInsertId();
	}

	public function update($page_id, $key, $value){
		if ($value == '') return;
		$stmt = $this->db->prepare("UPDATE {$this->table} SET value = :value WHERE page_id = :page_id AND key = :key");
		$stmt->execute(array('page_id' => $page_id, 'key' => $key, 'value' => $value));
	}

	public function delete($page_id, $key = false){
		if ($key){
			$stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE page_id = :page_id AND key = :key");
			$stmt->execute(array('page_id' => $page_id, 'key' => $key));
		} else {
			$stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE page_id = :page_id");
			$stmt->execute(array('page_id' =>$page_id));
		}
	}

	public function getById($id){
		$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
		$stmt->execute(array('id' => $id));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function updateById($id, $value){
		if ($value == '') return;
		$stmt = $this->db->prepare("UPDATE {$this->table} SET value = :value WHERE id = :id");
		$stmt->execute(array('id' => $id, 'value' => $value));
	}

	public function deleteById($id){
		$stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
		$stmt->execute(array('id' => $id));
	}

	private function issetTable($table){
		return (bool)$this->db->query("SELECT 1 FROM {$table} WHERE 1");
	}
}