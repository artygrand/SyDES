<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class FormController extends Controller{
	public $name = 'form';
	public static $front = array('send');

	public function install(){
		$this->db->exec("CREATE TABLE forms (
		`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`template` TEXT default 'default',
		`name` TEXT default '',
		`description` TEXT default '',
		`success_text` TEXT default '',
		`submit_button` TEXT default '',
		`fields` TEXT default '',
		`form_attr` TEXT default '',
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
		$this->db->exec("DROP TABLE IF EXISTS forms");
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
		if (!isset($this->config_site['modules'][$this->name])){
			return;
		}
		$stmt = $this->db->query("SELECT * FROM forms");
		$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (empty($forms)){
			$forms = array();
		}

		$stmt = $this->db->query("SELECT form_id, count(id) as count FROM form_results GROUP BY form_id");
		$counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($counts as $count){
			$results[$count['form_id']] = $count['count'];
		}
		if (empty($results)){
			$results = array();
		}

		$data['content'] = $this->load->view('constructors/form-list', array(
			'forms' => $forms,
			'results' => $results,
		));
		$data['sidebar_left'] = $this->getSideMenu('constructors/form');
		$data['meta_title'] = t('module_form');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('title' => t('module_form')),
		));

		$this->response->data = $data;
	}

	public function results(){
		$data['sidebar_left'] = $this->getSideMenu('constructors/form');
		$data['meta_title'] = t('module_form');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('url' => '?route=constructors/form', 'title' => t('module_form')),
			array('title' => t('results')),
		));

		$this->response->data = $data;
	}

	public function edit(){
		if (!isset($this->request->get['id'])){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT * FROM forms WHERE id = {$id}");
		$form = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$form){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$stmt = $this->db->query("SELECT * FROM form_notices WHERE form_id = {$id}");
		$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$notices[] = array('id' => '0', 'to' => '', 'from' => '', 'subject' => '', 'body' => '');

		$stmt = $this->db->query("SELECT `to` FROM form_notices WHERE `to` NOT LIKE '#%'");
		$mails = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$data['content'] = $this->load->view('constructors/form-form', array(
			'form_id' => $id,
			'form' => $form,
			'notices' => $notices,
			'mails' => implode(',', $mails),
			'templates' => array(
				'default' => t('default'),
				'modal' => t('modal'),
				'modal_sm' => t('modal_sm'),
				'modal_lg' => t('modal_lg'),
				'custom' => t('custom')
			),
		));
		$data['sidebar_left'] = $this->getSideMenu('constructors/form');
		$data['form_url'] = '?route=constructors/form/save';
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['meta_title'] = t('module_form');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => '?route=constructors', 'title' => t('module_constructors')),
			array('url' => '?route=constructors/form', 'title' => t('module_form')),
			array('title' => t('editing')),
		));

		$this->response->data = $data;
		$this->response->script[] = '/system/module/constructors/assets/form.js';
		$this->response->addJsL10n(array(
			'remove' => t('remove'),
			'form_sended' => t('form_sended'),
			'from_field' => t('from_field'),
			'tip_custom_template' => t('tip_custom_template'),
		));
	}

	public function add(){
		$stmt = $this->db->prepare("INSERT INTO forms (name) VALUES (:name)");
		$stmt->execute(array('name' => $this->request->post['name']));
		$form_id = $this->db->lastInsertId();
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form/edit&id=' . $form_id);
	}

	public function save(){
		$settings = $this->request->post['settings'];
		if (isset($this->request->post['fields'])){
			$fields = $this->request->post['fields'];
			foreach ($fields as $i => $field){
				$fields[$i]['required'] = isset($field['required']) ? 1 : 0;
			}
			$settings['fields'] = json_encode($fields);
		} else {
			$settings['fields'] = '{}';
		}
		
		
		$notices = $this->request->post['notice'];
		$form_id = $settings['id'];

		$stmt = $this->db->prepare("UPDATE forms SET template = :template, name = :name,
			description = :description, success_text = :success_text, submit_button = :submit_button,
			fields = :fields, form_attr = :form_attr, status = :status WHERE id = :id");
		$stmt->execute($settings);

		foreach ($notices as $id => $notice){
			if ($id == 0){
				if ($notice['to'] == '' or $notice['subject'] == '' or $notice['subject'] == 'body'){
					continue;
				}
				$stmt = $this->db->prepare("INSERT INTO form_notices (form_id, subject, `from`, `to`, body) VALUES (:form_id, :subject, :from, :to, :body)");
				$notice['form_id'] = $form_id;
				$stmt->execute($notice);
			} else {
				$stmt = $this->db->prepare("UPDATE form_notices SET `to` = :to, `from` = :from, subject = :subject, body = :body WHERE id = :id");
				$notice['id'] = $id;
				$stmt->execute($notice);
			}
		}

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form/edit&id=' . $form_id);
	}

	public function delete(){
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT name FROM form WHERE id = {$id}");
		$name = $stmt->fetchColumn();
		if ($name){
			$this->confirm(sprintf(t('confirm_want_to_delete'), t('form') . ' ' . $name), '?route=constructors/form');

			$this->db->exec("DELETE FROM forms WHERE id = {$id}");
			$this->db->exec("DELETE FROM form_notices WHERE form_id = {$id}");
			$this->db->exec("DELETE FROM form_results WHERE form_id = {$id}");
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=constructors/form');
	}

	public function send(){
		if (IS_AJAX and isset($this->request->post['form_id'], $this->request->post['token']) and $this->request->post['token'] == 'token'){
			// сохраняем и отправляем, если надо
			// меняем токен в сессии
			
			$stmt = $this->db->prepare("SELECT success_text, fields FROM forms WHERE id = :id AND status = 1");
			$stmt->execute(array('id' => $this->request->post['form_id']));
			$form = $stmt->fetch(PDO::FETCH_ASSOC);
			if (empty($form)){
				return;
			}
			
			$subject = '';
			$headers   = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";
			$headers[] = "Content-Transfer-Encoding: 8bit";
			$headers[] = "From: Promo <robot@promo.ru>";
			$headers[] = "Subject: {$subject}";
			$headers[] = "X-Mailer: PHP/".phpversion();
			$headers = implode("\r\n", $headers);

			$this->response->body = array('status' => 'ok', 'message' => $form['success_text']);
		} elseif (IS_POST){
			// сделать что надо
			// редиректнуть назад
		}
	}
}