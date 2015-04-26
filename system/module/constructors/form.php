<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class FormController extends Controller{
	public $name = 'form';

	public function install(){
		$this->db->exec("CREATE TABLE form (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`form_attr` TEXT default '',
		`display_as` TEXT default 'block',
		`name` TEXT default '',
		`description` TEXT default '',
		`success_text` TEXT default '',
		`submit_button` TEXT default '',
		`fields` TEXT default '',
		`status` INTEGER default 1
	)");
		$this->db->exec("CREATE TABLE form_notices (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`form_id` INTEGER,
		`subject` TEXT default '',
		`from` TEXT default '',
		`to` TEXT default '',
		`body` TEXT default ''
	)");
		$this->db->exec("CREATE TABLE form_results (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`form_id` INTEGER,
		`content` TEXT default '',
		`date` TEXT default '',
		`ip` TEXT default '',
		`status` INTEGER default 1,
		`viewed` INTEGER default 0
	)");
		$this->registerModule();
		$this->response->notify(t('installed'));
		$this->response->redirect('?route=config/modules');
	}

	public function uninstall(){
		$this->db->exec("DROP TABLE IF EXISTS form");
		$this->db->exec("DROP TABLE IF EXISTS form_notices");
		$this->db->exec("DROP TABLE IF EXISTS form_results");
		$this->unregisterModule();
		$this->response->notify(t('uninstalled'));
		$this->response->redirect('?route=config/modules');
	}

	public function config(){
		$this->response->redirect('?route=constructors/form');
	}

	public function index(){
		$stmt = $this->db->query("SELECT * FROM form");
		$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!$forms){
			$forms = array();
		}
		$data['content'] = $this->load->view('constructors/form-list', array(
			'forms' => $forms,
		));
		$data['sidebar_left'] = $this->getSideMenu('constructors/form');
		$data['meta_title'] = t('module_form');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('title' => t('module_form'))
		));

		$this->response->data = $data;
	}
	
	public function edit(){
		if (!isset($this->request->get['id'])){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT * FROM form WHERE id = {$id}");
		$form = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$form){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$stmt = $this->db->query("SELECT * FROM form_notices WHERE form_id = {$id}");
		$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$notices[] = array('id' => '', 'to' => '', 'from' => '', 'subject' => '', 'body' => '');

		$data['content'] = $this->load->view('constructors/form-form', array(
			'form_id' => $id,
			'form' => $form,
			'notices' => $notices,
		));
		$data['sidebar_left'] = $this->getSideMenu('constructors/form');
		$data['form_url'] = '?route=constructors/form/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('form_editing');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('url' => '?route=constructors/form', 'title' => t('module_form')),
			array('title' => t('form_editing')),
		));

		$this->response->data = $data;
		$this->response->script[] = '/system/module/constructors/assets/form.js';
	}

	public function add(){
		$stmt = $this->db->prepare("INSERT INTO form (name) VALUES (:name)");
		$stmt->execute(array('name' => $this->request->post['name']));
		$form_id = $this->db->lastInsertId();
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form/edit&id=' . $form_id);
	}

	public function save(){
		
		$form_id = (int)$this->request->post['id'];
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form/edit&id=' . $form_id);
	}

	public function delete(){
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT name FROM form WHERE id = {$id}");
		$name = $stmt->fetchColumn();
		if ($name){
			$this->confirm(sprintf(t('confirm_want_to_delete'), t('form') . ' ' . $name), '?route=constructors/form');

			$this->db->exec("DELETE FROM form WHERE id = {$id}");
			$this->db->exec("DELETE FROM form_notices WHERE form_id = {$id}");
			$this->db->exec("DELETE FROM form_results WHERE form_id = {$id}");
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=constructors/form');
	}
}