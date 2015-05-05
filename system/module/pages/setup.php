<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SetupController extends Controller{
	public $name = 'setup';
	public $type = 'page';

	public function __construct(){
		parent::__construct();
		if (isset($this->request->get['type'], $this->config_site['page_types'][$this->request->get['type']])){
			$this->type = $this->request->get['type'];
		}
	}

	public function index(){
		if (!IS_AJAX){
			throw new BaseException(t('error_page_not_found'));
		}

		$show = array();
		if ($this->config_site['page_types'][$this->type]['list']['category']){
			$show[] = 'category';
		}
		$show = array_merge($show, $this->config_site['page_types'][$this->type]['list']['meta']);

		$stmt = $this->db->query("SELECT key FROM pages_meta GROUP BY key ORDER BY key");
		$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
		$columns = array('category' => t('category'));
		foreach ($keys as $key){
			$columns[$key] = $key;
		}

		$body = H::form(array(
			'show' => array(
				'label' => t('show_columns'),
				'type' => 'checkbox',
				'list' => $columns,
				'value' => $show
			)
		));
		$footer = H::button(t('save'), 'button', 'class="btn btn-primary apply-modal"');
		$this->response->body = H::modal(t('pages_setup'), $body, $footer, '?route=pages/setup/saveindex&type=' . $this->type);
	}

	public function saveindex(){
		if (!IS_AJAX){
			throw new BaseException(t('error_page_not_found'));
		}

		$columns = $this->request->post['show'];
		$category = 0;
		if (in_array('category', $columns)){
			$category = 1;
			foreach (array_keys($columns, 'category') as $key) {
				unset($columns[$key]);
			}
		}

		$config = $this->config_site;
		$config['page_types'][$this->type]['list'] = array(
			'category' => $category,
			'meta' => $columns
		);

		arr2file($config, DIR_SITE . $this->site . '/config.php');
		$this->response->notify(t('saved'));
	}

	public function form(){
		if (!IS_AJAX){
			throw new BaseException(t('error_page_not_found'));
		}

		$form = $this->config_site['page_types'][$this->type]['form'];
		$parent = (int)$this->request->get['parent_id'];
		$columns = array();
		$show = array();

		if ($this->config_site['page_types'][$this->type]['structure'] == 'list'){
			$columns = array('position' => t('position'), 'cdate' => t('creation_date'));
			if ($form['position']){
				$show[] = 'position';
			}
			if ($form['cdate']){
				$show[] = 'cdate';
			}
		}
		if (isset($form['meta'][$parent])){
			$show = array_merge($show, $form['meta'][$parent]);
		}

		$stmt = $this->db->query("SELECT key FROM pages_meta pm, pages p WHERE p.id = pm.page_id AND p.type = '{$this->type}' OR pm.page_id = 0 GROUP BY key ORDER BY key");
		$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
		foreach ($keys as $key){
			$columns[$key] = $key;
		}

		$body = H::form(array(
			'show' => array(
				'label' => t('show_fields'),
				'type' => 'checkbox',
				'list' => $columns,
				'value' => $show
			),
			'empty' => array('type' => 'hidden','value' => 1)
		));

		$footer = H::button(t('save'), 'button', 'class="btn btn-primary apply-modal"');
		$this->response->body = H::modal(t('pages_setup'), $body, $footer, '?route=pages/setup/saveform&type=' . $this->type . '&parent_id=' . $parent);
	}

	public function saveform(){
		if (!IS_AJAX){
			throw new BaseException(t('error_page_not_found'));
		}

		$parent = (int)$this->request->get['parent_id'];
		$columns = $this->request->post['show'];
		$position = 0;
		$cdate = 0;
		if (in_array('position', $columns)){
			$position = 1;
			foreach (array_keys($columns, 'position') as $key) {
				unset($columns[$key]);
			}
		}
		if (in_array('cdate', $columns)){
			$cdate = 1;
			foreach (array_keys($columns, 'cdate') as $key) {
				unset($columns[$key]);
			}
		}

		$config = $this->config_site;
		$config['page_types'][$this->type]['form']['position'] = $position;
		$config['page_types'][$this->type]['form']['cdate'] = $cdate;
		$config['page_types'][$this->type]['form']['meta'][$parent] = $columns;

		arr2file($config, DIR_SITE . $this->site . '/config.php');
		$this->response->notify(t('saved'));
	}
}