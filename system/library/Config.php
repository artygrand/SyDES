<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Config extends HasRegistry{
	private $data = array();
	private $module;

	public function __construct($module){
		parent::__construct();
		$this->module = $module;
		$stmt = $this->db->query("SELECT key, value FROM config WHERE module = '{$module}'");
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$data){
			return;
		}
		foreach($data as $d){
			$this->data[$d['key']] = json_decode($d['value'], true);
		}
	}

	public function get($key = false){
		if ($key){
			return isset($this->data[$key]) ? $this->data[$key] : false;
		} else {
			return $this->data;
		}
		return $this;
	}

	public function set($key, $value = 'e#eq'){
		if ($value != 'e#eq'){
			$this->data[$key] = $value;
		} else {
			$this->data = $key;
		}
		return $this;
	}

	public function delete($key = false){
		if ($key){
			unset($this->data[$key]);
		} else {
			$this->data = array();
		}
		return $this;
	}

	public function save(){
		$this->db->exec("DELETE FROM config WHERE module = '{$this->module}'");
		$stmt = $this->db->prepare("INSERT INTO config (module, key, value) VALUES ('{$this->module}', :key, :value)");
		foreach($this->data as $key => $value){
			$stmt->execute(array('key' => $key, 'value' => json_encode($value)));
		}
	}
}