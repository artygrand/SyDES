<?php
/**
* SyDES :: box module for configure templates and layouts
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
 
class Templates extends Module{
	public $name = 'templates';

	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowed4html = array('view', 'file_edit', 'file_save', 'file_delete', 'layout_edit', 'layout_save', 'layout_delete', 'layout_add', 'file_saveas');

	/**
	* Sets the allowed actions for user via AJAX
	* @var array
	*/
	public static $allowed4ajax = array('modal_saveas', 'file_saveas', 'modal_layout_new', 'layout_add');

	/**
	* Sets the allowed actions for demo user
	* @var array
	*/
	public static $allowed4demo = array('view', 'layout_edit');

	function __construct(){
		$this->layouts = '../template/' . Admin::$siteConfig['template'] . '/layouts.db';
	}

	public function view(){
		header('X-XSS-Protection: 0');
		$layouts = $this->getLayouts();
		$files = $this->getFiles('html');
		unset($files['404.html']);
		if (count($layouts) < count($files)){
			$used = array();
			foreach($layouts as $layout){
				$used[] = $layout['file'];
			}
			foreach($files as $file){
				if (in_array($file, $used)) continue;
				$name = str_replace('.html', '', $file);
				$layouts[$name] = array(
					'name' => $name,
					'file' => $file,
					'left' => '',
					'right' => '',
					'top' => '',
					'bottom' => ''
				);
			}// TODO добавить проверку на новые файлы даже если их меньше макетов
			if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_saved'));
		}
		$main = render('module/templates/tpl/main.php', array(
			'files' => $this->getFiles(),
			'layouts' => $layouts
		));
		$crumbs[] = array('title' => lang('templates'));
		$r['contentCenter'] = $main;
		$r['title'] = lang('templates');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}
	
	public function file_edit(){
		header('X-XSS-Protection: 0');
		$path = '../template/' . Admin::$siteConfig['template'] . '/' . $_GET['file'];
		$ext = strrchr($_GET['file'], '.');
		$cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.19.0/';
		$r['cssfiles'][] = $cdn . 'codemirror.min.css';
		$r['jsfiles'][] = $cdn . 'codemirror.min.js';
		$r['jsfiles'][] = $cdn . 'addon/edit/matchbrackets.min.js';
		if($ext and file_exists($path)){
			if ($ext == '.css'){
				$r['jsfiles'][] = $cdn . 'mode/css/css.min.js';
				$code = 'css';
			} elseif ($ext == '.js'){
				$r['jsfiles'][] = $cdn . 'mode/javascript/javascript.min.js';
				$r['jsfiles'][] = $cdn . 'addon/edit/closebrackets.min.js';
				$code = 'javascript';
			} elseif ($ext == '.html'){
				$r['jsfiles'][] = $cdn . 'mode/javascript/javascript.min.js';
				$r['jsfiles'][] = $cdn . 'mode/css/css.min.js';
				$r['jsfiles'][] = $cdn . 'mode/xml/xml.min.js';
				$r['jsfiles'][] = $cdn . 'mode/htmlmixed/htmlmixed.min.js';
				$code = 'html';
			} else {
				throw new Exception('unauthorized_request');
			}
		} else {
			throw new Exception('unauthorized_request');
		}
		$r['js'] = '
		$(document).ready(function(){
			var editor = CodeMirror.fromTextArea(document.getElementById("code"), {mode:"text/' . $code . '", lineNumbers:true, lineWrapping:true, matchBrackets:true, autoCloseBrackets:true})
			editor.setSize(null, ($(".main").height()-80)+"px")
			$(document).on("mousedown","#modal-save",function(){
			$("#modal-form").append("<textarea name=\"code\" style=\"display:none\">"+editor.getValue()+"</textarea>")})
			
		})';
		$content = str_replace('<', '&lt;', file_get_contents($path));
		$main = '<textarea id="code" name="code" class="form-control" rows="30">' . $content . '</textarea>';
		$crumbs[] = array('title' => lang('templates'), 'url' => '?mod=templates');
		$crumbs[] = array('title' => lang('file_editing'));
		$r['title'] = lang('file_editing');
		$r['contentCenter'] = $main;
		$r['contentRight'] = render('module/templates/tpl/file-right.php', array(
			'files' => $this->getFiles(),
			'otherFiles' => getSelect($this->getFiles(), $_GET['file'], 'id="other" class="form-control" data-url="?mod=templates&act=file_edit&file="'),
			'thisFile' => $_GET['file']
		));
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = '?mod=templates&act=file_save&file=' . $_GET['file'];

		return $r;
	}
	
	public function file_save(){
		//if (!isAdmin){error} TODO
		$name = trim($_GET['file']);
		if (empty($name)) throw new Exception('no_value');
		$ext = strrchr($name, '.');
		if (!$ext) $name = properUri($name) . '.html';
		$path = '../template/' . Admin::$siteConfig['template'] . '/' . $name;
		if(file_put_contents($path, $_POST['code']) === false) throw new Exception(lang('not_saved'));
		//clearCache();
		if (isset($_GET['goto']) and $_GET['goto'] == 'view'){
			redirect('?mod=templates', lang('saved'), 'success');
		} else {
			redirect('?mod=templates&act=file_edit&file=' . $_GET['file'], lang('saved'), 'success');
		}
	}
	
	public function file_delete(){
		//if (!isAdmin){error} TODO
		
		$name = trim($_GET['file']);
		if (empty($name)) throw new Exception('no_value');
		if (strpos($name, '/') !== false) throw new Exception('unauthorized_request');
		$path = '../template/' . Admin::$siteConfig['template'] . '/' . $name;
		if (file_exists($path)){
			unlink($path);
		}
		redirect('?mod=templates', lang('deleted'), 'success');
	}
	
	public function layout_edit(){
		$layouts = $this->getLayouts();
		$layout = $layouts[$_GET['layout']];
		$layout['files'] = getSelect($this->getFiles('html'), $layout['file'], 'class="form-control" name="file"');

		$main = render('module/templates/tpl/layout.php', $layout);
		$crumbs[] = array('title' => lang('templates'), 'url' => '?mod=templates');
		$crumbs[] = array('title' => lang('layout_editing'));
		$r['title'] = lang('layout_editing');
		$r['contentCenter'] = $main;
		$r['contentRight'] = '<button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = '?mod=templates&act=layout_save&layout=' . $_GET['layout'];
		return $r;
	}
	
	public function layout_save(){
		$layouts = $this->getLayouts();
		$layouts[$_GET['layout']] = array(
			'name' => $_POST['name'],
			'file' => $_POST['file'],
			'left' => $_POST['left'],
			'right' => $_POST['right'],
			'top' => $_POST['top'],
			'bottom' => $_POST['bottom']
		);
		if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_saved'));
		redirect('?mod=templates', lang('saved'), 'success');
	}
	
	public function layout_delete(){
		$layouts = $this->getLayouts();
		unset($layouts[$_GET['layout']]);
		if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_deleted'));
		redirect('?mod=templates', lang('deleted'), 'success');
	}

	public function modal_saveas(){
		return array('modal' => array('title' => lang('save_as'),
		'content' => '<label class="control-label">' . lang('enter_filename') . '</label><input type="text" class="form-control" name="new-name">',
		'form_url' => '?mod=templates&act=file_saveas&file=' . $_GET['file']));
	}

	public function file_saveas(){
		//if (!isAdmin){error} TODO
		$name = trim($_POST['new-name']);
		if (empty($name)) throw new Exception('no_value');
		$ext = strrchr($name, '.');
		if (!$ext) $name = properUri($name) . strrchr($_GET['file'], '.');
		$path = '../template/' . Admin::$siteConfig['template'] . '/' . $name;
		if(file_put_contents($path, isset($_POST['code']) ? $_POST['code'] : '') === false) throw new Exception(lang('not_saved'));
		chmod($path, 0777);
		redirect('?mod=templates&act=file_edit&file=' . $name, lang('added'), 'success');
	}

	public function modal_layout_new(){
		return array('modal' => array('title' => lang('new_layout_creation'),
		'content' => '<label class="control-label">' . lang('enter_new_layout_identifier') . '</label><input type="text" class="form-control" name="new-name">',
		'form_url' => '?mod=templates&act=layout_add'));
	}
	
	public function layout_add(){
		//if (!isAdmin){error} TODO
		$name = properUri(trim($_POST['new-name']));
		if (empty($name)) throw new Exception('no_value');
		$layouts = $this->getLayouts();
		$layouts[$name] = array(
			'name' => '',
			'file' => '',
			'left' => '',
			'right' => '',
			'top' => '',
			'bottom' => ''
		);
		if (!file_exists($this->layouts)){
			file_put_contents($this->layouts, '');
			chmod($this->layouts, 0777);
		}
		if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_saved'));
		redirect('?mod=templates&act=layout_edit&layout=' . $name, lang('added'), 'success');
	}

	private function getFiles($exts = array('html','css','js')){
		$path = '../template/' . Admin::$siteConfig['template'] . '/';
		foreach((array)$exts as $ext){
			foreach(glob($path . '*.' . $ext) as $file){
				$file = str_replace($path, '', $file);
				$files[$file] = $file;
			}
		}
		return $files;
	}

	private function getLayouts(){
		return file_exists($this->layouts) ? unserialize(file_get_contents($this->layouts)) : array();
	}
}
?>