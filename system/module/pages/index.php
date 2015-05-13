<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class PagesController extends Controller{
	public static $front = array('view');
	public $name = 'pages';
	public $type = 'page';
	public $settings;

	public function __construct(){
		parent::__construct();

		$this->load->model('pages');
		if (isset($this->request->get['type'], $this->config_site['page_types'][$this->request->get['type']])){
			$this->type = $this->request->get['type'];
		}

		$this->settings = $this->config_site['page_types'][$this->type];

		if ($this->section == 'admin'){
			$this->response->style[] = '/system/module/pages/assets/pages.css';
			$this->response->script[] = '/system/module/pages/assets/pages.js';

			if (count($this->config_site['locales']) > 1){
				$this->addContextMenu('locale', $this->locale);
				foreach ($this->config_site['locales'] as $locale){
					$this->addToContextMenu('locale', array(
						'title' => $locale,
						'link' => '?type=' . $this->type . '&locale=' . $locale
					));
				}
			}
			$this->addToContextMenu('setup', array(
				'title' => t('page_types'),
				'link' => '?route=config/pagetypes'
			));
			$this->addToContextMenu('setup', array(
				'title' => t('meta_data'),
				'link' => '?route=common/meta&module=pages'
			));
		}
	}

	public function view(){
		$data = $this->pages_model->read($this->value);

		if (empty($data['title'][$this->locale]) || $data['status'] == 0 || $data['type'] == 'trash'){
			throw new BaseException(t('error_page_not_found'));
		}

		$data['title'] = $data['title'][$this->locale];
		$content = explode('<hr id="cut" />', htmlspecialchars_decode($data['content'][$this->locale]));
		if (count($content) == 2){
			$data['preview'] = $content[0];
			$data['content'] = $content[1];
		} else {
			$data['preview'] = '';
			$data['content'] = $content[0];
		}

		$stmt = $this->db->query("SELECT key, value FROM pages_meta WHERE page_id = 0 OR page_id = {$data['id']} GROUP BY key");
		$meta = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($meta){
			foreach ($meta as $m){
				if (isset($m['key'][2]) && $m['key'][2] == '_' && substr($m['key'], 0, 2) == $this->locale){
					$data[substr($m['key'], 3)] = $m['value']; // en_metakey to metakey
				} else {
					$data[$m['key']] = $m['value'];
				}
			}
		}

		$this->addContextMenu('edit', t('edit'), ADMIN .'/?route=pages/edit&type=' . $data['type'] . '&id=' . $data['id']);
		$this->response->data = array_reverse($data);
		
		$this->response->addJsL10n($this->load->language('front', false, $this->locale));
	}

	public function index(){
		$filter = array();
		if ($this->settings['structure'] == 'tree'){
			$filter['page']['type'] = array('condition' => '=', 'value' => $this->type);
		} else {
			$skip = 0;
			$limit = 25;
			$orderby = 'cdate';
			$order = 'desc';

			foreach (array('skip', 'limit', 'orderby', 'order') as $item){
				if (isset($this->request->get[$item])){
					$$item = $this->request->get[$item];
					setcookie("{$this->type}_{$item}", $$item, time()+3600);
				} elseif (isset($_COOKIE["{$this->type}_{$item}"])){
					$$item = $_COOKIE["{$this->type}_{$item}"];
				}
			}

			$filter = $this->pages_model->getFilter($this->type);
			$filter['skip'] = (int)$skip;
			$filter['limit'] = (int)$limit;
			$fields = array_merge(array('cdate', 'title', 'parent_id', 'status'), $this->settings['list']['meta']);
			$filter['orderby'] = in_array($orderby, $fields) ? $orderby : 'cdate';
			$filter['order'] = in_array($order, array('asc', 'desc')) ? $order : 'desc';
		}

		$pages = $this->pages_model->getByFilter($this->type, $filter);
		if (!$pages){
			$pages = array();
		}

		$data = array();
		$data['footer_left'] = '';
		if ($this->settings['structure'] == 'tree'){
			$statuses = array('2'=>t('in_menu'), '1'=>t('visible'), '0'=>t('hidden'));
			if ($pages){
				foreach ($pages as $i => $p){
					$pages[$i]['level'] = substr_count($p['position'],'#');
					$pages[$i]['attr'] = 'id="page-' . $p['id'] . '"';
					$pages[$i]['status_select'] = H::select('status', $pages[$i]['status'], $statuses, 'data-id="' . $pages[$i]['id'] . '" data-reload="1"');
					$pages[$i]['title'] = empty($p['title']) ? t('no_translation') : $p['title'];
				}
				$data['footer_left'] = '<a class="btn btn-primary btn-xs" id="sort-start">' . t('sort_pages') . '</a><a class="btn btn-primary btn-xs" id="sort-stop">' . t('save') . '</a>';
				$this->response->script[] = '/system/assets/js/jquery.nestedSortable.js';
			}
			$status2 = t('show_in_menu');

			$data['content'] = $this->load->view('pages/index-tree', array(
				'pages' =>    $pages,
				'type' =>     $this->type,
				'root' =>     $this->settings['root'],
				'base' => '//' . $this->base . '/',
			));
		} else {
			$arr = array_merge(array('title', 'parent_id', 'status'), $this->settings['list']['meta']);
			$ordered = array_fill_keys($arr, array('current' => '', 'new' => 'asc'));
			$statuses = array('2' => t('sticky'), '1' => t('visible'), '0' => t('hidden'));

			if (in_array($orderby, $arr)){
				$ordered[$orderby] = array(
					'current' => 'ordered-' . $order,
					'new' => $order == 'asc' ? 'desc' : 'asc',
				);
			}

			if ($pages){
				$parents = $this->pages_model->getParents($this->type);
				foreach ($pages as $i => $p){
					$pages[$i]['status_select'] = H::select('status', $pages[$i]['status'], $statuses, 'data-id="' . $pages[$i]['id'] . '" data-reload="1"');
					$pages[$i]['parent_title'] = $parents[$pages[$i]['parent_id']]['title'];
					$pages[$i]['parent_path'] = $parents[$pages[$i]['parent_id']]['fullpath'];
					$pages[$i]['title'] = empty($p['title']) ? t('no_translation') : $p['title'];
				}

				$pagination = H::pagination('', $this->total_pages, $skip, $limit); // total_pages indirectly returns model
				if ($pagination){
					$pagination .= H::select('limit', $limit, array(12,25,50,100,200,400), 'class="goto" data-url="?route=pages&type=' . $this->type . '&filter=clear&skip=0&limit="');
				}
				$data['footer_center'] = $pagination;
			}

			$status2 = t('set_sticky');

			$filter = (isset($this->request->get['filter']) && $this->request->get['filter'] != 'clear') ? $this->request->get['filter'] : array();
			$filter = array_merge(
				array(
				'status' => 6,
				'parent_id' => 0,
				'title' => '',
				), $filter
			);
			$parents = $this->pages_model->getParentSelect($this->type, $filter['parent_id'], 0);

			if ($this->type != 'trash'){
				$this->addToContextMenu('setup', array(
					'title' => t('module'),
					'link' => '?route=pages/setup/index&type=' . $this->type,
					'modal' => 'small',
				));
			}

			$list = $this->type == 'trash' ? 'trash' : 'list';
			$data['content'] = $this->load->view('pages/index-' . $list, array(
				'pages' =>     $pages,
				'statuses' =>  $statuses,
				'type' =>      $this->type,
				'show_meta' => $this->settings['list']['meta'],
				'ordered' =>   $ordered,
				'filter' =>    $filter,
				'parents' =>   $parents,
				'show_category' => $this->settings['list']['category'],
				'base' => '//' . $this->base . '/',
			));
		}

		$batch = array(
			'0' => t('all_selected_pages')
		);
		if ($this->type != 'trash'){
			$batch['setstatus&value=2&reload=1'] = $status2;
			$batch['setstatus&value=1&reload=1'] = t('show');
			$batch['setstatus&value=0&reload=1'] = t('hide');
			$batch['move&to=trash'] = t('delete');
		}
		foreach ($this->config_site['page_types'] as $type => $ty){
			if ($type == $this->type){
				continue;
			}
			$batch['move&to=' . $type] = sprintf(t('move_to'), $ty['title']);
		}
		
		$data['footer_left'] .= H::select('batch', '0', $batch, array('id' => 'batch'));

		if (!$pages){
			$data['content'] .= '<div class="no-content">' . t('no_content') . '<a href="?route=pages/edit&type=' . $this->type . '" data-toggle="tooltip"><span class="glyphicon glyphicon-plus-sign"></span>' . t('add') . '</a></div>';
		}

		$data['meta_title'] = t('view') . ' ' . $this->settings['title'];
		$crumbs = array(
			array('title' => $this->settings['title'])
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$this->response->data = $data;
	}

	public function edit(){
		$id = -1;
		if (isset($this->request->get['id'])){
			$id = $this->request->get['id'];
		} elseif (isset($this->request->get['source'])){
			$id = $this->request->get['source'];
		}

		if ($id < 1){ // new page
			$page = array(
				'id' => -1,
				'parent_id' => isset($this->request->get['parent']) ? (int)$this->request->get['parent'] : $this->settings['root'],
				'alias' => '',
				'layout' => $this->settings['layout'],
				'title' => array(),
				'content' => array(),
				'status' => 1,
				'position' => 100,
				'cdate' => time(),
			);
		} else { // chosen or cloned
			$page = $this->pages_model->read($id);
			if (!$page){
				throw new BaseException(t('error_page_not_found'));
			}

			$page['path'] = substr($page['path'], 0, -(strlen($page['alias'])));
			if (strlen($page['path']) > 128){
				$page['path'] = substr($page['path'], 0, 62) . '...' . substr($page['path'], -63);
			}

			if (isset($this->request->get['source'])){
				$page['alias'] .= '-copy';
			}
		}

		if ($this->settings['structure'] == 'tree'){
			$statuses = array(2 => t('in_menu'), 1 => t('visible'), 0 => t('hidden'));
		} else {
			$statuses = array(2 => t('sticky'), 1 => t('visible'), 0 => t('hidden'));
			unset($page['path']);
		}
		$layout_db = include DIR_TEMPLATE . $this->config_site['template'] . '/layouts.php';
		$layouts = array();
		foreach ($layout_db as $k => $v){
			$layouts[$k] = $v['name'];
		}
		$permanent = !empty($this->settings['form']['meta'][$page['parent_id']]) ? "'" . implode("','", $this->settings['form']['meta'][$page['parent_id']]) . "'" : '';
		$right = $this->load->view('pages/editor-right', array(
			'status' => H::select('status', $page['status'], $statuses, 'data-id="' . $page['id'] . '" class="form-control"'),
			'layout' => H::select('layout', $page['layout'], $layouts, 'class="form-control"'),
			'show' => $this->settings['form'],
			'position' => $page['position'],
			'cdate' => date('d.m.Y', $page['cdate']),
			'id' => $page['id'],
			'permanent_meta' => $permanent
		));

		$button = '
		<div class="btn-group btn-block">
			<a class="col-xs-10 btn btn-primary submit">' . t('save') . '</a>
			<button type="button" class="col-xs-2 btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			<ul class="dropdown-menu dropdown-menu-right">
				<li><button type="submit" name="act" value="save">' . t('save_and_back') . '</button></li>
				<li><button type="submit" name="act" value="clear">' . t('save_and_new') . '</button></li>
				<li><button type="submit" name="act" value="copy">' . t('save_copy') . '</button></li>
			</ul>
		</div>';

		$tabs = array();
		foreach ($this->config_site['locales'] as $loc){
			$title = isset($page['title'][$loc]) ? $page['title'][$loc] : '';
			$content = isset($page['content'][$loc]) ? $page['content'][$loc] : '';
			$tabs['tab-' . $loc] = array(
				'title' => t('content') . ' ' . $loc,
				'content' => '
<div class="form-group">
	<label>' .t('page_title') . '</label>
	<input type="text" name="title[' . $loc . ']" class="form-control" value="' . $title . '">
</div>
<div class="form-group">
	<label>' .t('page_content') . '</label>
	<textarea class="form-control ckeditor" rows="25" name="content[' . $loc . ']" id="editor_' . $loc . '">' . $content . '</textarea>
</div>
',
			);
		}
		if ($permanent){
			$tabs['tab-meta'] = array(
				'title' => t('meta_data'),
				'content' => '',
			);
		}

		$data = array();
		$data['content'] =  $this->load->view('pages/editor', array(
			'page' => $page,
			'parents' => $this->pages_model->getParentSelect($this->type, $page['parent_id'], $id),
			'locale' => $this->locale,
			'type' => $this->type,
			'base' => '//' . $this->base . '/',
			'tabs' => $tabs,
		));

		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db', $button) . $right;
		$data['form_url'] = "?route=pages/save&type={$this->type}";
		$crumbs = array(
			array('url' => "?route=pages&type={$this->type}", 'title' => $this->settings['title']),
			array('title' => t('editing'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$data['meta_title'] = isset($page['title'][$this->locale]) ? t('editing') . ' ' . $page['title'][$this->locale] : t('adding');
		$this->response->data = $data;
		$this->response->script[] = '/system/module/common/assets/meta.js';

		$this->addToContextMenu('setup', array(
			'title' => t('module'),
			'link' => '?route=pages/setup/form&type=' . $this->type . '&parent_id=' . $page['parent_id'],
			'modal' => 'small',
		));
	}

	public function save(){
		if (empty($this->request->post['title'][$this->locale]) || !isset($this->request->post['alias'])){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$id = (int)$this->request->post['id'];
		$parent_id = (int)$this->request->post['parent_id'];
		$act = $this->request->post['act'];

		$content = array();
		foreach ($this->config_site['locales'] as $loc){
			$content[$loc] = array(
				'title' => $this->request->post['title'][$loc],
				'content' => $this->request->post['content'][$loc],
			);
		}

		if ($id < 0 || $act == 'copy'){ // new page or clone
			if (!empty($this->request->post['alias'])){
				$alias = $this->request->post['alias'];
			} else {
				$alias = $this->request->post['title'][$this->locale];
			}
			if ($act == 'copy'){
				$alias .= '-copy';
			}

			$main = array(
				'parent_id' => $parent_id,
				'alias' => $alias,
				'status' => (int)$this->request->post['status'],
				'layout' => $this->request->post['layout'],
				'type' => $this->type,
				'position' => isset($this->request->post['position']) ? $this->request->post['position'] : 100,
				'cdate' => isset($this->request->post['cdate']) ? strtotime($this->request->post['cdate']) : time(),
			);

			$meta = isset($this->request->post['meta']) ? $this->request->post['meta'] : false;
			$id = $this->pages_model->create($main, $content, $meta);
			if ($act == 'apply'){
				$act = 'refresh';
			}
		} else { // old page
			$main = array(
				'alias' => $this->request->post['alias'],
				'status' => (int)$this->request->post['status'],
				'layout' => $this->request->post['layout'],
				'position' => isset($this->request->post['position']) ? $this->request->post['position'] : false,
				'cdate' => isset($this->request->post['cdate']) ? strtotime($this->request->post['cdate']) : false,
				'parent_id' => $parent_id,
			);
			$this->pages_model->update($id, $main, $content);
		}

		elog('User is saved page ' . $content[$this->locale]['title']);
		$this->response->notify(t('saved'));
		if ($act == 'save'){
			$this->response->redirect('?route=pages&type=' . $this->type);
		} elseif ($act == 'clear'){
			$this->response->redirect('?route=pages/edit&type=' . $this->type . '&parent=' . $parent_id);
		} elseif ($act == 'apply'){
			// nothing
		} else {
			$this->response->redirect('?route=pages/edit&type=' . $this->type . '&id=' . $id);
		}
	}

	public function delete(){
		if (empty($this->request->get['id']) || preg_match('![^\d,]!', $this->request->get['id'])){
			throw new BaseException(t('error_empty_values_passed'));
		}
		
		$ids = array_unique(explode(',', $this->request->get['id']));
		foreach ($ids as $id){
			$this->pages_model->delete($id);
		}

		elog('User is deleted page with id ' . $_GET['id']);
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=pages&type=' . $this->type);
	}

	public function setstatus(){
		if (!isset($this->request->post['id'], $this->request->get['value']) || preg_match('![^\d,]!', $this->request->post['id'])){
			throw new BaseException(t('error_empty_values_passed'));
		}
		$val = (int)$this->request->get['value'];
		$id = $this->request->post['id'];
		$this->pages_model->setStatus($id, $val);
		
		$this->response->notify(t('saved'));
		if ($this->request->get['reload'] == 1){
			$this->response->reload();
		}
	}

	public function move(){
		if (isset($this->request->post['id']) && !preg_match('![^\d,]!', $this->request->post['id'])){
			$ids = explode(',', $this->request->post['id']);
		}
		if (isset($this->request->get['id'])){
			$ids = array((int)$this->request->get['id']);
		}
		if (!isset($ids) || !isset($this->request->get['to'], $this->config_site['page_types'][$this->request->get['to']])){
			throw new BaseException(t('error_empty_values_passed'));
		}

		if ($this->settings['structure'] == 'tree'){
			foreach ($ids as $id){
				$children = $this->pages_model->getChildren($id);
				if ($children){
					foreach ($children as $child){
						$target = $this->config_site['page_types'][$child['type']];
						if ($target['structure'] == 'tree'){
							$ids[] = $child['id'];
						} else {
							$this->pages_model->setValue($child['id'], 'parent_id', $target['root']);
						}
					}
				}
			}
		}
		$ids = array_unique($ids);

		$target = $this->config_site['page_types'][$this->request->get['to']];
		foreach ($ids as $id){
			if ($target['structure'] == 'tree'){
				$position = $this->pages_model->getLastChildPos($target['root']) + 1;
				$position = $this->pages_model->getValue($target['root'], 'position') . '#' . $position;
			} else {
				$position = 100;
			}
			$old_position = $this->pages_model->getValue($id, 'position');
			$this->pages_model->setValue($id, 'position', $position);
			$this->pages_model->setValue($id, 'parent_id', $target['root']);
			$this->pages_model->setValue($id, 'type', $this->request->get['to']);
			$this->pages_model->updPositionAfter($old_position, -1);
		}
		if (!$this->config_site['use_alias_as_path']){
			$this->pages_model->rebuildPaths();
		}
		$this->response->notify(t('moved'));
		if (IS_AJAX){
			$this->response->reload();
		} else {
			$this->response->redirect('?route=pages&type=' . $this->type);
		}
		$this->cache->clear();
	}

	public function setparent(){
		if (!isset($this->request->post['id'], $this->request->post['parent_id']) || $this->request->post['id'] < 2){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$id = (int)$this->request->post['id'];
		$parent_id = (int)$this->request->post['parent_id'];

		if ($this->settings['structure'] == 'tree'){
			$parent_position = $this->pages_model->getValue($parent_id, 'position');
			$old_position = $this->pages_model->getValue($id, 'position');
			$position = $this->pages_model->getLastChildPos($parent_id) + 1;
			$new_position = $parent_position . '#' . $position;

			$this->pages_model->setValue($id, 'parent_id', $parent_id);
			$this->pages_model->updatePositions($old_position, $new_position);
			$this->pages_model->updPositionAfter($old_position, -1);
		} else {
			$this->pages_model->setValue($id, 'parent_id', $parent_id);
		}

		if (!$this->config_site['use_alias_as_path']){
			$this->pages_model->rebuildPaths();
		}
		$this->response->notify(t('saved'));
	}

	public function reorder(){
		if (!isset($this->request->post['page'])){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$this->pages_model->rebuildPositions($this->request->post['page']);
		if (!$this->config_site['use_alias_as_path']){
			$this->pages_model->rebuildPaths();
		}

		$this->response->notify(t('saved'));
		$this->response->reload();
	}

	public function find(){
		if (!isset($this->request->post['term']) || !IS_AJAX){
			throw new BaseException(t('error_empty_values_passed'));
		}
		$result = $this->pages_model->find($this->request->post['term']);
		if (!$result){
			$result = '[]';
		}
		$this->response->body = $result;
	}
}