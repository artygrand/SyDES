<?php
/**
* SyDES :: box module for creating and editing infoblocks
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
 
class Iblocks extends Module{
	public $name = 'iblocks';
	public static $allowed4html = array('view', 'edit', 'save', 'delete');
	public static $allowed4ajax = array('modal_saveas', 'saveas');
	public static $allowed4demo = array('view', 'edit');

	function __construct(){
		$this->dir = SYS_DIR . 'iblock/';
		if (!isset($_GET['iblock'])){$_GET['iblock'] = '';}
		$this->path = $this->dir . $_GET['iblock'] . '.iblock';
	}

	public function view(){
		header('X-XSS-Protection: 0');
		$crumbs[] = array('title' => lang('iblocks'));
		$r['contentCenter'] = render('module/iblocks/tpl/view.php', array('iblocks' => $this->getIblocks()));
		$r['title'] = lang('iblocks');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $r;
	}

	public function edit(){
		header('X-XSS-Protection: 0');
		if(!file_exists($this->path)){
			throw new Exception('unauthorized_request');
		}
		$cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/3.19.0/';
		$r['cssfiles'][] = $cdn . 'codemirror.min.css';
		$r['jsfiles'][] = $cdn . 'codemirror.min.js';
		$r['jsfiles'][] = $cdn . 'addon/edit/matchbrackets.min.js';
		$r['jsfiles'][] = $cdn . 'addon/edit/closebrackets.min.js';
		$r['jsfiles'][] = $cdn . 'mode/htmlmixed/htmlmixed.min.js';
		$r['jsfiles'][] = $cdn . 'mode/xml/xml.min.js';
		$r['jsfiles'][] = $cdn . 'mode/javascript/javascript.min.js';
		$r['jsfiles'][] = $cdn . 'mode/css/css.min.js';
		$r['jsfiles'][] = $cdn . 'mode/clike/clike.min.js';
		$r['jsfiles'][] = $cdn . 'mode/php/php.min.js';
		$r['js'] = '
		$(document).ready(function(){
			var editor = CodeMirror.fromTextArea(document.getElementById("code"), {mode:"application/x-httpd-php", lineNumbers:true, lineWrapping:true, matchBrackets:true,
			autoCloseBrackets:true, indentUnit:4, indentWithTabs:true, enterMode:"keep", tabMode:"shift"})
			editor.setSize(null, ($(".main").height()-80)+"px")
			$(document).on("mousedown","#modal-save",function(){$("#modal-form").append("<textarea name=\"code\" style=\"display:none\">"+editor.getValue()+"</textarea>")})
		})';
		$content = str_replace('<', '&lt;', file_get_contents($this->path));
		$main = '<textarea id="code" name="code" class="form-control" rows="30">' . $content . '</textarea>';
		$button = '
<div class="form-group">
	<div class="btn-group btn-block with-dropdown">
		<a href="#" class="btn btn-primary submit btn-block" id="btn-save" data-act="apply">' . lang('save') . '</a>
		<div class="btn-group">
			<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			<ul class="dropdown-menu pull-right">
				<li><a href="#" class="submit" data-act="save">' . lang('save_and_back') . '</a></li>
				<li><a href="#" class="ajaxmodal" data-url="?mod=iblocks&act=modal_saveas&iblock=' . $_GET['iblock'] . '">' . lang('save_as') . '</a></li>
			</ul>
		</div>
	</div>
</div>';
		
		$right = Admin::getSaveButton($this->path, $button) . User::getMasterInput();
		$right .= '<div class="form-group"><label class="control-label">' . lang('other_files'). '</label>' . getSelect($this->getIblocks(), $_GET['iblock'], 'id="other" class="form-control" data-url="?mod=iblocks&act=edit&iblock="') . '</div>';
		$crumbs[] = array('title' => lang('iblocks'), 'url' => '?mod=iblocks');
		$crumbs[] = array('title' => lang('file_editing'));
		$r['title'] = lang('file_editing');
		$r['contentCenter'] = $main;
		$r['contentRight'] = $right;
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['form_url'] = '?mod=iblocks&act=save&iblock=' . $_GET['iblock'];
		return $r;
	}

	public function save(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		if(!file_exists($this->path)){
			throw new Exception('unauthorized_request');
		}
		if(file_put_contents($this->path, $_POST['code']) === false) throw new Exception(lang('not_saved'));
		clearCache();
		Admin::log('User is saved iblock ' . $_GET['iblock']);
		if (isset($_GET['goto']) and $_GET['goto'] == 'view'){
			redirect('?mod=iblocks', lang('saved'), 'success');
		} else {
			redirect('?mod=iblocks&act=edit&iblock=' . $_GET['iblock'], lang('saved'), 'success');
		}
	}

	public function delete(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}		
		if (file_exists($this->path)){
			unlink($this->path);
			Admin::log('User is deleted iblock ' . $_GET['iblock']);
		}
		
		redirect('?mod=iblocks', lang('deleted'), 'success');
	}

	public function modal_saveas(){
		return array('modal' => array('title' => lang('add'),
		'content' => '<div class="form-group"><label class="control-label">' . lang('enter_iblock_name') . '</label><input type="text" class="form-control" name="new-name"></div>' . User::getMasterInput(),
		'form_url' => '?mod=iblocks&act=saveas&iblock=' . $_GET['iblock']));
	}

	public function saveas(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		$name = trim($_POST['new-name']);
		if (empty($name)) throw new Exception('no_value');
		$name = properUri($name);
		$path = $this->dir . $name . '.iblock';
		if(file_put_contents($path, isset($_POST['code']) ? $_POST['code'] : '') === false) throw new Exception(lang('not_saved'));
		chmod($path, 0777);
		Admin::log('User is saved iblock ' . $_GET['iblock']);
		redirect('?mod=iblocks&act=edit&iblock=' . $name, lang('added'), 'success');
	}

	private function getIblocks(){
		foreach(glob($this->dir . '*.iblock') as $file){
			$file = str_replace(array($this->dir, '.iblock'), '', $file);
			$files[$file] = $file;
		}
		return $files;
	}
}
?>