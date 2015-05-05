<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SitesController extends Controller{
	public $name = 'sites';
	
	public function index(){
		$sites_data = array();
		$sites = str_replace(DIR_SITE, '', glob(DIR_SITE . 's*'));
		foreach ($sites as $site){
			$sites_data[$site]['config'] = include DIR_SITE . $site . '/config.php';
			$sites_data[$site]['domain'] = include DIR_SITE . $site . '/domains.php';
		}

		$data['content'] = $this->load->view('config/sites-list', array('sites' => $sites_data));
		$data['sidebar_left'] = $this->getSideMenu('config/sites', array('interface'));
		$data['sidebar_right'] = ' ';
		$data['meta_title'] = t('site_list');

		$crumbs = array(
			array('url' => '?route=config', 'title' => t('settings')),
			array('title' => t('site_list'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);

		$this->response->data = $data;
	}

	public function add(){
		if (isset($this->request->post['name'], $this->request->post['locales'], $this->request->post['domains'])){
			if (!$this->user->isAdmin()){
				throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=config/sites/add');
			}

			$sites = str_replace(DIR_SITE . 's', '', glob(DIR_SITE . 's*'));
			$i = $sites ? (count($sites) > 1 ? max($sites)+1 : $sites[0] + 1) : 1;
			$dir = DIR_SITE . 's' . $i;
			if (!mkdir($dir)){
				die(sprintf(t('error_dir_creation_failed'), $dir));
			}

			$domain_arr = explode("\n", trim($this->request->post['domains']));
			foreach ($domain_arr as $d){
				$d = trim($d);
				if ($d){
					$domains[] = $d;
				}
			}
			$locales = explode(" ", trim($this->request->post['locales']));
			$locales = array_diff($locales, array(''));
			$config = array(
				'name' => $this->request->post['name'],
				'template' => $this->request->post['template'],
				'locales' => $locales,
				'maintenance_mode' => $this->request->post['maintenance_mode'],
				'need_cache' => $this->request->post['need_cache'],
				'use_alias_as_path' => $this->request->post['use_alias_as_path'],
				'check_updates' => $this->request->post['check_updates'],
				'page_types' => array(
					'page' => array(
						'title' => t('pages'),
						'layout' => 'page',
						'structure' => 'tree',
						'root' => 0,
						'form' => array(
							'position' => 0,
							'cdate' => 0,
							'meta' => array(),
						)
					),
					'trash' => array(
						'title' => t('trash'),
						'hidden' => 1,
						'layout' => 'page',
						'structure' => 'list',
						'root' => 0,
						'form' => array(
							'position' => 0,
							'cdate' => 0,
							'meta' => array(),
						),
						'list' => array(
							'category' => 0,
							'meta' => array()
						)
					)
				),
				'modules' => array(),
			);

			arr2file($domains, $dir . '/domains.php');
			arr2file($config, $dir . '/config.php');

			$this->site = 's' . $i;
			global $app;
			$app->connect2db();
			$this->execSqlFiles(DIR_MODULE . 'config/install.sql');
			$this->db->exec("INSERT INTO pages VALUES ('1','0','','/','#1000','2','page','page', " . time() . ")");
			foreach ($config['locales'] as $locale){
				$this->db->exec("INSERT INTO `pages_content` VALUES ('1','{$locale}','Home','This is homepage content for locale {$locale}.')");
			}
			$this->response->redirect('?route=config/sites');
		} else {
			$templates = str_replace(DIR_TEMPLATE, '', glob(DIR_TEMPLATE . '*'));
			$data['content'] = $this->load->view('config/sites-form', array(
				'title' => t('new_site'),
				'name' => '',
				'locales' => $this->language,
				'domains' => $this->request->server['HTTP_HOST'],
				'use_alias_as_path' => 0,
				'maintenance_mode' => 0,
				'need_cache' => 0,
				'template' => H::select('template', '', $templates, 'class="form-control"'),
				'site' => 'new',
				'sites' => str_replace(DIR_SITE, '', glob(DIR_SITE . 's*')),
				'check_updates' => 1,
			));
			$data['sidebar_right'] = H::saveButton() . $this->user->getMastercodeInput();
			$data['sidebar_left'] = $this->getSideMenu('config/sites', array('interface'));
			$data['form_url'] = '?route=config/sites/add';
			$data['meta_title'] = t('site_creation');
			
			$crumbs = array(
				array('url' => '?route=config', 'title' => t('settings')),
				array('url' => '?route=config/sites', 'title' => t('site_list')),
				array('title' => t('site_creation'))
			);
			$data['breadcrumbs'] = H::breadcrumb($crumbs);

			$this->response->data = $data;
		}
	}

	public function edit(){
		$templates = str_replace(DIR_TEMPLATE, '', glob(DIR_TEMPLATE . '*'));
		$config = include DIR_SITE . $this->site . '/config.php';
		$domains = include DIR_SITE . $this->site . '/domains.php';
		$data['content'] = $this->load->view('config/sites-form', array(
			'title' => $config['name'],
			'name' => $config['name'],
			'locales' => implode(' ', $config['locales']),
			'domains' => implode("\n", $domains),
			'use_alias_as_path' => $config['use_alias_as_path'],
			'maintenance_mode' => $config['maintenance_mode'],
			'need_cache' => $config['need_cache'],
			'template' => H::select('template', $config['template'], $templates, 'class="form-control"'),
			'site' => $this->site,
			'sites' => 1,
			'check_updates' => $config['check_updates'],
		));
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/config.php') . $this->user->getMastercodeInput();
		$data['sidebar_left'] = $this->getSideMenu('config/sites', array('interface'));
		$data['form_url'] = '?route=config/sites/save';
		$data['meta_title'] = t('site_editing');
		$crumbs = array(
			array('url' => '?route=config', 'title' => t('settings')),
			array('url' => '?route=config/sites', 'title' => t('site_list')),
			array('title' => t('site_editing'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);

		$this->response->data = $data;
	}

	public function save(){
		if (!isset($this->request->post['name'], $this->request->post['locales'], $this->request->post['domains'])){
			throw new BaseException(t('error_page_not_found'));
		}

		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=config/sites/edit');
		}

		$dir = DIR_SITE . $this->request->post['site'];

		$domain_arr = explode("\n", trim($this->request->post['domains']));
		foreach ($domain_arr as $d){
			$d = trim($d);
			if ($d){
				$domains[] = $d;
			}
		}
		$locales = explode(" ", trim($this->request->post['locales']));
		$locales = array_diff($locales, array(''));
		
		$config = array(
			'name' => $this->request->post['name'],
			'template' => $this->request->post['template'],
			'locales' => $locales,
			'maintenance_mode' => $this->request->post['maintenance_mode'],
			'need_cache' => $this->request->post['need_cache'],
			'use_alias_as_path' => $this->request->post['use_alias_as_path'],
			'check_updates' => $this->request->post['check_updates'],
		);
		$use_alias_old = $this->config_site['use_alias_as_path'];
		$this->config_site = array_merge($this->config_site, $config);
		
		if ($this->request->post['use_alias_as_path'] != $use_alias_old){
			$this->load->model('pages');
			$this->pages_model->canRebuildPaths();
			$this->pages_model->rebuildPaths();
		}

		arr2file($domains, $dir . '/domains.php');
		arr2file($this->config_site, $dir . '/config.php');

		$this->response->redirect('?route=config/sites');
	}

	public function config(){
		$config = $this->config_site;
		if (in_array($this->request->get['key'], array('template', 'maintenance_mode', 'need_cache'))){
			$config[$this->request->get['key']] = $this->request->get['value'];
			arr2file($config, DIR_SITE . $this->site . '/config.php');
		}
		$this->response->redirect('?route=config/sites');
	}
}