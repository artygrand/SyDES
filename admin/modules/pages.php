<?php
/**
* Box module for working with all types of page.
* Accept a type, action and id. <?mod=pages&type=news&act=edit&id=12>
* @version 1.4 (for sydes 1.7)
* @author ArtyGrand
*/

class Pages extends Module{
	/**
	* Sets the visibility a links on view of pages by default
	* @var boolean
	*/
	public $isVisibleLinks = false; 

	/**
	* Sets the name of default page template
	* @var string
	*/
	public $template = 'index';

	/**
	* Sets the native module name for menu
	* @var string
	*/
	public static $nativeName = 'Страницы';

	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'edit', 'save', 'delete', 'toggle', 'remove', 'recover', 'clearcache');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowedAjaxActions = array('loadchilds', 'setnewalias', 'setnewparent', 'toggle', 'remove', 'recover', 'metaadd', 'metaupdate', 'metadelete', 'clearcache');

	function __construct(){
		$this -> setModuleName();
		parent::__construct();
		$this -> type = isset($_GET['type']) ? properUri($_GET['type']) : 'page';
		$this -> fullTree = isset($_COOKIE['fullTree']) ? ' checked' : '';
		$recover = strpos($this -> type, 'trash_') !== false ? '<a href="?mod=' . $this -> name . '&act=recover&id=%1$d">' . lang('recover') . '</a>' : '';
		$delete = !empty($recover) ? 'delete' : 'remove';
		$actWidth = !empty($recover) ? 260 : 150;
		$v = $this -> isVisibleLinks ? '' : 'path';
		$this -> format = '
	<tr%5$s data-path="%3$s">
		<td style="width:14px;">
			<input type="checkbox" value="%1$d" name="id[]" class="ids">
		</td>
		<td style="width:40px;">#%1$d</td>
		<td class="l%7$d">
			<span class="%6$s" data-id="%1$d">%2$s</span>
		</td>
		<td class="' . $v . '">
			<a href="..%3$s">%3$s</a>
		</td>
		<td style="width:100px;">
			<a href="?mod=pages&act=toggle&id=%1$d" title="%9$s">%8$s</a>
		</td>
		<td style="width:' . $actWidth . 'px;">
			' . $recover . '
			<a href="?mod=pages&type=' . $this -> type . '&act=edit&id=%1$d">' . lang('edit') . '</a>
			<a href="?mod=pages&act=' . $delete . '&id=%1$d">' . lang('delete') . '</a>
		</td>
	</tr>';
	
		include CLASS_DIR . 'meta.php';
		$this -> meta = new Meta($this -> name);
		
		$stmt = Core::$db -> query("SELECT dflt_tpl FROM page_types WHERE type = '{$this -> type}'");
		$template = $stmt -> fetchColumn();
		if (is_file('../templates/' . Core::$config['template']. '/' . $template . '.html')){
			$this -> template = $template;
		}
	}
	
	/**
	* Show list of all pages by type or only first level of tree
	* @return array
	*/
	public function view(){
		$title = $this -> getTypeName();
		if (!$title) throw new Exception(lang('unauthorized_request'));
		$p['breadcrumbs'] = $title . ' &gt; <span>' . lang('view') . '</span>' . $this -> getLocaleSwitcher();
		$p['content'] = '<table class="table full zebra highlight hideLinks"><thead><tr><th><input type="checkbox" id="checkall" title="' . lang('check_all') . '"></th><th>ID</th><th>' . lang('page_title') . '</th><th><span id="fullpath" class="help" title="' . lang('click_to_show') . '">' . lang('link') . '</span></th><th>' . lang('status') . '</th><th>' . lang('actions') . '</th></tr></thead><tbody>';

		if(isset($_COOKIE['fullTree'])){
			//$stmt = Core::$db -> query("SELECT p1.id as id, pages_content.title as title , p1.fullpath as fullpath, p1.parent_id as parent_id, p1.status as status, count(p1.id) as haschilds FROM pages p1, pages p2, pages_content WHERE p1.type = '{$this -> type}' AND (p1.id = p2.parent_id OR p1.id = p2.id) AND pages_content.locale = '" . Core::$locale . "' AND pages_content.page_id = p1.id GROUP BY p1.id");
			$stmt = Core::$db -> query("SELECT p1.id as id, pages_content.title as title, p1.fullpath as fullpath, p1.parent_id as parent_id, p1.status as status, count(p1.id) as haschilds FROM pages p1, pages p2 LEFT JOIN pages_content ON pages_content.page_id = p1.id AND pages_content.locale = '" . Core::$locale . "' WHERE p1.type = '{$this -> type}' AND (p1.id = p2.parent_id OR p1.id = p2.id) GROUP BY p1.id");
			$rawData = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			uasort ($rawData, 'natorder');
		} else {
			$rawData = $this -> getPagesByTypeAndParent($this -> type, 0);
		}

		if (!$rawData){
			$p['content'] .= '<tr><td> </td><td colspan="5">' . lang('yet_empty') . '</td></tr>';
		} else {
			$rawData = $this -> correct($rawData);
			foreach ($rawData as $data){
				$p['content'] .= vsprintf($this -> format, $data);
			}
		}
		$p['content'] .= '</tbody></table>';
		$p['jquery'] = file_get_contents('modules/pages/pages_view_jquery.js');
		$p['jsfunc'] = "
		$(document).on('click', '.canHideChilds', function(){
			var parent = $(this).parents('tr').attr('data-path')
			$('tr[data-path^=\"'+parent+'/\"]').hide()
			$(this).toggleClass('canHideChilds canShowChilds')
		})
		$(document).on('click', '.canShowChilds', function(){
			var parent = $(this).parents('tr').attr('data-path')
			$('tr[data-path^=\"'+parent+'/\"]').each(function(){
				if($(this).find('span').hasClass('canShowChilds')){
					$(this).find('.canShowChilds').toggleClass('canHideChilds canShowChilds')
				}
				$(this).show()
			})
			$(this).toggleClass('canHideChilds canShowChilds')
		})
		$(document).on('click', '.canLoadChilds', function(){
			var tr = $(this).parents('tr')
			var id = $(this).attr('data-id')
			$.getJSON('ajax.php?mod=" . $this -> name . "&type=" . $this -> type . "&act=loadchilds&id='+id,{}, function(data){
				tr.after(data.content)
			})
			$(this).toggleClass('canLoadChilds canHideChilds')
		})";
		$p['footer-left'] = '
				<select name="doit" data-where="' . $this -> name . '">
					<option value="0">' . lang('all_selected_pages') . ':</option>
					<option value="toggle">' . lang('show_or_hide') . '</option>
					<option value="remove">' . lang('delete') . '</option>
					<option value="clearcache">' . lang('clear_cache') . '</option>
				</select>
			';
		$p['footer-center'] = '
			<label><input type="checkbox" id="full-tree" ' . $this -> fullTree . '> ' . lang('show_all_pages') . '</label>
			';
		return $p;
	}

	/**
	* Load childs of current page over ajax
	*/
	public function loadchilds(){
		$rawData = $this -> getPagesByTypeAndParent($this -> type, (int)$_GET['id']);
		$p = '';
		$rawData = $this -> correct($rawData);
		foreach ($rawData as $data){
			$p .= vsprintf($this -> format, $data);
		}
		$json['content'] = $p;
		return $json;
	}
	
	/**
	* Get childs of this page
	*/
	public function getPagesByTypeAndParent($type, $parent){
		$stmt = Core::$db -> query("SELECT p1.id as id, pages_content.title as title, p1.fullpath as fullpath, p1.parent_id as parent_id, p1.status as status, count(p1.id) as haschilds FROM pages p1, pages p2 LEFT JOIN pages_content ON pages_content.page_id = p1.id AND pages_content.locale = '" . Core::$locale . "' WHERE p1.type = '{$type}' AND (p1.id = p2.parent_id OR p1.id = p2.id) AND p1.parent_id = '{$parent}' GROUP BY p1.id");
		$result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		uasort ($result, 'natorder');
		return $result;
	}
	
	private function correct($rawData){
		foreach ($rawData as &$data){
			$data['status'] = $data['status'] == 0 ? ' class="hidden"' : '';
			$data['level'] = substr_count($data['fullpath'], '/');
			if ($data['status']){
				$data['toggle'] = lang('hidden');
				$data['toggle2'] = lang('show');
			} else {
				$data['toggle'] = lang('visible');
				$data['toggle2'] = lang('hide');
			}
			if (isset($_COOKIE['fullTree'])){
				$data['haschilds'] = $data['haschilds'] > 1 ? 'canHideChilds' : '';
			} else {
				$data['haschilds'] = $data['haschilds'] > 1 ? 'canLoadChilds' : '';
			}
			if($data['title'] == NULL){
				$data['title'] = '[' . lang('no_translation') . ']';
			}
			if (count(Core::$config['locale']) > 1){
				$data['fullpath'] = '/' . Core::$locale . $data['fullpath'];
			}
		}
		return $rawData;
	}
	
	/**
	* Show page editor. Empty or with data
	*/
	public function edit(){
		$title = $this -> getTypeName();
		if (!$title) throw new Exception(lang('unauthorized_request'));
		$p['breadcrumbs'] = $title . ' &gt; <span>' . lang('editor') . '</span>' . $this -> getLocaleSwitcher();

		if (isset($_GET['id'])){
			if ((int)$_GET['id'] != 0){
				$stmt = Core::$db -> query("SELECT pages.*, pages_content.title as title, pages_content.content as content FROM pages LEFT JOIN pages_content ON pages_content.page_id = pages.id AND pages_content.locale = '" . Core::$locale . "'  WHERE pages.id = " . (int)$_GET['id']);
				$rawData = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			}
			if (!$rawData) throw new Exception(lang('unauthorized_request'));
			$allPages = $this -> getPageNamesByType();
			$p['content'] = $this -> getPageForm($rawData, $allPages, $this -> meta -> getPlugin($rawData[0]['id']));
		} else {
			$allPages = $this -> getPageNamesByType();
			$p['content'] = $this -> getPageForm('empty', $allPages, '');
		}
		$p['jquery'] = file_get_contents('modules/pages/pages_edit_jquery.js');
		return $p;
	}
	private function getPageNamesByType(){
		$stmt = Core::$db -> query("SELECT p1.id as id, pages_content.title as title, p1.fullpath as fullpath, p1.template as template FROM pages p1, pages_content WHERE p1.type = '{$this -> type}' AND pages_content.locale = '" . Core::$locale . "' AND pages_content.page_id = p1.id ORDER BY p1.fullpath");
		return $stmt -> fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	* Update page data or create new
	* @return array
	*/
	public function save(){
		if (!$_POST['title'] or !isset($_POST['alias'])){
			throw new Exception(lang('no_value'));
		}

		foreach ($_POST as $k => $v){
			${$k} = trim($v);
		}

		$content = preg_replace_callback('!<code>(.*?)</code>!ms', create_function('$match', 'return "<code>".htmlspecialchars($match[1])."</code>";'), $content);
		
		if(!$_GET['id']){//create new page
			if ($alias){
				$alias = properUri($alias);
			} elseif ($title){
				$alias = properUri($title);
			} else {
				throw new Exception(lang('no_value'));
			}

			$status = 1;
			$type = $this -> type;
			$parentPath = '';
			if ((int)$parent_id != 0){
				$stmt = Core::$db -> query("SELECT fullpath FROM pages WHERE id = " . (int)$parent_id);
				$parentPath = $stmt -> fetchColumn();
			}
			$fullpath = $parentPath . '/' . $alias;

			$stmt = Core::$db -> prepare("INSERT INTO pages (alias, template, parent_id, status, type, fullpath) VALUES (:alias, :template, :parent_id, :status, :type, :fullpath)");
			if (!$stmt->execute(array('alias' => $alias, 'template' => $template, 'parent_id' => $parent_id, 'status' => $status, 'type' => $type, 'fullpath' => $fullpath))) throw new Exception(lang('error_not_saved'));
			$id = Core::$db -> lastInsertId();
			$stmt = Core::$db -> prepare("INSERT INTO pages_content (page_id, locale, title, content) VALUES ($id, :locale, :title, :content)");
			if (!$stmt->execute(array('content' => $content, 'title' => $title, 'locale' => Core::$locale))) throw new Exception(lang('error_not_saved'));
			clearAllCache();
		} else {//update old page
			$id = (int)$_GET['id'];
			if ($id == 0) throw new Exception(lang('unauthorized_request'));
			$stmt = Core::$db -> prepare("UPDATE pages SET template = :template WHERE id = :id");
			if (!$stmt->execute(array('id' => $id, 'template' => $template))) throw new Exception(lang('error_not_saved'));
			$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO pages_content VALUES (:id,:locale,:title,:content)");
			if (!$stmt->execute(array('id' => $id, 'content' => $content, 'title' => $title, 'locale' => Core::$locale))) throw new Exception(lang('error_not_saved'));
			$this -> clearcache();
		}
		$p['redirect']['url'] = '?mod=pages&type=' . $this -> type . '&act=edit&id=' . $id;
		$p['redirect']['message'] = lang('saved');
		return $p;
	}

	/**
	* Delete page permanently
	* @return array
	*/
	public function delete(){
		if ($_GET['id']){
			$id = (int)$_GET['id'];
			if($id < 1){
				throw new Exception(lang('unauthorized_request'));
			}
			if(!$stmt = Core::$db -> query("SELECT id FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id = {$id}) || '/%'")){
				throw new Exception(lang('unauthorized_request'));
			}
			$pathsOfDeath = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			foreach($pathsOfDeath as $path){
				$id .= ',' . $path['id'];
			}
			Core::$db -> exec("DELETE FROM pages WHERE id IN ({$id})");
			Core::$db -> exec("DELETE FROM pages_content WHERE page_id IN ({$id})");
			$this -> meta -> deleteByPageId($_GET['id']);
			$json['redirect'] = 1;
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}
	
	/**
	* Remove to trash bin
	* @return array
	*/
	public function remove(){
		if ($_GET['id']){
			if(preg_match('![^\d,]!', $_GET['id'])){// will passed only 2,14,25 string
				throw new Exception(lang('unauthorized_request'));
			}
			$id = $_GET['id'];
			if(!$stmt = Core::$db -> query("SELECT id FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id IN ({$id})) || '/%'")){
				throw new Exception(lang('unauthorized_request'));
			}
			$paths = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			foreach($paths as $path){
				$id .= ',' . $path['id'];
			}
			Core::$db -> exec("UPDATE pages SET type = 'trash_' || type , status = 0 WHERE id IN ({$id}) AND type NOT LIKE 'trash_%'");
			clearAllCache();
			$json['redirect'] = 1;
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}
	
	/**
	* Recover from trash bin
	* @return array
	*/
	public function recover(){
		if ($_GET['id']){
			if(preg_match('![^\d,]!', $_GET['id'])){// will passed only 2,14,25 string
				throw new Exception(lang('unauthorized_request'));
			}
			$id = $_GET['id'];
			if(!$stmt = Core::$db -> query("SELECT id FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id IN ({$id})) || '/%'")){
				throw new Exception(lang('unauthorized_request'));
			}
			$paths = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			foreach($paths as $path){
				$id .= ',' . $path['id'];
			}
			Core::$db -> exec("UPDATE pages SET type = SUBSTR(type, 7,100), status = 1 WHERE id IN ({$id}) AND type LIKE 'trash_%'");
			clearAllCache();
			$json['redirect'] = 1;
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}

	/**
	* Hide of show these pages
	* @return array
	*/
	public function toggle(){
		if (!empty($_GET['id'])){
			if(preg_match('![^\d,]!', $_GET['id'])){ // will passed only 2,14,25 string
				throw new Exception(lang('unauthorized_request'));
			}
			$id = $_GET['id'];
			$stmt = Core::$db -> query("SELECT fullpath, status FROM pages WHERE id IN ($id)");
			$paths = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			foreach($paths as $path){
				$path['status'] = $path['status'] == 1 ? 0 : 1;
				Core::$db -> exec("UPDATE pages SET status = '{$path['status']}' WHERE fullpath = '{$path['fullpath']}' OR fullpath LIKE '{$path['fullpath']}' || '/%'");
			}
			clearAllCache();
			$json['redirect'] = 1;
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}

	public function setnewalias(){
		if (trim($_POST['alias']) != ''){
			$newAlias = properUri($_POST['alias']);
			$id =(int)$_POST['id'];
			if(!$stmt = Core::$db -> query("SELECT fullpath, alias FROM pages WHERE id = " . $id)){
				throw new Exception(lang('unauthorized_request'));
			}
			$old = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			$newPath = str_replace_once($old[0]['alias'], $newAlias, $old[0]['fullpath']);
			if(!Core::$db -> exec("UPDATE pages SET alias = '{$newAlias}', fullpath = '{$newPath}' WHERE id = " . $id)){
				throw new Exception(lang('error_not_saved'));
			}
			$this -> updateChildrensByFullpath($id, $old[0]['fullpath']);
			clearAllCache();
			$json['success'] = lang('saved');
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}

	/**
	* Move full node with childs to new parent
	* and rewrite fullpath
	* @return array
	*/
	public function setnewparent(){
		if (!empty($_POST['id'])){
			$id = (int)$_POST['id'];
			$newParent = (int)$_POST['parent_id'];
			$stmt = Core::$db -> query("SELECT fullpath FROM pages WHERE id = " . $id);
			$oldPath =  $stmt -> fetchColumn();
			if ($newParent == 0){
				Core::$db -> exec("UPDATE pages SET parent_id = '$newParent', fullpath = '/' || (SELECT alias FROM pages WHERE id = '$id') WHERE id = '$id'");
			} else{
				Core::$db -> exec("UPDATE pages SET parent_id = '$newParent', fullpath = (SELECT fullpath FROM pages WHERE id = '$newParent') || '/' || (SELECT alias FROM pages WHERE id = '$id') WHERE id = '$id'");
			}
			$this -> updateChildrensByFullpath($id, $oldPath);
			clearAllCache();
			$json['success'] = lang('saved');
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}
	
	private function updateChildrensByFullpath($id, $oldPath){
		$stmt = Core::$db -> query("SELECT id, fullpath FROM pages WHERE fullpath LIKE '{$oldPath}' || '/%'");
		$childs = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		$stmt = Core::$db -> query("SELECT fullpath FROM pages WHERE id = " . $id);
		$newPath = $stmt -> fetchColumn();
		$stmt = Core::$db -> prepare("UPDATE pages SET fullpath = :fullpath WHERE id = :id");
		foreach($childs as $child){
			$child['fullpath'] = str_replace_once($oldPath, $newPath, $child['fullpath']);
			$stmt -> execute($child);
		}
	}

	/**
	* Get native name of page type, for menu or breadcrumbs
	* @return string
	*/
	private function getTypeName(){
		if (strpos($this -> type, 'trash_') === false){
			$stmt = Core::$db -> query("SELECT name FROM page_types WHERE type = '{$this -> type}'");
			return $stmt -> fetchColumn();
		} else {
			return lang('trash');
		}
	}

	/**
	* Load page editor form
	* @return string
	*/
	private function getPageForm($rawData, $allPages, $meta){
		//if isset page, then get data
		if (is_array($rawData)){
			$rawData[0]['title'] = str_replace('"', "&quot;", $rawData[0]['title']);
			$rawData[0]['status'] = $rawData[0]['status'] == 1 ? '<a href="?mod=pages&act=toggle&id=' . $rawData[0]['id'] . '" class="span3">' . lang('hide_page') . '</a>' : '<a href="?mod=pages&act=toggle&id=' . $rawData[0]['id'] . '" class="span3">' . lang('show_page') . '</a>';
			$rawData[0]['content'] = str_replace("<", "&lt;", $rawData[0]['content']);
			$cached = $this -> isCached($rawData[0]['fullpath']) ? '<a href="?mod=pages&act=clearcache&id=' . $rawData[0]['id'] . '" class="span3">' . lang('clear_cache') . '</a>' : '<span class="span3">' . lang('not_cached') . '</span>';
		} else { //else create empty page
			$rawData = array(array( 'alias' => '', 'parent_id' => '0', 'title' => '', 'content' => '', 'template' => $this -> template, 'status' => '<span class="span3">' . lang('not_saved') . '</span>', 'id' => NULL));
			$cached = '<span class="span3"> </span>';
		}
		//list of all template files
		$templates = globRecursive('../templates/' . Core::$config['template'], array('html')); 
		$selectTemplate = getSelect($templates, 'title', $rawData[0]['template'], 'name="template" class="full"');

		//list of all pages with current type
		$parents = PHP_EOL.'<select name="parent_id" class="full"><option value="0">&raquo; ' . lang('root') . '</option>'.PHP_EOL;
		$parents .= $this -> getPagesSelect($allPages, $rawData[0]['parent_id'], $rawData[0]['id']);
		$parents .= '</select>' . PHP_EOL;
		
		ob_start();
			include TPL_DIR . 'form_page.html';
			$form = ob_get_contents();
		ob_end_clean();

		return $form;
	}
	
	/**
	* Checks the cache of page
	* @param string $path
	* @return bool
	*/
	private function isCached($path){
		if (count(Core::$config['locale']) > 1){
			$path = '/' . Core::$locale . $path;
		}
		return is_file(SYS_DIR . 'cache/' . md5($path));
	}
	
	/**
	* Delete cache of current page
	* @return array
	*/
	public function clearcache(){
		if(!empty($_GET['id'])){
			if(preg_match('![^\d,]!', $_GET['id'])){// will passed only 2,14,25 string
				throw new Exception(lang('unauthorized_request'));
			}
			$stmt = Core::$db -> query("SELECT fullpath FROM pages WHERE id IN ({$_GET['id']})");
			$paths = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			foreach($paths as $path){
				if (count(Core::$config['locale']) > 1){
					$path['fullpath'] = '/' . Core::$locale . $path['fullpath'];
				}
				$crc = md5($path['fullpath']);
				if(is_file(SYS_DIR . 'cache/' . $crc)){
					unlink(SYS_DIR . 'cache/' . $crc);
				}
			}
			$json['redirect'] = 1;
			return $json;
		} else {
			throw new Exception(lang('no_value'));
		}
	}
	
	/**
	* Get select tag with parent tree
	* @param array $allPapes
	* @param string $parent
	* @param string $current
	* @return string
	*/
	private function getPagesSelect($allPapes, $parent, $current){
		$listing = '';
		$disableLevel = 1800;
		$format = '	<option value="%d" %s%s>%s %s</option>';
		foreach ($allPapes as $page){
			if (in_array($page['template'], Core::$config['final_template'])){
				continue;
			} 
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
			$shift = str_pad('',$level,'- ');
			$listing .= sprintf($format, $page['id'], $thisSelect, $thisDisable, $shift, $page['title']) . PHP_EOL;
		}
		return $listing;
	}

	public function metaadd(){
		$json['content'] = $this -> meta -> add((int)$_POST['page_id'], $_POST['key'], $_POST['value']);
		if ($json['content']){
			$json['success'] = lang('saved');
		} else {
			$json['error'] = lang('no_value');
		}
		return $json;
	}
	
	public function metaupdate(){	
		$this -> meta -> update((int)$_POST['id'], $_POST['value']);
		$json['success'] = lang('saved');
		return $json;
	}
	
	public function metadelete(){
		$this -> meta -> delete((int)$_POST['id']);
		$json['success'] = lang('deleted');
		return $json;
	}
	
	private function getLocaleSwitcher(){
		$s = '';
		if (count(Core::$config['locale']) > 1){
			$pos = strpos($_SERVER['REQUEST_URI'], '&locale=');
			foreach(Core::$config['locale'] as $loc){
				if ($loc == Core::$locale){
					$s .= $loc . ' | ';
				} else {
					$link = $pos === false ? $_SERVER['REQUEST_URI'] . '&locale=' . $loc : substr_replace($_SERVER['REQUEST_URI'], '&locale=' . $loc, $pos);
					$s .= '<a href="' . $link . '">' . $loc . '</a> | ';
				}
			}
			$s = '<span class="right">'.substr($s, 0, -3).'</span>';
		}
		return $s;
	}
	
	/**
	* Delete page by id and put her childs to parent
	* @param string $id = "'1','3','6'" 
	* @return void
	*
	public function deleteByIdAndPutChilds2Parent($id){
		$stmt = Core::$db -> query("SELECT fullpath, parent_id, id FROM pages WHERE id IN ($id)");
		$pathsOfMercy = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		foreach($pathsOfMercy as $path){
			Core::$db -> exec("UPDATE pages SET parent_id = '{$path['parent_id']}' WHERE parent_id = '{$path['id']}'");
			$this -> updateChildrensByFullpath($path['parent_id'], $path['fullpath']);
		}
		Core::$db -> exec("DELETE FROM pages WHERE id IN ($id)");
	}*/
}
?>