<?php
/**
* Box module. Infoblocks (vidgets) manager.
* @varsion: 1.1.3
* @author ArtyGrand
*/
 
class Iblock extends Module{
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'edit', 'save', 'delete');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowedAjaxActions = array('add');
	
	function __construct(){
		$this -> setModuleName();
		$this -> format = '
		<tr>
			<td style="width:20px;"></td>
			<td>{iblock:%6$s}</td>
			<td style="width:150px;"><p>
				<a href="?mod=iblock&act=edit&file=%6$s">' . lang('edit') . '</a>
				<a href="?mod=iblock&act=delete&file=%6$s.iblock">' . lang('delete') . '</a>
			</p></td>
		</tr>';
		parent::__construct();
	}

	/**
	* Gets list of iblocks
	* @return array
	*/
	public function view(){
		$p['jquery'] = "
			$('#table_input').focus(function(){
				$(this).attr('placeholder','" . lang('insert_title') . "')
			})
			$('#table_input').keypress(function(e){
				if(e.which == 13){
					add($(this).val(), $(this).attr('data-mod'))
				}
			})";
		$rawData = globRecursive(IBLOCK_DIR, array("iblock"));
		$p['content'] = '<table class="table full zebra highlight"><thead><tr><th></th><th><input class="full" id="table_input" type="text" placeholder="' . lang('add_iblock') . '" data-mod="iblock"></th><th id="table_msg"></th></tr></thead><tbody>' . PHP_EOL;
		if(!$rawData){
			$p['content'] .= "<tr><td></td><td><span>" . lang('epmty') . "</span></td><td></td></tr>\n";
		} else{
			uksort($rawData, 'strnatcmp');
			foreach ($rawData as $data){
				$p['content'] .= vsprintf($this -> format, $data);
			}
		}
		$p['content'] .= "</tbody></table>\n";
		$p['breadcrumbs'] = lang('iblocks') . ' &gt; <span>' . lang('view') . '</span>';
		return $p;
	}

	/**
	* Gets iblock edit form
	* @return array
	*/
	public function edit(){
		if(preg_match('![^\w a-я-]!', $_GET['file'])){
			throw new Exception(lang('unauthorized_request'));
		}

		// for cyrillic names
		$cyr_name = iconv('utf-8','cp1251//TRANSLIT', $_GET['file']);

		$name = $_GET['file'];
		if (is_file(IBLOCK_DIR . $cyr_name . '.iblock')){
			$content = file_get_contents(IBLOCK_DIR . $cyr_name . '.iblock');
			$content = str_replace("<", "&lt;", $content);
			$files = globRecursive(IBLOCK_DIR, array('iblock'));
			$p['content'] = '
	<form action="?mod=iblock&act=save&file=' . $name. '" method="post">
		<table class="full form">
			<tr>
				<td>
					<div class="title">' . lang('content') . '</div>
					<div><textarea id="words" name="content" class="full" autofocus>' . $content . '</textarea></div>
				</td>
				<td>
					' . getSaveButton($files[$cyr_name . '.iblock']['fullpath']) . '
					<div class="title">' . lang('other_files') . ':</div><div>' . getSelect($files, 'cyr_name', $name, 'name="other" class="full"') . '</div>
					' . getCodeInput() . '
				</td>
			</tr>
		</table>
	</form>';
			$p['breadcrumbs'] = '<a href="?mod=iblock">' . lang('iblocks') . '</a> &gt; ' . lang('editor') . ' ' . $name;
		}
		else throw new Exception(lang('unauthorized_request'));
		$p['jquery'] = '$(\'select[name="other"]\').change(function(){location.href = \'?mod=iblock&act=edit&file=\' + $(this).val()})';
		return $p;
	}

	/**
	* save iblocks
	* @return array
	*/
	public function save(){
		if (isset($_GET['file'])){
			if (!canEdit()){
				throw new Exception(lang('unauthorized_request'));
			}
			$cyr_name = iconv('utf-8','cp1251//TRANSLIT', $_GET['file']);
			if (is_file(IBLOCK_DIR . $_GET['file'] . '.iblock')){
				$iblock = $_GET['file'];
			}
			elseif(is_file(IBLOCK_DIR . $cyr_name . '.iblock')){
				$iblock = $cyr_name;
			}
			if(file_put_contents(IBLOCK_DIR . $iblock . '.iblock', $_POST['content']) === false){
				throw new Exception(lang('not_saved'));
			}
			clearAllCache();
			$p['redirect'] = 1;
			return $p;
		}
		throw new Exception(lang('no_value'));
	}

	/**
	* Adds one iblock
	* @return array
	*/
	public function add(){
		if ($_POST['name']){
			$name = properUri(trim($_POST['name']));
			if ($name and !is_file(IBLOCK_DIR . $name . '.iblock')){
				if(!file_put_contents(IBLOCK_DIR . $name . '.iblock' , "<?php\n\n\n\n?>")){
					throw new Exception(lang('not_saved'));
				}
				$json['content'] = '?mod=iblock&act=edit&file='. $name;
				return $json;
			}
			else {
				throw new Exception(lang('already_exists'));
			}
		}
		throw new Exception(lang('no_value'));
	}

	/**
	* Delete iblock
	* @return array
	*/
	public function delete(){
		if (isset($_GET['file']) and strrchr($_GET['file'], '.iblock') !== false){
			$cyr_name = iconv('utf-8','cp1251//TRANSLIT', $_GET['file']);
			if (is_file(IBLOCK_DIR . $_GET['file'])){
				$iblock = $_GET['file'];
			}
			elseif(is_file(IBLOCK_DIR . $cyr_name)){
				$iblock = $cyr_name;
			}
			else throw new Exception(lang('unauthorized_request2'));
			
			if(!unlink(IBLOCK_DIR . $iblock)){
				throw new Exception(lang('not_deleted'));
			}
		}
		else throw new Exception(lang('unauthorized_request1'));
		$p['redirect'] = 1;
		return $p;
	}
 }
?>