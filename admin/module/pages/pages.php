<?php
/**
* SyDES :: box module for manage all pages
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Pages extends Module{
	public $name = 'pages';
	public static $allowed4html = array('view', 'edit', 'save', 'delete', 'remove', 'recover', 'clearcache');
	public static $allowed4ajax = array('loadchilds', 'setnewalias', 'setnewparent', 'setstatus', 'remove', 'recover', 'metaadd', 'metaupdate', 'metadelete', 'clearcache', 'modal');
	public static $allowed4demo = array('view', 'edit');
	public $type = 'page';

	function __construct(){
		if (isset($_GET['type']) and in_array($_GET['type'], array_keys(Admin::$siteConfig['page_types']))){
			$this->type = $_GET['type'];
		}
		$this->conf = Admin::$siteConfig['page_types'][$this->type];
	}

	public function view(){
		if (!issetTable('pages')){
			$this->install();
			redirect('?mod=pages', lang('installed'), 'success');
		}
		$pages = $this->getPages();
		$statuses = array(2 => lang('visible'), 1 => lang('not_in_menu'), 0 => lang('hidden'));
		if ($this->conf['structure'] == 'tree'){
			foreach($pages as &$page){
				$page['level'] = substr_count($page['fullpath'], '/');
			}
		}
		
		$batch = array(
			'0' => lang('all_selected_pages'),
			'1' => lang('show'),
			'2' => lang('hide'),
			'3' => lang('hide_from_menu'),
			'4' => lang('set_new_parent'),
			'5' => lang('delete'),
			'6' => lang('clear_cache'),
		);
		$r['footerLeft'] = getSelect($batch, '', 'id="actions"');
		$r['jsfiles'][] = 'module/pages/tpl/script.js';
		$crumbs[] = array('title' => lang('pages'));
		$r['contentCenter'] = render('module/pages/tpl/view.php', array('pages' => $pages, 'statuses' => $statuses));
		$r['title'] = lang('view') . ' ' . Admin::$siteConfig['page_types'][$this->type]['title'];
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}
	public function edit(){
	
		return array('title' => 'Редактирование страницы', 'contentCenter' => 'gecnj');
	}
	
	public function install(){
		Admin::$db -> beginTransaction();
		$schema = file_get_contents('module/pages/schema.sql');
		$schema = preg_split('/;(\s*)/', $schema);
		foreach ($schema as $table){
			$table = trim($table);
			if (!empty($table)){
				Admin::$db -> exec($table);
			}
		}
		$dump = file_get_contents('module/pages/dump.sql');
		$dump = preg_split('/;(\s*)/', $dump);
		foreach ($dump as $insert){
			$insert = trim($insert);
			if (!empty($insert)){
				Admin::$db -> exec($insert);
			}
		}
		Admin::$db -> commit();
	}

	public function setstatus(){
		if(!isset($_GET['id'], $_GET['val']) or preg_match('![^\d,]!', $_GET['id'])) throw new Exception('no_value');
		$val = (int)$_GET['val'];
		$id = $_GET['id'];
		Admin::$db -> exec("UPDATE pages SET status=$val WHERE EXISTS(SELECT * FROM pages as t1 WHERE t1.id IN ($id) AND (pages.fullpath=t1.fullpath OR pages.fullpath LIKE t1.fullpath||'/%' AND pages.status>$val OR t1.fullpath LIKE pages.fullpath||'/%' AND pages.status<$val))");
		return array('success' => lang('saved'), 'reload' => 1);
	}

	private function getPages(){
		$stmt = Admin::$db -> query("SELECT p1.id, pages_content.title, p1.fullpath, p1.parent_id, p1.status, p1.position, count(p1.id)-1 as haschilds
			FROM pages p1, pages p2 LEFT JOIN pages_content ON pages_content.page_id = p1.id AND pages_content.locale = '" . Admin::$locale . "'
			WHERE p1.type = '{$this -> type}' AND (p1.id = p2.parent_id OR p1.id = p2.id) GROUP BY p1.id ORDER BY p1.position, p1.fullpath");
		return $stmt -> fetchAll(PDO::FETCH_ASSOC);
	}
}
?>