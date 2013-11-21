<?php
/**
* SyDES :: box module for manage all pages
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Pages extends Module{
	public $name = 'pages';
	public static $allowed4html = array('view', 'edit', 'save', 'delete', 'send', 'recover');
	public static $allowed4ajax = array('setalias', 'modal_setnewparent', 'setnewparent', 'setstatus', 'send', 'recover', 'metaadd', 'metaupdate', 'metadelete','setposition', 'delete');
	public static $allowed4demo = array('view', 'edit');
	public $type = 'page';

	function __construct(){
		$types = array_keys(Admin::$siteConfig['page_types']);
		$types[] = 'trash';
		if (isset($_GET['type']) and in_array($_GET['type'], $types)){
			$this->type = $_GET['type'];
		}
		if ($this->type == 'trash'){
			$this->conf = array('structure' => 'tree', 'title' => lang('trash_bin'));
		} else {
			$this->conf = Admin::$siteConfig['page_types'][$this->type];
		}
		
		include SYS_DIR . 'plugin/meta.php';
		$this->meta = new Meta($this->name);
	}

	public function view(){
		if (!issetTable('pages')){
			$this->install();
			redirect('?mod=pages', lang('installed'), 'success');
		}
		if ($this->conf['structure'] == 'tree'){
			$pages = $this->getPages('p1.position, p1.fullpath');
			foreach($pages as &$page){
				$page['level'] = substr_count($page['fullpath'], '/');
			}
			$statuses = array(2 => lang('visible'), 1 => lang('not_in_menu'), 0 => lang('hidden'));
			$status2 = lang('show');
			$status1 = lang('hide_from_menu');
			$template = 'module/pages/tpl/view-tree.php';
		} else {
			$pages = $this->getPages('p1.status DESC, p1.id DESC');
			$statuses = array(2 => lang('sticky'), 1 => lang('visible'), 0 => lang('hidden'));
			$status2 = lang('set_sticky');
			$status1 = lang('show');
			$template = 'module/pages/tpl/view-list.php';
			//filter by parent and skip
			if (isset($_GET['parent_id'])){
				if ($_GET['parent_id'] == 'all'){
					setcookie("{$this->type}_parent_id", '');
				} else {
					$parent_id = (int)$_GET['parent_id'];
					setcookie("{$this->type}_parent_id", $parent_id, time()+3600);
				}
			} elseif (isset($_COOKIE["{$this->type}_parent_id"])){
				$parent_id = (int)$_COOKIE["{$this->type}_parent_id"];
			}
			if (isset($_GET['skip']) and $_GET['skip'] != ''){
				$skip = (int)$_GET['skip'];
				setcookie("{$this->type}_skip", $skip, time()+3600);
			} elseif (isset($_COOKIE["{$this->type}_skip"])){
				$skip = (int)$_COOKIE["{$this->type}_skip"];
			} else {
				$skip = 0;
			}
			if (isset($parent_id)){
				foreach($pages as $k => $v){
					if ($v['parent_id'] != $parent_id){unset($pages[$k]);} //TODO need optimize?
				}
			}
			$count = count($pages);
			$perPage = 50;
			$pages = array_slice($pages, $skip, $perPage);
			$r['footerCenter'] = getPaginator('?mod=pages&type=' . $this->type, $count, $skip, $perPage);
		}
		$batch = array(
			'0' => lang('all_selected_pages'),
			'setstatus&val=2' => $status2,
			'setstatus&val=1' => $status1,
			'setstatus&val=0' => lang('hide'),
			"modal_setnewparent&type={$this->type}" => lang('set_new_parent'),
			'send&to=trash' => lang('delete')
		);
		//TODO на основе видимой меты генерировать таблицу
		$r['footerLeft'] = getSelect($batch, '', 'id="actions"');
		$r['jsfiles'][] = 'module/pages/tpl/script.js';
		$crumbs[] = array('title' => $this->conf['title']);
		$r['contentCenter'] = render($template, array('pages' => $pages, 'statuses' => $statuses, 'type' => $this->type));
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
			$page['fullpath'] = $page['parent_id'] == 0 ? '/' : $this->getFullpath($page['parent_id']) . '/';
			$page['status'] = $page['parent_id'] == 0 ? 2 : 1;
		} else {
			$page = $this->getPage($id);
			$page = $page[0];
			if (isset($_GET['source'])){
				$page['alias'] .= '-copy';
			}
		}
		if ($this->conf['structure'] == 'tree'){
			$statuses = array(2 => lang('visible'), 1 => lang('not_in_menu'), 0 => lang('hidden'));
		} else {
			$statuses = array(2 => lang('sticky'), 1 => lang('visible'), 0 => lang('hidden'));
		}
		$parents = $this->getPossibleParents($this->conf['root']);
		foreach($parents as &$p){
			if ($p['id'] == 0){
				$p['title'] = lang('root');
				break;
			}
		}
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
		$r['contentCenter'] = render('module/pages/tpl/editor.php', array('page' => $page,'parents' => $this->getParentSelect($parents, $page['parent_id'], $id)));
		$r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/database.db', $button) . $right . $meta;
		$r['title'] = lang('editor') . ' ' . $this->conf['title'] . ' ' . $page['title'];
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = "?mod=pages&type={$this->type}&act=save&id={$sid}";
		return $r;
	}
	
	public function install(){
		$files = array('module/pages/schema.sql','module/pages/dump.sql');
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

	public function save(){
		if (empty($_POST['title']) or !isset($_POST['alias'])){
			throw new Exception(lang('no_value'));
		}
		$id = (int)$_GET['id'];
		foreach ($_POST as $k => $v){
			${$k} = trim($v);
		}
		if ($id < 1 or $_GET['goto'] == 'copy'){
			$id = $this->getMaxId();
			if ($alias){
				$alias = properUri($alias);
			} elseif ($title){
				$alias = properUri($title);
			} else {
				throw new Exception(lang('no_value'));
			}
			$parentPath = '';
			$parent_id = (int)$parent_id;
			if ($parent_id > 0){
				$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = " . $parent_id);
				$parentPath = $stmt->fetchColumn();
			}
			if ($_GET['goto'] == 'copy'){
				$alias .= '-copy';
			}
			$fullpath = $parentPath . '/' . $alias;
			$position = $this->getLastPosition($parent_id);
			$this->growPos($position);
			
			$stmt = Admin::$db->prepare("INSERT OR REPLACE INTO pages VALUES ($id, :parent_id, :alias, :fullpath, :position, :status, :layout, '{$this->type}')");
			$stmt->execute(array('parent_id' => $parent_id, 'alias' => $alias, 'fullpath' => $fullpath, 'position' => $position, 'status' => $status, 'layout' => $layout ));
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
		Admin::$db->exec("UPDATE pages SET status = $val WHERE EXISTS(SELECT * FROM pages as t1 WHERE t1.id IN ($id) AND (pages.fullpath = t1.fullpath OR pages.fullpath LIKE t1.fullpath || '/%' AND pages.status > $val OR t1.fullpath LIKE pages.fullpath || '/%' AND pages.status < $val))");
		$r['success'] = lang('saved');
		if ($_GET['rel'] != 'no') $r['reload'] = 1;
		return $r;
	}

	public function setposition(){
		if(!isset($_GET['id'], $_GET['delta'],$_GET['qty']) or !is_numeric($_GET['id'])) throw new Exception('no_value');
		$delta = (int)$_GET['delta'];
		$id = $_GET['id'];
		$qty = (int)$_GET['qty'];
		// TODO оптимизировать
		if($delta < 0){
			Admin::$db->exec("UPDATE pages SET position = position+$qty WHERE position BETWEEN (SELECT position+($delta) FROM pages WHERE id = $id) AND (SELECT position-1 FROM pages WHERE id = $id) AND type = '{$this->type}'");
		} else {
			Admin::$db->exec("UPDATE pages SET position = position-$qty WHERE position BETWEEN (SELECT position+$qty FROM pages WHERE id = $id) AND (SELECT position+($qty+$delta-1) FROM pages WHERE id = $id) AND type = '{$this->type}'");
		}
		Admin::$db->exec("UPDATE pages SET position = position + ($delta) WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id = $id) || '%' AND type = '{$this->type}'");
		return array('success' => lang('saved'));
	}

	public function modal_setnewparent(){
		$parents = $this->getPossibleParents($this->conf['root']);
		foreach($parents as &$p){
			if ($p['id'] == 0){
				$p['title'] = lang('root');
				break;
			}
		}
		return array('modal' => array('title' => lang('set_new_parent'),
		'content' => '<div class="form-group"><label class="control-label">' . lang('select_parent') . '</label>
		<select name="parent_id" class="form-control">' . $this->getParentSelect($parents, -1, (int)$_GET['id']) . '</select></div>',
		'form_url' => '?mod=pages&act=setnewparent&id=' . $_GET['id']));
	}
	
	public function setnewparent(){
		if (empty($_GET['id']) or preg_match('![^\d,]!', $_GET['id'])){
			throw new Exception(lang('no_value'));
		}
		$parent = (int)$_POST['parent_id'];
		$ids = explode(',', $_GET['id']);
		// TODO избавиться от цикла и вообще переписать
		foreach ($ids as $id){
			$pos = $this->getLastPosition($parent);
			$_GET['id'] = $id;
			$stmt = Admin::$db->query("SELECT $pos - position FROM pages WHERE id = $id");
			$_GET['delta'] = $stmt->fetchColumn();
			$stmt = Admin::$db->query("SELECT count(*) FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id = $id) || '%'");
			$_GET['qty'] = $stmt->fetchColumn();
			if($_GET['delta'] > 0){
				$_GET['delta'] = $_GET['delta'] - $_GET['qty'];
			}
			$this->setposition();
			$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = $id");
			$oldPath = $stmt->fetchColumn();
			Admin::$db->exec("UPDATE pages SET parent_id = $parent, fullpath = (SELECT fullpath FROM pages WHERE id = $parent) || '/' || (SELECT alias FROM pages WHERE id = $id) WHERE id = $id");
			$this->updateChilds($id, $oldPath);
		}
		clearCache();
		Admin::log('User is changed page parent for id' . $_GET['id']);
		if ($_GET['rel'] != 'no'){
			redirect("?mod=pages&type={$this->type}", lang('saved'), 'success');
		} else {
			$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = (SELECT parent_id FROM pages WHERE id = $id)");
			return array('parent_path' => $stmt->fetchColumn());
		}
	}

	public function send(){
		if (empty($_GET['id']) or preg_match('![^\d,]!', $_GET['id'])){
			throw new Exception(lang('no_value'));
		}
		$id = $_GET['id'];
		$to = in_array($_GET['to'], array_keys(Admin::$siteConfig['page_types'])) ? $_GET['to'] : 'trash';
		$ids = explode(',', $id);
		foreach($ids as $i){
			$stmt = Admin::$db->query("SELECT position FROM pages WHERE id = $i");
			$pos = $stmt->fetchColumn();
			$qty = $this->getLastPosition($i) - $pos;
			$this->shrinkPos($pos, $qty);
		}
		
		$root = $_GET['to'] == 'trash' ? 0 : Admin::$siteConfig['page_types'][$_GET['to']]['root'];
		if ($root == 0){
			$path = '';
		} else {
			$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = $root");
			$path = $stmt->fetchColumn();
		}
		Admin::$db->exec("UPDATE pages SET type = '$to', status = 0, position = 0, parent_id = $root, fullpath = '$path/' || alias WHERE EXISTS(
			SELECT * FROM pages t1 WHERE t1.id IN ($id) AND (pages.fullpath = t1.fullpath AND id != 0 OR pages.fullpath LIKE t1.fullpath || '/%'))");
		clearCache();
		Admin::log("User is sended page to $to, id {$_GET['id']}");
		redirect("?mod=pages&type={$this->type}", lang('deleted'), 'success');
	}

	public function delete(){
		if (empty($_GET['id']) or preg_match('![^\d,]!', $_GET['id'])){
			throw new Exception(lang('no_value'));
		}
		Admin::$db->exec("DELETE FROM pages WHERE id IN ({$_GET['id']}) AND type = 'trash'");
		Admin::$db->exec("DELETE FROM pages_content WHERE page_id IN ({$_GET['id']})");
		Admin::$db->exec("DELETE FROM pages_meta WHERE page_id IN ({$_GET['id']})");
		Admin::log('User is deleted page with id ' . $_GET['id']);
		redirect('?mod=pages&type=trash', lang('deleted'), 'success');
	}

	public function recover(){
		// после беты
	}

	private function getPages($order){
		$pathPrefix = (count(Admin::$config['sites'][Admin::$site]['locales']) > 1) ? '/' . Admin::$locale : '';
		$stmt = Admin::$db->query("SELECT p1.id, pc1.title, '$pathPrefix' || p1.fullpath as fullpath, p1.parent_id, p1.status, p1.position, count(p1.id)-1 as haschilds, pc2.title as parent_title
			FROM pages p1, pages p2 
			LEFT JOIN
				pages_content pc1 ON pc1.page_id = p1.id AND pc1.locale = '" . Admin::$locale . "',
				pages_content pc2 ON pc2.page_id = p1.parent_id AND pc2.locale = '" . Admin::$locale . "'
			WHERE p1.type = '{$this->type}' AND p2.type = '{$this->type}' AND (p1.id = p2.parent_id OR p1.id = p2.id) GROUP BY p1.id ORDER BY {$order}");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function getPage($id){;
		$stmt = Admin::$db->query("SELECT pages.*, pc.title, pc.content
			FROM pages LEFT JOIN pages_content pc ON pc.page_id = pages.id AND pc.locale = '" . Admin::$locale . "'
			WHERE pages.id = {$id}");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	private function getParentSelect($pages, $parent, $current){
		$listing = '';
		$disableLevel = 1800;
		$format = '	<option value="%d" %s%s>%s %s</option>';
		foreach ($pages as $page){
			$thisSelect = $parent == $page['id'] ? 'selected' : '';
			$level = substr_count($page['fullpath'], '/') * 2;
			if ($current == $page['id']){
				$thisDisable = 'disabled';
				$disableLevel = $level;
			} elseif ($disableLevel < $level){
				$thisDisable = 'disabled';
			} elseif ($disableLevel >= $level){
				$thisDisable = '';
				$disableLevel = 1800;
			}
			$shift = str_pad('',$level - 2,'- ');
			if (empty($page['title'])) $page['title'] = lang('no_translation');
			$listing .= sprintf($format, $page['id'], $thisSelect, $thisDisable, $shift, $page['title']) . PHP_EOL;
		}
		return $listing;
	}

	private function getPossibleParents($id){
		$stmt = Admin::$db->query("SELECT p1.id, pc.title, p1.fullpath FROM pages p1, pages p2 LEFT JOIN pages_content pc ON pc.page_id = p1.id AND pc.locale = '" . Admin::$locale . "' WHERE p2.id = $id and p1.fullpath LIKE p2.fullpath || '%' and p1.type = p2.type ORDER BY p1.position, p1.fullpath");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	private function getFullpath($id){
		$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = $id");
		return $stmt->fetchColumn();
	}

	private function getMaxId(){
		$stmt = Admin::$db->query("SELECT MAX(id)+1 FROM pages");
		return $stmt->fetchColumn();
	}

	private function getLastPosition($id){
		$stmt = Admin::$db->query("SELECT MAX(position)+1 FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id = $id) || '%' AND type = '{$this->type}'");
		return $stmt->fetchColumn();
	}

	private function growPos($pos){
		Admin::$db->exec("UPDATE pages SET position = position + 1 WHERE position >= $pos AND type = '{$this->type}'");
	}

	private function shrinkPos($pos, $qty){
		Admin::$db->exec("UPDATE pages SET position = position - $qty WHERE position >= $pos AND type = '{$this->type}'");
	}

	private function updateChilds($id, $oldPath){
		$stmt = Admin::$db->query("SELECT id, fullpath FROM pages WHERE fullpath LIKE '{$oldPath}' || '/%'");
		$childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = Admin::$db->query("SELECT fullpath FROM pages WHERE id = " . $id);
		$newPath = $stmt->fetchColumn();
		$stmt = Admin::$db->prepare("UPDATE pages SET fullpath = :fullpath WHERE id = :id");
		foreach($childs as $child){
			$child['fullpath'] = str_replace_once($oldPath, $newPath, $child['fullpath']);
			$stmt -> execute($child);
		}
	}

	public function setalias(){
		if (trim($_POST['alias']) == '') throw new Exception(lang('no_value'));
		$id =(int)$_POST['id'];
		if ($id < 1) throw new Exception(lang('no_value'));
		$newAlias = properUri($_POST['alias']);
		$stmt = Admin::$db->query("SELECT fullpath, alias FROM pages WHERE id = $id");
		$old = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$newPath = str_replace_once($old[0]['alias'], $newAlias, $old[0]['fullpath']);
		Admin::$db->exec("UPDATE pages SET alias = '{$newAlias}', fullpath = '{$newPath}' WHERE id = $id");
		$this->updateChilds($id, $old[0]['fullpath']);
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