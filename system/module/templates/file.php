<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class FileController extends Controller{
	public $name = 'file';

	public function __construct(){
		parent::__construct();
		$this->load->model('templates');
		$this->templates_model->prepare();
		$this->load->language('module_templates');
	}

	public function edit(){
		if (!isset($this->request->get['file']) or !is_file($this->templates_model->template_path . $this->request->get['file'])){
			throw new BaseException(t('error_page_not_found'));
		}

		$cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.19.0/';
		$this->response->style[] = $cdn . 'codemirror.min.css';
		$script[] = $cdn . 'codemirror.min.js';
		$script[] = $cdn . 'addon/edit/matchbrackets.min.js';

		$ext = strrchr($this->request->get['file'], '.');
		if ($ext == '.css'){
			$script[] = $cdn . 'mode/css/css.min.js';
			$mode = 'text/css';
		} elseif ($ext == '.js'){
			$script[] = $cdn . 'mode/javascript/javascript.min.js';
			$script[] = $cdn . 'addon/edit/closebrackets.min.js';
			$mode = 'text/javascript';
		} elseif ($ext == '.html'){
			$script[] = $cdn . 'mode/javascript/javascript.min.js';
			$script[] = $cdn . 'mode/css/css.min.js';
			$script[] = $cdn . 'mode/xml/xml.min.js';
			$script[] = $cdn . 'mode/htmlmixed/htmlmixed.min.js';
			$mode = 'text/html';
		} elseif($ext == '.php'){
			$script[] = $cdn . 'addon/edit/closebrackets.min.js';
			$script[] = $cdn . 'mode/htmlmixed/htmlmixed.min.js';
			$script[] = $cdn . 'mode/xml/xml.min.js';
			$script[] = $cdn . 'mode/javascript/javascript.min.js';
			$script[] = $cdn . 'mode/css/css.min.js';
			$script[] = $cdn . 'mode/clike/clike.min.js';
			$script[] = $cdn . 'mode/php/php.min.js';
			$mode = 'application/x-httpd-php';
		}
		$this->response->script = $script;

		$path = $this->templates_model->template_path . $this->request->get['file'];
		$data['content'] = $this->load->view('templates/file', array(
			'content' => str_replace('<', '&lt;', file_get_contents($path)),
			'mode' => $mode
		));

		$button = '
			<div class="btn-group btn-block">
				<a class="col-xs-10 btn btn-primary submit">' . t('save') . '</a>
				<button type="button" class="col-xs-2 btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li><button type="submit" name="act" value="back">' . t('save_and_back') . '</button></li>
				</ul>
			</div>';

		$data['sidebar_right'] = H::saveButton($path, $button) . $this->user->getMastercodeInput();
		if ($ext != '.php'){
			$data['sidebar_right'] .= '<div class="form-group">
			<label class="control-label">' . t('other_files'). '</label>
			' . H::select('other', $this->request->get['file'], $this->templates_model->getFiles(), 'class="form-control goto" data-url="?route=templates/file/edit&tpl=' . $this->templates_model->template . '&file="') . '
			</div>
			<div class="form-group">' . $this->templates_model->getIblocks() . '</div>';
		}

		$data['meta_title'] = t('file_editing');
		$crumbs = array(
			array('title' => t('templates'), 'url' => '?route=templates&tpl=' . $this->templates_model->template),
			array('title' => t('file_editing'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$data['form_url'] = '?route=templates/file/save&tpl=' . $this->templates_model->template . '&file=' . $this->request->get['file'];

		$this->response->addHeader('X-XSS-Protection: 0');
		$this->response->data = $data;
	}

	public function save(){
		$filename = $this->request->get['file'] == 'clone' ? $this->request->post['file'] : $this->request->get['file'];
		$path = $this->templates_model->template_path . $filename;

		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=templates&tpl=' . $this->templates_model->template);
		}

		if (isset($_POST['code'])){ // old file
			$code = $_POST['code'];
		} elseif (!is_file($path)){ // new
			if (preg_match('/iblock\/([\w-]+)/', $filename, $iblock) and is_file(DIR_IBLOCK . $iblock[1] . '/default.php')){ // iblock
				$code = file_get_contents(DIR_IBLOCK . $iblock[1] . '/default.php');
			} else {
				$code = '';
			}
		} else {
			throw new BaseException(t('error_mastercode_needed'), 'danger', '?route=templates&tpl=' . $this->templates_model->template);
		}

		if (!is_dir(dirname($path))){
			mkdir(dirname($path), 0777, true);
		}

		file_put_contents($path, $code);
		elog('User is saved file ' . $filename);
		$this->response->notify(t('saved'));
		if (!IS_AJAX){			
			if (isset($this->request->post['act']) and $this->request->post['act'] == 'back'){
				$this->response->redirect('?route=templates&tpl=' . $this->templates_model->template);
			} else {
				$this->response->redirect('?route=templates/file/edit&tpl=' . $this->templates_model->template . '&file=' . $filename);
			}
		}
	}

	public function delete(){
		if (!isset($this->request->get['file'])){
			throw new BaseException(t('error_page_not_found'));
		}

		$url = '?route=templates&tpl=' . $this->templates_model->template;
		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', $url);
		}

		$path = $this->templates_model->template_path . $this->request->get['file'];
		if (is_file($path)){
			unlink($path);
			elog('User is deleted file ' . $this->request->get['file']);
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect($url);
	}

	public function cloneit(){
		if (!IS_AJAX or !isset($this->request->get['file']) or !is_file($this->templates_model->template_path . $this->request->get['file'])){
			throw new BaseException(t('error_page_not_found'));
		}

		$body = H::form(array(
			'file' => array(
				'label' => t('filename'),
				'type' => 'text',
				'value' => $this->request->get['file'],
				'attr' => 'class="form-control" placeholder="home.html" required'
			),
			'code' => array(
				'type' => 'textarea',
				'value' => str_replace('<', '&lt;', file_get_contents($this->templates_model->template_path . $this->request->get['file'])),
				'attr' => 'style="display:none;"'
			)
		));
		$footer = H::button(t('save'), 'submit', 'class="btn btn-primary"');
		$this->response->body = H::modal(t('file_cloning'), $body . $this->user->getMastercodeInput(), $footer, '?route=templates/file/save&tpl=' . $this->templates_model->template . '&file=clone');
	}
}