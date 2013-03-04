<?php
/**
* SyDES v1.7 (SQLite) black box file
*
* copyright 2011-2012, ArtyGrand (artygrand.ru)
* license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

//find ip with proxy
function getip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
	elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	$ip = getenv("HTTP_X_FORWARDED_FOR");
	elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
	elseif ($_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER['REMOTE_ADDR'];
 	else
	$ip = "unknown";
  
	return $ip;
}

//russian dates
function rus_date() {
    $translate = array(
    'am' => 'дп', 'pm' => 'пп', 'AM' => 'ДП', 'PM' => 'ПП',
    'Monday' => 'Понедельник', 'Mon' => 'Пн', 'Tuesday' => 'Вторник', 'Tue' => 'Вт',
    'Wednesday' => 'Среда', 'Wed' => 'Ср', 'Thursday' => 'Четверг', 'Thu' => 'Чт',
    'Friday' => 'Пятница', 'Fri' => 'Пт', 'Saturday' => 'Суббота', 'Sat' => 'Сб',
    'Sunday' => 'Воскресенье', 'Sun' => 'Вс', 'January' => 'Января', 'Jan' => 'Янв',
    'February' => 'Февраля', 'Feb' => 'Фев', 'March' => 'Марта', 'Mar' => 'Мар',
    'April' => 'Апреля', 'Apr' => 'Апр', 'May' => 'Мая', 'May' => 'Мая',
    'June' => 'Июня', 'Jun' => 'Июн', 'July' => 'Июля', 'Jul' => 'Июл',
    'August' => 'Августа', 'Aug' => 'Авг', 'September' => 'Сентября', 'Sep' => 'Сен',
    'October' => 'Октября', 'Oct' => 'Окт', 'November' => 'Ноября', 'Nov' => 'Ноя',
    'December' => 'Декабря', 'Dec' => 'Дек', 'st' => 'ое', 'nd' => 'ое',
    'rd' => 'е', 'th' => 'ое'
    );
    
    if (func_num_args() > 1) {
        $timestamp = func_get_arg(1);
        return strtr(date(func_get_arg(0), $timestamp), $translate);
    } else {
        return strtr(date(func_get_arg(0)), $translate);
    }
}

//get base to links
function getbase($trim){
	$bhost = $_SERVER['HTTP_HOST'];
	$buri = rtrim(dirname($_SERVER['PHP_SELF']), $trim);
	return $bhost.$buri.'/';
}

/**
* Return flat array of folders or files with needed extensions
* or folders tree, if recursive mode is on
* @dir = string, relative path to the destination folder
* @mask = array('html', 'txt') - extensions
*		or array(true) - all files
*		or array('/') - folders
* @recursive = true or false
* @return array
*/
function globRecursive($dir, $mask, $recursive = false, $del = ''){
	$pages = array();
	foreach(glob($dir.'/*', GLOB_NOSORT) as $filename){
		if ($mask[0] === '/' and is_dir($filename)){
			$del = !$del ? $dir . '/' : $del;
			$alias = str_replace($del, '', $filename);
			$name = str_replace($dir . '/', '', $filename);
			$pages[$alias] = array('fullpath' => $filename, 'title' => $name);
		} elseif (in_array(pathinfo($filename, PATHINFO_EXTENSION), $mask)){
			$file = pathinfo($filename, PATHINFO_BASENAME);
			$parent = pathinfo($filename, PATHINFO_DIRNAME);
			$name = pathinfo($filename, PATHINFO_FILENAME);
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$cyr_name = iconv('cp1251','utf-8//TRANSLIT',$name);
			$pages[$file] = array('parent' => $parent, 'fullpath' => $filename, 'alias' => $file, 'title' => $name, 'ext' => $ext, 'cyr_name' => $cyr_name);
		}
		if($recursive === true and is_dir($filename)){
			if ($mask[0] === '/'){
				$pages[$alias]['childs'] = globRecursive($filename, $mask, true, $del);
				if (!$pages[$alias]['childs']){
					unset($pages[$alias]['childs']);
				}
			} else {
				$temp = globRecursive($filename, $mask, true);
				$pages = array_merge($pages, $temp);
			}
		}
	}
	return $pages;
}

function natorder($a,$b) { 
	return strnatcmp ($a['fullpath'], $b['fullpath']); 
}

function hook($mod, $act, $data){
	$functions=@Core::$hook[$mod -> name][$act];
	if(is_array($functions)){
		ksort($functions);
		foreach($functions as $function){
			if($function and function_exists($function)){
				$data = $function($mod, $data);
			}
		}
	}
	return $data;
}

/*ADMIN FUNCTIONS*/

/**
* Returns the correct ends for the russian numerators.
* 1 яблоко, 2 яблока, 5 яблок
* @var integer $num
* @var string $str1
* @var string $str2
* @var string $str3
* @return string
*/
function get_correct_str($num, $str1, $str2, $str3){
    $val = $num % 100;
    if ($val > 10 && $val < 20) return $num .' '. $str3;
    else {
        $val = $num % 10;
        if ($val == 1) return $num .' '. $str1;
        elseif ($val > 1 && $val < 5) return $num .' '. $str2;
        else return $num .' '. $str3;
    }
}

//just str_replace, but once
function str_replace_once($search, $replace, $text){ 
   $pos = strpos($text, $search); 
   return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text; 
}

function lang($text, $echo = false){ 
	global $l;
	if (isset($l[$text])){
		if ($echo) echo $l[$text];
		else return $l[$text];
	} else {
		if ($echo) echo $text;
		else return $text;
	}
}

/**
* Selects data from array and create <select>
* @param array $data
* @param string $what (which element to use. title, path or what?)
* @param string $current (selected option)
* @param string $props (properties: class="full" name="someshit" id="id")
* @return string
*/
function getSelect($data, $what, $current, $props = ''){
	$select = PHP_EOL . '<select ' . $props . '>' . PHP_EOL;
	foreach ($data as $value){
		$which = (is_array($value) and $what != '') ? $value[$what] : $value;
		$select .= '	<option value="' . $which . '"';
		$select .= $which == $current ? ' selected' : '';
		$select .= '>' . $which . "</option>\n";
	}
	return $select . "</select>\n";
}

/**
* Selects data from array and create inputs
* @param array $data
* @return string
*/
function getForm($data){
	$form = '';
	foreach($data as $name => $input){
		if($input['tag'] == 'textarea'){
			$form .= '<div class="title">' . $input['title'] . '</div><div><textarea name="' . $name . '" ' . $input['props'] . '>' . $input['val'] . '</textarea></div>' . PHP_EOL;
		} elseif ($input['tag'] == 'select'){
			$form .= '<div class="title">' . $input['title'] . '</div><div>' . getSelect($input['values'], '', $input['val'], 'name="' . $name . '" '.$input['props']) . '</div>';
		} else {
			$form .= '<div class="title">' . $input['title'] . '</div><div><input type="' . $input['tag'] . '" name="' . $name . '" value="' . $input['val'] . '" ' . $input['props'] . '></div>' . PHP_EOL;
		}
	}
	return $form . PHP_EOL;
}

function getSaveButton($file){
	return is_writable($file) ? '<button type="submit" class="full button">' . lang('save') . '</button>' : '<button type="button" class="full button">' . lang('not_writeable') . '</button>';
}

function getCodeInput(){
	if (!isset($_SESSION['master_code']) or $_SESSION['master_code'] != Core::$config['master_code']){
		return '<div class="title"><span class="help" title="' . lang('tip_developer_code') . '">' . lang('developer_code') . '</span>:</div><div><input type="text" name="code" class="full" required></div>';
	}
}

function canEdit(){
	if (!isset($_SESSION['master_code']) or $_SESSION['master_code'] != Core::$config['master_code']){
		if (md5($_POST['code']) == Core::$config['master_code']){
			$_SESSION['master_code'] = Core::$config['master_code'];
		} else return false;
	} 
	return true;
}

function issetTable($table){
	$stmt = Core::$db -> query("SELECT 1 FROM {$table} WHERE 1");
	if ($stmt) return true;
	else return false;
}

function createTable($table, $cols){
	$a = '';
	foreach($cols as $name => $col){
		$a .= ', ' . $name . ' ' . $col['type'];
	}
	Core::$db -> exec("CREATE TABLE {$table} (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT{$a})");
}

function getPageData($db, $locale, $where){
	if ((int)$where != 0){
		$stmt = $db -> prepare("SELECT pages.*, pages_content.title, pages_content.content FROM pages, pages_content WHERE pages.status = '1' AND pages.id = :where AND pages_content.locale = :locale AND pages_content.page_id = pages.id");
		$stmt->execute(array('where' => (int)$where, 'locale' => $locale));
	} else {
		$stmt = $db -> prepare("SELECT pages.*, pages_content.title, pages_content.content FROM pages, pages_content WHERE pages.status = '1' AND pages.fullpath = :where AND pages_content.locale = :locale AND pages_content.page_id = pages.id");
		$stmt->execute(array('where' => '/'.$where, 'locale' => $locale));
	}
	return $stmt -> fetchAll(PDO::FETCH_ASSOC);
}
function getMetaData($db, $id, $locale){
	$stmt = $db -> query("SELECT key, value FROM config_meta WHERE page_id = 1");
	$metas = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$stmt = $db -> prepare("SELECT key, value FROM pages_meta WHERE page_id = :id");
	$stmt->execute(array('id' => $id));
	$metas = array_merge($metas, $stmt -> fetchAll(PDO::FETCH_ASSOC));
	foreach($metas as $m){
		if ($m['key'][2] == '_' and substr($m['key'], 0, 2) == $locale){
			$meta[substr($m['key'], 3)] = $m['value'];
		} else {
			$meta[$m['key']] = $m['value'];
		}
	}
	return $meta;
}
?>