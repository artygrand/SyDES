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
		`status` INTEGER default 0,
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
		$results = array();
		foreach ($counts as $count){
			$results[$count['form_id']] = $count['count'];
		}
		if (empty($results)){
			$results = array();
		}

		$data = array();
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
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT * FROM form_results WHERE form_id = {$id} ORDER BY id DESC");
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$results){
			throw new BaseException(t('error_empty_values_passed'));
		}

		foreach ($results as $i => $result){
			$results[$i]['content'] = json_decode($result['content'], true);
		}
		$this->response->data = array(
			'sidebar_left' => $this->getSideMenu('constructors/form'),
			'meta_title' => t('module_form'),
			'content' => $this->load->view('constructors/form-results', array(
				'results' => $results,
				'locale' => $this->locale,
			)),
			'breadcrumbs' => H::breadcrumb(array(
				array('url' => '?route=constructors', 'title' => t('module_constructors')),
				array('url' => '?route=constructors/form', 'title' => t('module_form')),
				array('title' => t('results')),
			)),
		);
	}

	public function result(){
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT content, form_id FROM form_results WHERE id = {$id}");
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$result){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$this->db->exec("UPDATE form_results SET viewed = 1 WHERE viewed = 0 AND id = {$id}");
		$stmt = $this->db->query("SELECT fields FROM forms WHERE id = {$result['form_id']}");
		$fields = $stmt->fetchColumn();

		$fields = json_decode($fields, true);
		$result['content'] = json_decode($result['content'], true);
		foreach($fields as $field){
			$data = isset($result['content'][$field['key']]) ? $result['content'][$field['key']] : '';
			$table[] = array($field['label'], $data);
		}
		$body = H::table($table, array(), array('class' => 'table table-striped table-condensed'));
		$this->response->body = H::modal(t('view'), $body);
	}

	public function delresult(){
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT form_id FROM form_results WHERE id = {$id}");
		$form_id = $stmt->fetchColumn();
		$this->db->exec("DELETE FROM form_results WHERE id = {$id}");
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=constructors/form/results&id=' . $form_id);
	}

	public function toggleresult(){
		$id = (int)$this->request->get['id'];
		$stmt = $this->db->query("SELECT form_id FROM form_results WHERE id = {$id}");
		$form_id = $stmt->fetchColumn();
		$this->db->exec("UPDATE form_results SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END WHERE id = {$id}");
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form/results&id=' . $form_id);
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

		$data = array();
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
			'form_submitted' => t('form_submitted'),
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
			$i = 1;
			$fields = array();
			foreach ($this->request->post['fields'] as $field){
				$i++;
				$fields[$i] = $field;
				$fields[$i]['required'] = isset($field['required']) ? 1 : 0;
				$fields[$i]['hide_label'] = isset($field['hide_label']) ? 1 : 0;
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
				if ($notice['to'] == '' || $notice['subject'] == '' || $notice['subject'] == 'body'){
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

	public function delnotice(){
		$id = (int)$this->request->post['id'];
		$this->db->exec("DELETE FROM form_notices WHERE id = {$id}");
		$this->response->notify(t('deleted'));
	}

	public function cloneit(){
		$id = (int)$this->request->get['id'];
		if ($id < 1){
			throw new BaseException(t('error_empty_values_passed'));
		}

		$stmt = $this->db->prepare("INSERT INTO forms (template, name, description, success_text, submit_button, fields, form_attr, status)
			SELECT template, name, description, success_text, submit_button, fields, form_attr, status FROM forms
			WHERE id = :id");
		$stmt->execute(array('id' => $id));

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=constructors/form');
	}

	public function send(){
		if (!IS_POST || !isset($_SESSION['form_token_key'], $this->request->post['form_id'], $this->request->post[$_SESSION['form_token_key']]) ||
			$this->request->post[$_SESSION['form_token_key']] != $_SESSION['form_token_value']){
			throw new BaseException(t('error_empty_values_passed'));
		}

		unset($_SESSION['form_token_key'], $_SESSION['form_token_value']);

		$stmt = $this->db->prepare("SELECT success_text, fields FROM forms WHERE id = :id AND status = 1");
		$stmt->execute(array('id' => $this->request->post['form_id']));
		$form = $stmt->fetch(PDO::FETCH_ASSOC);
		if (empty($form)){
			return;
		}

		$fields = json_decode($form['fields'], true);
		$file_fields = $result = array();
		foreach ($fields as $field){
			if ($field['type'] != 'file' && $field['type'] != 'listing' && !isset($this->request->post[$field['key']])){
				throw new BaseException(t('error_empty_values_passed'));
			}

			if ($field['type'] == 'file'){
				$file_fields[$field['key']] = $field;
				$result[$field['key']] = '';
			} elseif ($field['type'] == 'listing'){
				if (!isset($this->request->post[$field['key']])){
					$result[$field['key']] = '';
					continue;
				}
				$source = explode("\r\n", $field['source']);
				if (strpos($source[0], '|') !== false){
					$option = array();
					foreach ($source as $row){
						$row = explode('|', $row);
						$option[$row[0]] = $row[1];
					}
					$source = $option;
				} else {
					$source = array_combine($source, $source);
				}

				if ($field['list_type'] == 'checkbox' && is_array($this->request->post[$field['key']])){
					$values = array();
					foreach($this->request->post[$field['key']] as $value){
						$values[] = $source[$value];
					}
					$result[$field['key']] = implode(', ', $values);
				} else {
					$result[$field['key']] = $source[$this->request->post[$field['key']]];
				}
			} elseif ($field['type'] == 'textarea'){
				$result[$field['key']] = nl2br($this->request->post[$field['key']]);
			} else {
				$result[$field['key']] = $this->request->post[$field['key']];
			}
		}

		$default_allowed = array('gif', 'jpeg', 'jpg', 'png', 'zip', 'rar', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
		if (!empty($file_fields)){
			$folder = DIR_ROOT . 'upload/files/form/';
			is_dir($folder) || mkdir($folder);
			foreach ($this->request->files as $key => $file){
				if (isset($file_fields[$key]) && $file['error'] == 0 && $file['size'] < 1048576 * 2){
					$allowed = empty($file_fields[$key]['allowed_files']) ? $default_allowed : explode(' ', $file_fields[$key]['allowed_files']);
					$arr = explode('.', strtolower($file['name']));
					$ext = end($arr);
					if (in_array($ext, $allowed)){
						$clean_name = convertToAscii($file['name']);
						move_uploaded_file($file['tmp_name'], $folder . $clean_name);
						$result[$key] = '<a href="http://' . $this->base . '/upload/files/form/' . $clean_name . '" target="_blank">' . $file['name'] . '</a>';
					}
				}
			}
		}

		$stmt = $this->db->prepare("INSERT INTO form_results (form_id, content, date, ip) values (:form_id, :content, :date, :ip)");
		$stmt->execute(array(
			'form_id' => $this->request->post['form_id'],
			'content' => json_encode($result),
			'date' => time(),
			'ip' => getip(),
		));

		$stmt = $this->db->prepare("SELECT * FROM form_notices WHERE form_id = :id");
		$stmt->execute(array('id' => $this->request->post['form_id']));
		$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($notices)){
			foreach ($result as $key => $val){
				$find[] = '#' . $key . '#';
				$replace[] = $val;
			}
			foreach ($notices as $notice){
				$to = str_replace("\r\n", ' ', $notice['to']);
				$to = str_replace($find, $replace, $to);

				$subject = '=?UTF-8?B?' . base64_encode($notice['subject']) . '?=';

				$message = htmlspecialchars_decode($notice['body']);
				$message = str_replace($find, $replace, $message);
				$message = wordwrap($message, 70);

				$from = str_replace("\r\n", ' ', $notice['from']);
				$from = str_replace($find, $replace, $from);
				$headers = implode("\r\n", array(
					'MIME-Version: 1.0',
					'Content-Type: text/html; charset=UTF-8',
					'Content-Transfer-Encoding: 8Bit',
					'From: ' . $from,
					'Reply-To: ' . $from,
					'X-Mailer: SyDES Form Constructor',
				));

				mail($to, $subject, $message, $headers);
			}
		}

		if (IS_AJAX){
			$this->response->body = array('message' => htmlspecialchars_decode($form['success_text']));
		} else {
			$back = isset($_SERVER['HTTP_REFERER']) ? str_replace('http://' . $_SERVER['HTTP_HOST'] . '/', '', $_SERVER['HTTP_REFERER']) : '';
			$this->response->redirect($back);
		}
	}
}