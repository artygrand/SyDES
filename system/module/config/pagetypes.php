<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class PagetypesController extends Controller{
	public $name = 'pagetypes';
	public $types;

	public function __construct(){
		parent::__construct();
		$this->load->model('pages');
		$this->types = $this->config_site['page_types'];
	}

	public function index(){
		$theme = parse_ini_file(DIR_TEMPLATE . $this->config_site['template'] . '/manifest.ini', true);
		$data = array();
		$data['content'] = $this->load->view('config/pagetypes-list', array(
				'types' => $this->types,
				'layouts' => $theme['layouts']
			));
		$data['sidebar_left'] = $this->getSideMenu('config/pagetypes', array('interface'));
		$data['sidebar_right'] = ' ';
		$data['meta_title'] = t('view') . ' ' . t('page_types');
		$crumbs = array(
			array('url' => '?route=config', 'title' => t('settings')),
			array('title' => t('page_types'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$this->response->data = $data;
	}

	public function edit(){
		$theme = parse_ini_file(DIR_TEMPLATE . $this->config_site['template'] . '/manifest.ini', true);
		$layouts = array();
		foreach ($theme['layouts'] as $k => $l){
			$layouts[$k] = $l['name'];
		}

		$data = array();
		$crumbs = array(
			array('url' => '?route=config', 'title' => t('pages')),
			array('url' => '?route=config/pagetypes', 'title' => t('page_types'))
		);

		if (!isset($this->request->get['type'], $this->types[$this->request->get['type']])){
			$roots = array(
				'auto' => t('auto_create_root_page')
			);
			$stmt = $this->db->query("SELECT p.id, pc.title FROM pages p, pages_content pc 
			WHERE p.id = pc.page_id AND p.type = 'page' AND pc.locale = '{$this->locale}' AND p.parent_id = 0 AND p.id > 1 
			ORDER BY p.position");
			$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($pages){
				foreach ($pages as $page){
					$roots[$page['id']] = $page['title'];
				}
			}

			$data['content'] = $this->load->view('config/pagetypes-form', array(
				'type' => 'new',
				'title' => '',
				'layout' => H::select('layout', '', $layouts, 'class="form-control"'),
				'structure' => H::select('structure', '', array('list'=>t('list'), 'tree'=>t('tree')), 'class="form-control"'),
				'root' => H::select('root', 'auto', $roots, 'class="form-control"'),
			));
			$data['meta_title'] = t('adding');
			$crumbs[] = array('title' => t('adding'));
		} else {
			$type = $this->types[$this->request->get['type']];
			$data['content'] = $this->load->view('config/pagetypes-form', array(
				'type' => $this->request->get['type'],
				'title' => $type['title'],
				'layout' => H::select('layout', $type['layout'], $layouts, 'class="form-control"'),
			));

			$data['meta_title'] = t('editing') . ' ' . $type['title'];
			$crumbs[] = array('title' => t('editing'));
		}

		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/config.php') . $this->user->getMastercodeInput();
		$data['sidebar_left'] = $this->getSideMenu('config/pagetypes', array('interface'));
		$data['form_url'] = '?route=config/pagetypes/save';
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$this->response->data = $data;
	}

	public function save(){
		if (!isset($this->request->post['type'], $this->request->post['title']) || !$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=config/pagetypes');
		}

		$config = $this->config_site;
		$type = $this->request->post['type'];
		if ($type != 'new'){
			$data = $this->types[$type];
			elog('User is saved page type ' . $this->request->post['title']);
		} else {
			if (empty($this->request->post['key'])){
				throw new BaseException(t('error_empty_values_passed'), 'warning', '?route=config/pagetypes');
			}
			$root = $this->request->post['root'];
			if ($root == 'auto'){
				$main = array(
					'parent_id' => 0,
					'alias' => $this->request->post['key'],
					'status' => 2,
					'layout' => 'page',
					'type' => 'page',
					'cdate' => time(),
				);
				$content = array();
				foreach ($this->config_site['locales'] as $loc){
					$content[$loc] = array(
						'title' => $this->request->post['title'],
						'content' => '',
					);
				}
				$root = $this->pages_model->create($main, $content);
			}
			$data = array(
				'structure' => $this->request->post['structure'],
				'root' => $root,
				'form' => array(
					'position' => 0,
					'cdate' => 0,
					'meta' => array(),
				),
			);

			if ($data['structure'] == 'list'){
				$data['list'] = array(
					'category' => 1,
					'meta' => array()
				);
			}

			$type = $this->request->post['key'];

			$dir = DIR_ROOT . 'upload/images/' . $type;
			is_dir($dir) || @mkdir($dir);

			elog('User is added page type ' . $this->request->post['title']);
		}
		$data['title'] = $this->request->post['title'];
		$data['layout'] = $this->request->post['layout'];

		$config['page_types'][$type] = $data;

		arr2file($config, DIR_SITE . $this->site . '/config.php');
		$this->response->redirect('?route=config/pagetypes');
	}

	public function delete(){
		$url = '?route=config/pagetypes';
		if (!isset($this->request->get['type'], $this->types[$this->request->get['type']]) || !$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', $url);
		}
		$type = $this->request->get['type'];
		$this->confirm(sprintf(t('confirm_want_to_delete'), t('type') . ' ' . $this->types[$type]['title']), $url);

		$stmt = $this->db->query("SELECT id FROM pages WHERE type = '{$type}'");
		$pages = $stmt->fetchAll(PDO::FETCH_COLUMN);
		if ($pages){
			$this->db->beginTransaction();
			foreach ($pages as $id){
				$this->db->exec("DELETE FROM pages WHERE id = {$id}");
				$this->db->exec("DELETE FROM pages_content WHERE page_id = {$id}");
				$this->db->exec("DELETE FROM pages_meta WHERE page_id = {$id}");
			}
			$this->db->commit();
		}

		$config = $this->config_site;
		unset($config['page_types'][$type]);
		arr2file($config, DIR_SITE . $this->site . '/config.php');
		elog('User is deleted page type ' . $this->types[$type]['title']);
		$this->response->redirect($url);
	}
}