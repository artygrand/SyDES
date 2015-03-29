<?php
class MenuModel{
	public function getMenus(){
		$stmt = Admin::$db -> query("SELECT * FROM menu");
		return $stmt -> fetchAll(PDO::FETCH_ASSOC);
	}

	public function getMenu($id){
		$stmt = Admin::$db -> query("SELECT * FROM menu WHERE id = {$id}");
		$menu = $stmt -> fetch(PDO::FETCH_ASSOC);
		$menu['context'] = unserialize($menu['context']);
		return $menu;
	}

	public function insertMenu($title, $context){
		Admin::$db -> query("INSERT INTO menu (title, context) VALUES ('{$title}', '{$context}')");
	}

	public function updateMenu($id, $title, $context){
		Admin::$db -> query("UPDATE menu SET title = '{$title}', context = '{$context}' WHERE id = {$id}");
	}

	public function deleteMenu($id){
		Admin::$db -> exec("DELETE FROM menu WHERE id = {$id}");
	}
}
?>