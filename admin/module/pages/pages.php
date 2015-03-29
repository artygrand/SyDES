<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Pages extends Module{
	public $name = 'pages';
	public static $allowed4html = array('view', 'edit', 'save', 'delete');
	public static $allowed4ajax = array('setalias', 'setstatus', 'metaadd', 'metaupdate', 'metadelete', 'delete', 'moveto');
	public static $allowed4demo = array('view', 'edit');
	public $type = 'page';

	function __construct(){
		$types = array_keys(Admin::$siteConfig['page_types']);
		if (isset($_GET['type']) and in_array($_GET['type'], $types)){
			$this->type = $_GET['type'];
		}
		$this->conf = Admin::$siteConfig['page_types'][$this->type];

		include_once SYS_DIR . 'plugin/meta.php';
		$this->meta = new Meta($this->name);
		$this->loadModel();
	}

	public function view(){
		if ($this->conf['structure'] == 'tree'){
			$pages = $this->pagesModel->getPages($this->type, 'p1.position, p1.fullpath');
			if ($pages){
				foreach($pages as &$page){
					$page['level'] = substr_count($page['fullpath'], '/');
				}
			}
			$statuses = array(2 => lang('visible'), 1 => lang('not_in_menu'), 0 => lang('hidden'));
			$status2 = lang('show');
			$status1 = lang('hide_from_menu');
			$template = 'module/pages/tpl/view-tree.php';
		} else {
			$pages = $this->pagesModel->getPages($this->type, 'p1.status DESC, p1.id DESC', $this->pagesModel->getFilter($this->type));
			$statuses = array(2 => lang('sticky'), 1 => lang('visible'), 0 => lang('hidden'));
			$status2 = lang('set_sticky');
			$status1 = lang('show');
			$template = 'module/pages/tpl/view-list.php';
			//skip for pagination
			$skip = 0;
			if (isset($_GET['skip']) and $_GET['skip'] != ''){
				$skip = (int)$_GET['skip'];
				setcookie("{$this->type}_skip", $skip, time()+3600);
			} elseif (isset($_COOKIE["{$this->type}_skip"])){
				$skip = (int)$_COOKIE["{$this->type}_skip"];
			}
			$count = count($pages);
			$perPage = 50;
			$pages = $pages ? array_slice($pages, $skip, $perPage) : array();
			$r['footerCenter'] = getPaginator('?mod=pages&type=' . $this->type, $count, $skip, $perPage);
		}
		$batch = array(
			'0' => lang('all_selected_pages'),
			'setstatus&val=2' => $status2,
			'setstatus&val=1' => $status1,
			'setstatus&val=0' => lang('hide'),
			'delete' => lang('delete')
		);

		$r['footerLeft'] = getSelect($batch, '', 'id="actions"');
		$r['jsfiles'][] = 'module/pages/tpl/script.js';
		$crumbs[] = array('title' => $this->conf['title']);
		$parents = $this->pagesModel->getBranch($this->conf['root']);
		$parentfilter = isset($_GET['filter']['parent_id']) ? $_GET['filter']['parent_id'] : 0;
		$filter = isset($_GET['filter']) ? $_GET['filter'] : array();
		$r['contentCenter'] = render($template, array('pages' => $pages, 'statuses' => $statuses, 'type' => $this->type,
			'show_meta' => Admin::$siteConfig['page_types'][$this->type]['meta'],
			'parents' => $this->pagesModel->getParentSelect($parents, $parentfilter, 0), 'filter' => $filter)
		);
		$r['title'] = lang('view') . ' ' . $this->conf['title'];
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}

	public function edit(){
		$sid = $id = -1;
		if (isset($_GET['id'])){
			$sid = $id = $_GET['id'];
		} elseif (isset($_GET['source'])){
			$id = $_GET['source'];
		}
		if ($id == -1){
			$page = array(
				'id' => -1,
				'parent_id' => isset($_GET['parent']) ? (int)$_GET['parent'] : $this->conf['root'],
				'alias' => '',
				'layout' => $this->conf['layout'],
				'title' => '',
				'content' => ''
			);
			if ($page['parent_id'] == 0){
				$page['fullpath'] = '/';
			} else {
				$data = $this->pagesModel->getData($page['parent_id'], 'fullpath');
				$page['fullpath'] = $data['fullpath'] . '/';
			}
			$page['status'] = $page['parent_id'] == 0 ? 2 : 1;
		} else {
			$page = $this->pagesModel->getPage($id);
			if (!$page){
				throw new Exception(lang('no_value'));
			}
			if (isset($_GET['source'])){
				$page['alias'] .= '-copy';
			}
		}
		if ($this->conf['structure'] == 'tree'){
			$statuses = array(2 => lang('visible'), 1 => lang('not_in_menu'), 0 => lang('hidden'));
		} else {
			$statuses = array(2 => lang('sticky'), 1 => lang('visible'), 0 => lang('hidden'));
		}
		$parents = $this->pagesModel->getBranch($this->conf['root']);
		foreach(unserialize(file_get_contents(TEMPLATE_DIR . Admin::$siteConfig['template'] . '/layouts.db')) as $k => $v){
			$layouts[$k] = $v['name'];
		}
		$right = render('module/pages/tpl/editor-right.php', array(
			'status' => getSelect($statuses, $page['status'], 'name="status" class="form-control status" data-id="' . $page['id'] . '" data-rel="no"'),
			'layout' => getSelect($layouts, $page['layout'], 'name="layout" class="form-control"')
			)
		);
		$button = '<div class="form-group">
	<div class="btn-group btn-block with-dropdown">
		<a href="#" class="btn btn-primary submit btn-block" id="btn-save" data-act="apply">' . lang('save') . '</a>
		<div class="btn-group">
			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			<ul class="dropdown-menu pull-right">
				<li><a href="#" class="submit" data-act="save">' . lang('save_and_back') . '</a></li>
				<li><a href="#" class="submit" data-act="clear">' . lang('save_and_new') . '</a></li>
				<li><a href="#" class="submit" data-act="copy">' . lang('save_copy') . '</a></li>
			</ul>
		</div>
	</div>
</div>';
		$meta = ($sid > -1) ? $this->meta->getPlugin($page['id']) : '';
		$crumbs[] = array('url' => "?mod=pages&type={$this->type}",'title' => $this->conf['title']);
		$crumbs[] = array('title' => lang('editor') . ' ' . $page['title']);
		$r['jsfiles'][] = 'module/pages/tpl/script.js';
		$r['contentCenter'] = render('module/pages/tpl/editor.php', array('page' => $page, 'parents' => $this->pagesModel->getParentSelect($parents, $page['parent_id'], $id)));
		$r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/database.db', $button) . $right . $meta;
		$r['title'] = lang('editor') . ' ' . $this->conf['title'] . ' ' . $page['title'];
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = "?mod=pages&type={$this->type}&act=save&id={$sid}";
		return $r;
	}

	public function save(){
		if (empty($_POST['title']) or !isset($_POST['alias'])){
			throw new Exception(lang('no_value'));
		}
		$id = (int)$_GET['id'];
		foreach ($_POST as $k => $v){
			${$k} = trim($v); // create variables
		}
		if ($id < 0 or $_GET['goto'] == 'copy'){
			$id = $this->pagesModel->getMaxId();
			if ($alias){
				$alias = properUri($alias);
			} elseif ($title){
				$alias = properUri($title);
			} else {
				throw new Exception(lang('no_value'));
			}
			$parent = array('fullpath' => '', 'position' => '#');
			$parent_id = (int)$parent_id;
			if ($parent_id > 0){
				$stmt = Admin::$db->query("SELECT fullpath, position || '.' as position FROM pages WHERE id = " . $parent_id);
				$parent = $stmt->fetch(PDO::FETCH_ASSOC);
			}
			if ($_GET['goto'] == 'copy'){
				$alias .= '-copy';
			}
			$position = $this->pagesModel->getLastChildPos($parent_id);
			$position = $parent['position'] . ($position + 1);
			$fullpath = $parent['fullpath'] . '/' . $alias;
			$stmt = Admin::$db->prepare("INSERT OR REPLACE INTO pages VALUES ($id, :parent_id, :alias, :fullpath, :position, :status, :layout, '{$this->type}')");
			$stmt->execute(array('parent_id' => $parent_id, 'alias' => $alias, 'fullpath' => $fullpath, 'position' => $position, 'status' => $status, 'layout' => $layout));
		} else {
			$stmt = Admin::$db->prepare("UPDATE pages SET layout = :layout WHERE id = $id");
			$stmt->execute(array('layout' => $layout));
		}
		$stmt = Admin::$db->prepare("INSERT OR REPLACE INTO pages_content VALUES ($id, :locale, :title, :content)");
		$stmt->execute(array('content' => $content, 'title' => $title, 'locale' => Admin::$locale));

		clearCache();
		Admin::log('User is saved page ' . $title);
		if ($_GET['goto'] == 'save'){
			redirect('?mod=pages&type=' . $this->type, lang('saved'), 'success');
		} elseif ($_GET['goto'] == 'clear'){
			redirect('?mod=pages&type=' . $this->type . '&act=edit&parent=' . $parent_id, lang('saved'), 'success');
		}
		redirect('?mod=pages&type=' . $this->type . '&act=edit&id=' . $id, lang('saved'), 'success');
	}

	public function setstatus(){
		if(!isset($_GET['id'], $_GET['val']) or preg_match('![^\d,]!', $_GET['id'])) throw new Exception('no_value');
		$val = (int)$_GET['val'];
		$id = $_GET['id'];
		$this->pagesModel->setStatus($id, $val);
		$r['success'] = lang('saved');
		if (!isset($_GET['rel'])) $r['reload'] = 1;
		return $r;
	}

	public function moveto(){
		if (empty($_POST['id'])){
			throw new Exception(lang('no_value'));
		}
		if (isset($_POST['parent'])){ //change parent
			$this->pagesModel->setPositionAfter($_POST['id'], -1);
			$old = $this->pagesModel->getData($_POST['id'], 'fullpath, position');
			$parent = array('fullpath' => '', 'position' => '#');
			if ($_POST['parent'] > 0){
				$stmt = Admin::$db->query("SELECT fullpath, position || '.' as position FROM pages WHERE id = " . $_POST['parent']);
				$parent = $stmt->fetch(PDO::FETCH_ASSOC);
			}
			$position = $this->pagesModel->getLastChildPos($_POST['parent']);
			$position = $parent['position'] . ($position + 1);
			$this->pagesModel->setParent($_POST['id'], $_POST['parent'], $parent['fullpath'], $position);
			$this->pagesModel->updateChilds($_POST['id'], $old['fullpath'], $old['position']);
		}
		if (isset($_POST['stoppos'])){ //change position
			$this->pagesModel->setPositionAfter($_POST['id'], -1);
			$page = $this->pagesModel->getSiblingByPos($_POST['stoppos'], $_POST['id']);
			if (!empty($page['id'])){
				$this->pagesModel->setPositionAfter($page['id'], 1, true);
			}
			$old = $this->pagesModel->getData($_POST['id'], 'position');
			$this->pagesModel->setData($_POST['id'], 'position', $page['position']);
			$this->pagesModel->updateChilds($_POST['id'], false, $old['position']);
		}

		clearCache();
		$output['success'] = lang('saved');
		if (isset($_POST['parent'])){
			if (isset($_POST['norefresh'])){
				$output['parent_path'] = $parent['fullpath'];
			} else {
				$output['reload'] = 1;
			}
		}
		return $output;
	}

	public function delete(){
		if (empty($_GET['id']) or preg_match('![^\d,]!', $_GET['id'])){
			throw new Exception(lang('no_value'));
		}
		$id = explode(',', $_GET['id']);
		$pages = array_unique(array_diff($id, array(0)));

		foreach($pages as $page){
			$this->pagesModel->setPositionAfter($page, -1);
		}
		$pages = $this->pagesModel->getChilds($pages);
		$this->pagesModel->delete($pages);
		
		Admin::log('User is deleted page with id ' . $_GET['id']);
		redirect('?mod=pages&type=' . $this->type, lang('deleted'), 'success');
	}

	public function setalias(){
		if (trim($_POST['alias']) == '') throw new Exception(lang('no_value'));
		$id =(int)$_POST['id'];
		if ($id < 1) throw new Exception(lang('no_value'));
		$newAlias = properUri($_POST['alias']);
		$stmt = Admin::$db->query("SELECT fullpath, alias FROM pages WHERE id = $id");
		$old = $stmt->fetch(PDO::FETCH_ASSOC);
		$newPath = str_replace_once($old['alias'], $newAlias, $old['fullpath']);
		Admin::$db->exec("UPDATE pages SET alias = '{$newAlias}', fullpath = '{$newPath}' WHERE id = $id");
		$this->pagesModel->updateChilds($id, $old['fullpath']);
		clearCache();
		return array('success' => lang('saved'));
	}

	public function metaadd(){
		$json['content'] = $this->meta->add((int)$_POST['page_id'], $_POST['key'], $_POST['value']);
		if ($json['content']){
			$json['success'] = lang('saved');
		} else {
			$json['error'] = lang('no_value');
		}
		return $json;
	}

	public function metaupdate(){	
		$this->meta->update((int)$_POST['id'], $_POST['value']);
		return array('success' => lang('saved'));
	}

	public function metadelete(){
		$this->meta->delete((int)$_POST['id']);
		return array('success' => lang('deleted'));
	}
}
?>