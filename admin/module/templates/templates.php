<?php
/**
* SyDES :: box module for configure templates and layouts
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Templates extends Module{
	public $name = 'templates';
	public static $allowed4html = array('view', 'file_edit', 'file_save', 'file_delete', 'layout_edit', 'layout_save', 'layout_delete', 'layout_add', 'file_saveas');
	public static $allowed4ajax = array('modal_saveas', 'file_saveas', 'modal_layout_new', 'layout_add');
	public static $allowed4demo = array('view', 'layout_edit', 'file_edit');

	function __construct(){
		$this->template = TEMPLATE_DIR . Admin::$siteConfig['template'] . '/';
		$this->layouts = $this->template . 'layouts.db';
	}

	public function view(){
		header('X-XSS-Protection: 0');
		$this->createLayouts();
		$main = render('module/templates/tpl/view.php', array(
			'files' => $this->getFiles(),
			'layouts' => $this->getLayouts()
		));
		$crumbs[] = array('title' => lang('templates'));
		$r['contentCenter'] = $main;
		$r['title'] = lang('templates');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}

	public function file_edit(){
		header('X-XSS-Protection: 0');
		$path = $this->template . $_GET['file'];
		$ext = strrchr($_GET['file'], '.');
		$cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.19.0/';
		$r['cssfiles'][] = $cdn . 'codemirror.min.css';
		$r['jsfiles'][] = $cdn . 'codemirror.min.js';
		$r['jsfiles'][] = $cdn . 'addon/edit/matchbrackets.min.js';
		if(!$ext or !file_exists($path)){
			throw new Exception('unauthorized_request');
		}
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
		$r['js'] = '
		$(document).ready(function(){
			var editor = CodeMirror.fromTextArea(document.getElementById("code"), {mode:"text/' . $code . '", lineNumbers:true, lineWrapping:true, matchBrackets:true, autoCloseBrackets:true,
			indentUnit:4, indentWithTabs:true, enterMode:"keep", tabMode:"shift"})
			editor.setSize(null, ($(".main").height()-80)+"px")
			$(document).on("mousedown","#modal-save",function(){$("#modal-form").append("<textarea name=\"code\" style=\"display:none\">"+editor.getValue()+"</textarea>")})
		})';
		$content = str_replace('<', '&lt;', file_get_contents($path));
		$main = '<textarea id="code" name="code" class="form-control" rows="30">' . $content . '</textarea>';
		$button = '
<div class="form-group">
	<div class="btn-group btn-block with-dropdown">
		<a href="#" class="btn btn-primary submit btn-block" id="btn-save" data-act="apply">' . lang('save') . '</a>
		<div class="btn-group">
			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			<ul class="dropdown-menu pull-right">
				<li><a href="#" class="submit" data-act="save">' . lang('save_and_back') . '</a></li>
				<li><a href="#" class="ajaxmodal" data-url="?mod=templates&act=modal_saveas&file=' . $_GET['file'] . '">' . lang('save_as') . '</a></li>
			</ul>
		</div>
	</div>
</div>';
		
		$right = Admin::getSaveButton(SITE_DIR . Admin::$site . '/config.db', $button) . User::getMasterInput();
		$right .= '<div class="form-group"><label class="control-label">' . lang('other_files'). '</label>' . getSelect($this->getFiles(), $_GET['file'], 'id="other" class="form-control" data-url="?mod=templates&act=file_edit&file="') . '</div>';
		$right .= '<div class="form-group"><label>' . lang('iblock_list') . '</label><pre>' . $this->getIblocks() . '</pre></div>';
		$crumbs[] = array('title' => lang('templates'), 'url' => '?mod=templates');
		$crumbs[] = array('title' => lang('file_editing'));
		$r['title'] = lang('file_editing');
		$r['contentCenter'] = $main;
		$r['contentRight'] = $right;
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = '?mod=templates&act=file_save&file=' . $_GET['file'];
		return $r;
	}

	public function file_save(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		$name = trim($_GET['file']);
		if (empty($name)) throw new Exception('no_value');
		$ext = strrchr($name, '.');
		if (!$ext){
			$name = properUri($name) . '.html';
		} elseif (!in_array($ext, array('.html','.css','.js'))){
			throw new Exception('unauthorized_request');
		}
		$path = $this->template . $name;
		if(file_put_contents($path, $_POST['code']) === false) throw new Exception(lang('not_saved'));
		clearCache();
		Admin::log('User is saved file ' . $_GET['file']);
		if (isset($_GET['goto']) and $_GET['goto'] == 'save'){
			redirect('?mod=templates', lang('saved'), 'success');
		} else {
			redirect('?mod=templates&act=file_edit&file=' . $_GET['file'], lang('saved'), 'success');
		}
	}

	public function file_delete(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}		
		$name = trim($_GET['file']);
		if (empty($name)) throw new Exception('no_value');
		if (strpos($name, '/') !== false) throw new Exception('unauthorized_request');
		$path = $this->template . $name;
		if (file_exists($path)){
			unlink($path);
			Admin::log('User is deleted file ' . $name);
		}
		redirect('?mod=templates', lang('deleted'), 'success');
	}

	public function layout_edit(){
		$layouts = $this->getLayouts();
		$layout = $layouts[$_GET['layout']];
		$layout['files'] = getSelect($this->getFiles('html'), $layout['file'], 'class="form-control" name="file"');
		$main = render('module/templates/tpl/layout.php', $layout);
		$right = Admin::getSaveButton($this->layouts) . User::getMasterInput() . '<div class="form-group"><label>' . lang('iblock_list') . '</label><pre>' . $this->getIblocks() . '</pre></div>';
		$crumbs[] = array('title' => lang('templates'), 'url' => '?mod=templates');
		$crumbs[] = array('title' => lang('layout_editing'));
		$r['title'] = lang('layout_editing');
		$r['contentCenter'] = $main;
		$r['contentRight'] = $right;
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = '?mod=templates&act=layout_save&layout=' . $_GET['layout'];
		return $r;
	}

	public function layout_save(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
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
		Admin::log('User is saved layout ' . $_GET['layout']);
		redirect('?mod=templates', lang('saved'), 'success');
	}

	public function layout_delete(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		$layouts = $this->getLayouts();
		unset($layouts[$_GET['layout']]);
		if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_deleted'));
		Admin::log('User is deleted layout ' . $_GET['layout']);
		redirect('?mod=templates', lang('deleted'), 'success');
	}

	public function modal_saveas(){
		return array('modal' => array('title' => lang('save_as'),
		'content' => '<div class="form-group"><label class="control-label">' . lang('enter_filename') . '</label><input type="text" class="form-control" name="new-name"></div>' . User::getMasterInput(),
		'form_url' => '?mod=templates&act=file_saveas&file=' . $_GET['file']));
	}

	public function file_saveas(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		$name = trim($_POST['new-name']);
		if (empty($name)) throw new Exception('no_value');
		$ext = strrchr($name, '.');
		if (!$ext) $name = properUri($name) . strrchr($_GET['file'], '.');
		$path = $this->template . $name;
		if(file_put_contents($path, isset($_POST['code']) ? $_POST['code'] : '') === false) throw new Exception(lang('not_saved'));
		chmod($path, 0777);
		Admin::log('User is saved file ' . $name);
		redirect('?mod=templates&act=file_edit&file=' . $name, lang('added'), 'success');
	}

	public function modal_layout_new(){
		return array('modal' => array('title' => lang('new_layout_creation'),
		'content' => '<div class="form-group"><label class="control-label">' . lang('enter_new_layout_identifier') . '</label><input type="text" class="form-control" name="new-name"></div>' . User::getMasterInput(),
		'form_url' => '?mod=templates&act=layout_add'));
	}

	public function layout_add(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		$name = properUri(trim($_POST['new-name']));
		if (empty($name)) throw new Exception('no_value');
		$layouts = $this->getLayouts();
		$layouts[$name] = array('name' => '', 'file' => '', 'left' => '', 'right' => '', 'top' => '', 'bottom' => '');
		if (!file_exists($this->layouts)){
			file_put_contents($this->layouts, '');
			chmod($this->layouts, 0777);
			Admin::log('User is saved layout ' . $name);
		}
		if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_saved'));
		redirect('?mod=templates&act=layout_edit&layout=' . $name, lang('added'), 'success');
	}

	public function getFiles($exts = array('html','css','js')){
		foreach((array)$exts as $ext){
			foreach(glob($this->template . '*.' . $ext) as $file){
				$file = str_replace($this->template, '', $file);
				$files[$file] = $file;
			}
		}
		return $files;
	}

	public function getLayouts(){
		return file_exists($this->layouts) ? unserialize(file_get_contents($this->layouts)) : array();
	}

	public function createLayouts(){
		$layouts = $this->getLayouts();
		$used = array();
		foreach($layouts as $layout){
			$used[] = $layout['file'];
		}
		$files = $this->getFiles('html');
		unset($files['404.html']);
		$need = array_diff($files,$used);
		if (count($need)){
			foreach($need as $file){
				$name = str_replace('.html', '', $file);
				$layouts[$name] = array('name' => $name, 'file' => $file, 'left' => '', 'right' => '', 'top' => '', 'bottom' => '');
			}
			if(file_put_contents($this->layouts, serialize($layouts)) === false) throw new Exception(lang('not_saved'));
		}
	}

	public function getIblocks(){
		$path = glob(SYS_DIR . 'iblock/*.iblock');
		$pre = '';
		if($path){
			foreach($path as $file){
				$file = str_replace(array(SYS_DIR . 'iblock/','.iblock'), array('{iblock:','}'), $file);
				$pre .= $file . PHP_EOL;
			}
		}
		return $pre;
	}
}
?>