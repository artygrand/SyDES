<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class RedirectController extends Controller{
	public $name = 'redirect';

	public function index(){
		$stmt = $this->db->query("SELECT * FROM routes WHERE route = 'common/redirect'");
		$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$result = array();
		if (!empty($routes)){
			foreach ($routes as $route){
				$result[] = array(
					'from' => $route['alias'],
					'to' => str_replace('url=', '/', $route['params'])
				);
			}
		}
		
		$data = array();
		$data['sidebar_left'] = $this->getSideMenu('tools/redirect');
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['content'] = $this->load->view('tools/redirect', array('result' => $result));
		$data['meta_title'] = t('module_redirect');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_redirect'))
		));
		$data['form_url'] = '?route=tools/redirect/save';

		$this->response->data = $data;
	}

	public function save(){
		$input = $this->request->post['redir'];
		if ($input['new_key']['from'][0] != ''){
			foreach ($input['new_key']['from'] as $i => $from){
				if ($from == '' || $input['new_key']['to'][$i] == '') continue;
				$input[] = array(
					'from' => $from,
					'to' => $input['new_key']['to'][$i]
				);
			}
		}
		unset($input['new_key']);

		$this->db->beginTransaction();
		$this->db->exec("DELETE FROM routes WHERE route = 'common/redirect'");
		$stmt = $this->db->prepare("INSERT INTO routes (alias, route, params) VALUES (:from, 'common/redirect', :to)");
		foreach ($input as $rule){
			$rule['to'] = 'url=' . substr($rule['to'], 1);
			$rule['from'] = htmlspecialchars_decode($rule['from']);
			$stmt->execute($rule);
		}
		$this->db->commit();

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=tools/redirect');
	}
}