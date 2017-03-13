<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class LayoutController extends Controller{
	public $name = 'layout';

	public function __construct(){
		parent::__construct();
		$this->load->model('templates');
		$this->templates_model->prepare();
		$this->load->language('module_templates');
	}

	public function edit(){
		if (!isset($this->request->get['layout'])){
			throw new BaseException(t('error_page_not_found'));
		}

		$layouts = $this->templates_model->settings['layouts'];
		if (isset($layouts[$this->request->get['layout']])){
			$layout = $layouts[$this->request->get['layout']];
			$layout['html'] = str_replace(array('&', '<'), array('&amp;', '&lt;'), file_get_contents($this->templates_model->template_path . 'layout/' . $this->request->get['layout'] . '.html'));
			$source_file = $this->templates_model->template_path . 'layout/' . $this->request->get['layout'] . '.html';
		} else {
			$layout = array('name' => '', 'file' => 'page.html', 'html' => '{content}');
			$source_file = $this->templates_model->settings_file;
		}

		$files = $this->templates_model->getFiles('html');
		unset($files['404.html'], $files['403.html'], $files['503.html']);

		$layout['files'] = H::select('file', $layout['file'], $files, 'class="form-control"');

		$cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.24.2/';
		$this->response->style[] = $cdn . 'codemirror.min.css';
		$script = array();
		$script[] = $cdn . 'codemirror.min.js';
		$script[] = $cdn . 'addon/edit/matchbrackets.min.js';
		$script[] = $cdn . 'addon/edit/closebrackets.min.js';
		$script[] = $cdn . 'mode/htmlmixed/htmlmixed.min.js';
		$script[] = $cdn . 'mode/xml/xml.min.js';
		$script[] = $cdn . 'mode/javascript/javascript.min.js';
		$script[] = $cdn . 'mode/css/css.min.js';
		$script[] = $cdn . 'mode/clike/clike.min.js';
		$script[] = $cdn . 'mode/php/php.min.js';
		$layout['mode'] = 'application/x-httpd-php';
		$this->response->script = $script;

		$data = array();
		$data['content'] = $this->load->view('templates/layout', $layout);
		$data['sidebar_right'] = H::saveButton($source_file) . $this->user->getMastercodeInput() . '
		<div class="form-group">' . $this->templates_model->getIblocks() . '</div>';

		$data['meta_title'] = t('layout_editing');
		$crumbs = array(
			array('title' => t('templates'), 'url' => '?route=templates&tpl=' . $this->templates_model->template),
			array('title' => t('layout_editing'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$data['form_url'] = '?route=templates/layout/save&tpl=' . $this->templates_model->template . '&layout=' . $this->request->get['layout'];

		$this->response->addHeader('X-XSS-Protection: 0');
		$this->response->data = $data;
	}

	public function save(){
		$key = $this->request->get['layout'] == 'clone' ? convertToAscii($this->request->post['key']) : $this->request->get['layout'];
		$url = '?route=templates/layout/edit&tpl=' . $this->templates_model->template .'&layout=' . $key;

		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', $url);
		}

		$this->templates_model->settings['layouts'][$key] = array(
			'name' => $this->request->post['name'],
			'file' => $this->request->post['file'],
		);

		file_put_contents($this->templates_model->template_path . 'layout/' . $key . '.html', $_POST['html']);
		write_ini_file($this->templates_model->settings, $this->templates_model->settings_file, true);

		elog('User is saved layout ' . $key);
		$this->response->notify(t('saved'));
		$this->response->redirect($url);
	}

	public function delete(){
		if (!isset($this->request->get['layout'])){
			throw new BaseException(t('error_page_not_found'));
		}

		$url = '?route=templates&tpl=' . $this->templates_model->template;
		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', $url);
		}

		unset($this->templates_model->settings['layouts'][$this->request->get['layout']]);
		unlink($this->templates_model->template_path . 'layout/' . $this->request->get['layout'] . '.html');
		write_ini_file($this->templates_model->settings, $this->templates_model->settings_file, true);

		elog('User is deleted layout ' . $this->request->get['layout']);
		$this->response->notify(t('deleted'));
		$this->response->redirect($url);
	}

	public function cloneit(){
		$layouts = $this->templates_model->settings['layouts'];
		if (!isset($this->request->get['layout']) || !IS_AJAX || !isset($layouts[$this->request->get['layout']])){
			throw new BaseException(t('error_page_not_found'));
		}

		$body = H::form(array(
			'name' => array(
				'label' => t('layout_name'),
				'type' => 'string',
				'value' => '',
				'attr' => 'class="form-control" placeholder="' . t('article') . '" required'
			),
			'key' => array(
				'label' => t('layout_key'),
				'type' => 'string',
				'value' => '',
				'attr' => 'class="form-control" placeholder="article" required'
			),
			'html' => array(
				'type' => 'hidden',
				'value' => str_replace(array('&', '"'), array('&amp;', '&quot;'), file_get_contents($this->templates_model->template_path . 'layout/' . $this->request->get['layout'] . '.html')),
			),
			'file' => array(
				'type' => 'hidden',
				'value' => $layouts[$this->request->get['layout']]['file'],
			)
		));
		$footer = H::button(t('save'), 'submit', 'class="btn btn-primary"');
		$this->response->body = H::modal(t('layout_cloning'), $body . $this->user->getMastercodeInput(), $footer, '?route=templates/layout/save&tpl=' . $this->templates_model->template . '&layout=clone');
	}
}