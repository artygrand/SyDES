<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Menu extends Module{
	public $name = 'menu';
	public static $allowed4html = array('view', 'save', 'delete');
	public static $allowed4ajax = array('modal_getpages');
	public static $allowed4demo = array('view');

	public function view(){
		if (!issetTable('menu')){
			$this -> createTable(array('module/menu/schema.sql'));
			redirect('?mod=menu', lang('installed'), 'success');
		}
		$menus = $this -> menuModel -> getMenus();
		if (isset($_GET['id'])){
			$menu = $this -> menuModel -> getMenu((int)$_GET['id']);
		} else {
			$menu = array('id' => '0', 'title' => '', 'context' => array(0 => array('level' => '', 'title' => '', 'fullpath' => '')));
		}
		$crumbs[] = array('title' => lang('module_name'));
		$r['contentCenter'] = render('module/menu/tpl/view.php', array('menus' => $menus, 'menu' => $menu));
		$r['title'] = lang('module_name');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}

	public function save(){
		$title = $_POST['menu_title'];
		$context = array();
		$prev_level = 0;
		$prev_id = 0;
		$last_level_parent = array(0, 0);
		foreach($_POST['title'] as $i => $v){
			if (empty($v)){
				continue;
			}
			$context[$i]['id'] = $i+1;
			$_POST['level'][$i] = empty($_POST['level'][$i]) ? 1 : $_POST['level'][$i];
			if ($_POST['level'][$i] != $prev_level){
				if ($_POST['level'][$i] > $prev_level){
					$context[$i]['parent_id'] = $prev_id;
				} else {
					$context[$i]['parent_id'] = $last_level_parent[$_POST['level'][$i]];
				}
			} else {
				$context[$i]['parent_id'] = $context[$i-1]['parent_id'];
			}
			$context[$i]['fullpath'] = $_POST['fullpath'][$i];
			$context[$i]['title'] = $v;
			$context[$i]['level'] = $_POST['level'][$i];
			
			$prev_level = $_POST['level'][$i];
			$prev_id = $context[$i]['id'];
			$last_level_parent[$_POST['level'][$i]] = $context[$i]['parent_id'];
		}

		if (empty($context)){
			redirect('?mod=menu', lang('no_value'));
		}

		$context = serialize($context);
		if(!isset($_GET['id']) or (int)$_GET['id'] == 0){
			$this -> menuModel -> insertMenu($title, $context);
		} else {
			$this -> menuModel -> updateMenu((int)$_GET['id'], $title, $context);
		}
		redirect('?mod=menu', lang('saved'), 'success');
	}

	public function delete(){
		if(isset($_GET['id'])){
			$this -> menuModel -> deleteMenu((int)$_GET['id']);
		}
		redirect('?mod=menu', lang('deleted'), 'success');
	}

	public function modal_getpages(){
		/* TODO add page selector*/
		
		return array('modal' => array('title' => lang('select_page'), 'content' => 'text', 'form_url' => '?mod=menu'));
	}
}
?>