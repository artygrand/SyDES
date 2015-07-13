<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class UpgradeController extends Controller{
	public $name = 'upgrade';

	public function __construct(){
		parent::__construct();
		$this->load->model('tools/upgrade');
	}

	public function index(){
		$latest = $this->cache->get('last_version');
		if (!$latest){
			$latest = $this->upgrade_model->getContent('http://sydes.ru/update/latest.txt');
			$this->cache->set('last_version', $latest, 86400);
		}

		$data = array();
		$data['sidebar_left'] = $this->getSideMenu('tools/upgrade');
		$data['sidebar_right'] = ' ';
		$data['content'] = $this->load->view('tools/upgrade', array('latest' => $latest));
		$data['meta_title'] = t('module_update');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_update'))
		));

		$this->response->data = $data;
	}
	
	public function run(){
		$latest = $this->cache->get('last_version');
		$diff_file = DIR_CACHE . VERSION . '-' . $latest. '.diff';
		$diff_source = 'https://github.com/artygrand/SyDES/compare/v' . VERSION . '...v' . $latest . '.diff';
		file_put_contents($diff_file, $this->upgrade_model->getContent($diff_source));
		
		try{
			$patcher = new Patcher(DIR_ROOT);
			$valid = $patcher->validatePatch($diff_file);
			if ($valid !== false){
				$patcher->processPatch($diff_file);
				$this->response->alert(t('upgraded'));
			} else {
				$this->response->alert('<strong>Error report:</strong><br>' . implode('<br>', $patcher->getError()), 'danger');
			}
		} catch(Exception $e){
			$this->response->alert($e->getMessage(), 'danger');
		}
		$this->cache->clear();
		$this->response->redirect('?route=tools/upgrade');
	}
}