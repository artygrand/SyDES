<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class IblocksController extends Controller{
	public $name = 'iblocks';

	public function index(){
		$this->response->data = array(
			'content' => $this->load->view('iblocks/index', array('iblocks' => $this->getIblocks())),
			'sidebar_left' => ' ',
			'sidebar_right' => ' ',
			'meta_title' => t('iblocks'),
			'breadcrumbs' => H::breadcrumb(array(
				array('title' => t('iblocks'))
			)),
		);
	}

	public function edit(){
		if (!isset($this->request->get['iblock']) || !is_file(DIR_IBLOCK . $this->request->get['iblock'] . '/iblock.php')){
			throw new BaseException(t('error_page_not_found'));
		}

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
		$mode = 'application/x-httpd-php';
		$this->response->script = $script;

		$path = DIR_IBLOCK . $this->request->get['iblock'] . '/iblock.php';
		$data = array();
		$data['content'] = $this->load->view('templates/file', array(
			'content' => str_replace(array('&', '<'), array('&amp;', '&lt;'), file_get_contents($path)),
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
		
		$data['sidebar_right'] .= '<div class="form-group">
			<label class="control-label">' . t('other_iblocks'). '</label>
			' . H::select('other', $this->request->get['iblock'], $this->getIblocks(), 'class="form-control goto" data-url="?route=iblocks/edit&iblock="') . '
		</div>';

		$data['meta_title'] = t('iblock_editing');
		$crumbs = array(
			array('title' => t('iblocks'), 'url' => '?route=iblocks'),
			array('title' => t('iblock_editing'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);
		$data['form_url'] = '?route=iblocks/save&iblock=' . $this->request->get['iblock'];

		$this->response->data = $data;
	}

	public function save(){
		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', '?route=iblocks');
		}

		$iblock = $this->request->get['iblock'] == 'clone' ? $this->request->post['iblock'] : $this->request->get['iblock'];
		$path = DIR_IBLOCK . $iblock . '/iblock.php';

		if (isset($_POST['code'])){ // old or cloned
			$code = $_POST['code'];
		} elseif (!is_file($path)){ // new
			$code = '';
		} else {
			throw new BaseException(t('error_file_already_exists'), 'danger', '?route=iblocks');
		}

		if (!is_dir(dirname($path))){
			mkdir(dirname($path), 0777, true);
		}

		file_put_contents($path, $code);
		elog('User is saved iblock ' . $iblock);
		$this->response->notify(t('saved'));
		if (!IS_AJAX){			
			if (isset($this->request->post['act']) && $this->request->post['act'] == 'back'){
				$this->response->redirect('?route=iblocks');
			} else {
				$this->response->redirect('?route=iblocks/edit&iblock=' . $iblock);
			}
		}
	}

	public function delete(){
		$url = '?route=iblocks';
		if (!isset($this->request->get['iblock'])){
			throw new BaseException(t('error_page_not_found'));
		}
		if (!$this->user->isAdmin()){
			throw new BaseException(t('error_mastercode_needed'), 'warning', $url);
		}
		$this->confirm(sprintf(t('confirm_want_to_delete'), t('iblock') . ' ' . $this->request->get['iblock']), $url);

		if (file_exists(DIR_IBLOCK . $this->request->get['iblock'] . '/iblock.php')){
			foreach (glob(DIR_IBLOCK . $this->request->get['iblock'] . '/*') as $path){
				unlink($path);
			}
			rmdir(DIR_IBLOCK . $this->request->get['iblock']);
			elog('User is deleted iblock ' . $this->request->get['iblock']);
		}
		$this->response->notify(t('deleted'));
		$this->response->redirect($url);
	}

	public function cloneit(){
		if (!IS_AJAX || !isset($this->request->get['iblock']) || !is_file(DIR_IBLOCK . $this->request->get['iblock'] . '/iblock.php')){
			throw new BaseException(t('error_page_not_found'));
		}

		$body = H::form(array(
			'iblock' => array(
				'label' => t('iblock_name'),
				'type' => 'string',
				'value' => $this->request->get['iblock'],
				'attr' => 'class="form-control" placeholder="my_infoblock" required'
			),
			'code' => array(
				'type' => 'textarea',
				'value' => str_replace(array('&', '<'), array('&amp;', '&lt;'), file_get_contents(DIR_IBLOCK . $this->request->get['iblock'] . '/iblock.php')),
				'attr' => 'style="display:none;"'
			)
		));
		$footer = H::button(t('save'), 'submit', 'class="btn btn-primary"');
		$this->response->body = H::modal(t('iblock_cloning'), $body . $this->user->getMastercodeInput(), $footer, '?route=iblocks/save&iblock=clone');
	}

	private function getIblocks(){
		$files = array();
		foreach (glob(DIR_IBLOCK . '*') as $file){
			$files[] = str_replace(DIR_IBLOCK, '', $file);
		}
		return $files;
	}
}