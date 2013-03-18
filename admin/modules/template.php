<?php
/**
* Box module. Template manager.
* @varsion: 1.2.1
* @author ArtyGrand
*/
 
class Template extends Module{
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'edit', 'save');
	
	/**
	* Instantly redirects to index file editor
	* @return array
	*/
	public function view(){
		$p['redirect']['url'] = '?mod=template&act=edit&file=index.html';
		return $p;
	}

	/*
	* Gets template editor form
	* @return array
	*/
	public function edit(){
		$path = '../templates/' . Core::$config['template'] . '/' . $_GET['file'];
		if(preg_match('![^\.\w-]!', $_GET['file']) or !in_array(strrchr($_GET['file'], '.'), array('.html', '.css', '.js')) or !is_file($path)){
			throw new Exception(lang('unauthorized_request'));
		}

		$files = globRecursive('../templates/' . Core::$config['template'], array('html', 'css', 'js'));
		$content = file_get_contents($path);
		$content = str_replace("<", "&lt;", $content);
		$p['content'] = '
	<form action="?mod=template&act=save&file=' . $_GET['file'] . '" method="post">
		<table class="full form">
			<tr>
				<td>
					<div class="title">' . lang('content') . '</div>
					<div><textarea id="words" name="content" class="full" autofocus>' . $content . '</textarea></div>
				</td>
				<td>
					' . getSaveButton($path) . '
					<div class="title">' . lang('other_files') . ':</div><div>' . getSelect($files, 'alias', $_GET['file'], 'name="other" class="full"') . '</div>
					' . getCodeInput() . '
				</td>
			</tr>
		</table>
	</form>
	<!--<script src="/admin/ckeditor/ckeditor.js"></script>
<script>
var editor = CKEDITOR.replace("words",{uiColor:"#CFB39C",height:600,fullPage:true,toolbar:[["Source"]]});
</script>-->
	';
		$p['breadcrumbs'] = '<span>' . lang('template') . '</span> &gt; ' . lang('editor') . ' ' . $_GET['file'];
		
		$p['jquery'] = '$(\'select[name="other"]\').change(function(){location.href = \'?mod=template&act=edit&file=\' + $(this).val()})';
		return $p;
	}

	/**
	* Save template
	* @return array
	*/
	public function save(){
		if (isset($_GET['file'])){
			if (!canEdit() or !in_array(strrchr($_GET['file'], '.'), array('.html', '.css', '.js'))){
				throw new Exception(lang('unauthorized_request'));
			}
			if(file_put_contents('../templates/' . Core::$config['template'] . '/' . $_GET['file'], $_POST['content']) === false){
				throw new Exception(lang('not_saved'));
			}
			clearAllCache();
			$p['redirect'] = 1;
			return $p;
		}
		throw new Exception(lang('no_value'));
	}
}
?>