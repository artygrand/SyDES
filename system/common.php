<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

function redirect($link, $m = '', $s = 'error'){
	setcookie('messText', $m, time()+5);
	setcookie('messStatus', $s, time()+5);
	if(Admin::$mode == 'ajax'){
		die(json_encode(array('redirect' => $link)));
	} else {
		$host = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
		header("Location: http://$host$link");
		die;
	}
}
	
function getip(){
	if (!empty($_SERVER['HTTP_X_REAL_IP'])){
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function rus_date(){
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

	if (func_num_args() > 1){
		$timestamp = func_get_arg(1);
		return strtr(date(func_get_arg(0), $timestamp), $translate);
	} else {
		return strtr(date(func_get_arg(0)), $translate);
	}
}

function globRecursive($dir, $mask, $recursive = false, $del = ''){
	$pages = array();
	foreach(glob($dir.'/*') as $filename){
		if (is_array($mask)){
			if (in_array(pathinfo($filename, PATHINFO_EXTENSION), $mask)){
				static $file = 1;			
				$pages[$file]['dir'] = pathinfo($filename, PATHINFO_DIRNAME);
				$pages[$file]['title'] = pathinfo($filename, PATHINFO_BASENAME);
				$pages[$file]['cyr_name'] = iconv('cp1251','utf-8//TRANSLIT', pathinfo($filename, PATHINFO_FILENAME));
				$pages[$file]['ext'] = pathinfo($filename, PATHINFO_EXTENSION);
				$file++;
			}
		} elseif (is_dir($filename)){
			$del = !$del ? $dir . '/' : $del;
			$alias = str_replace($del, '', $filename);
			$pages[$alias] = array(
				'fullpath' => $filename,
				'title' => str_replace($dir . '/', '', $filename)
			);
		} 
		if($recursive == true and is_dir($filename)){
			if (!is_array($mask)){
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

function lang($text, $dl = array()){
	static $l = array();
	if ($dl) $l = array_merge($l, $dl);
	return isset($l[$text]) ? $l[$text] : $text;
}

function token($length){
    $chars = array(
        'A','B','C','D','E','F','G','H','J','K','L','M',
        'N','P','Q','R','S','T','U','V','W','X','Y','Z',
        'a','b','c','d','e','f','g','h','i','j','k','m',
        'n','o','p','q','r','s','t','u','v','w','x','y','z',
        '1','2','3','4','5','6','7','8','9');
    if ($length < 0 or $length > 58) return null;
    shuffle($chars);
    return implode('', array_slice($chars, 0, $length));
}

function render($template, $data = array()){
	if (file_exists($template)){
		extract($data);
		ob_start();
			require($template);
			$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}

function rus_ending($num, $str1, $str2, $str3){
    $val = $num % 100;
    if ($val > 10 && $val < 20) return "$num $str3";
    else {
        $val = $num % 10;
        if ($val == 1) return "$num $str1";
        elseif ($val > 1 && $val < 5) return "$num $str2";
        else return "$num $str3";
    }
}

function str_replace_once($search, $replace, $text){
   $pos = strpos($text, $search); 
   return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text; 
}

function getSelect($data, $selected, $props = ''){
	$select = "\n<select {$props}>\n";
	foreach ($data as $value => $title){
		$select .= "\t<option value=\"{$value}\"";
		$select .= in_array($value, (array)$selected) ? ' selected' : '';
		$select .= ">{$title}</option>\n";
	}
	return "{$select}</select>\n";
}

function getPaginator($url, $count, $current, $perPage = 10, $links = 3){
	$pages = ceil($count / $perPage);
	if ($pages < 2) return;
	
	$url .= strpos($url, '?') === false ? '?' : '&';
	$thisPage = floor($current / $perPage);

	if ($pages < ($links * 2) + 2){
		$from = 1;
		$to = $pages;
	} else {
		if ($thisPage < $links + 1){
			$from = 1;
			$to = ($links * 2) + 1;
		} elseif ($thisPage < $pages - $links - 1){
			$from = $thisPage - ($links - 1);
			$to = $thisPage + ($links + 1);
		} elseif ($thisPage > $pages - $links - 2){
			$from = $pages - ($links * 2);
			$to = $pages;
		}
	}
	$html = '';
	for ($i = $from; $i <= $to; $i++){
		$skip = ($i - 1) * $perPage;
		if ($current == $skip){
			$html .= '<span class="active">' . $i . '</span> ';
		} else {
			$html .= '<a href="' . $url . 'skip=' . $skip . '">' . $i . '</a> ';
		}
	}
	if ($pages > ($links * 2) + 1){
		$html = '<a href="' . $url . 'skip=0"><<</a> ' . $html . '<a href="' . $url . 'skip=' . ($pages - 1) * $perPage . '">>></a>';
	}

	return '<div class="paginator">' . $html . '</div>';
}

function getList($data, $current, $props = '', $which = 'ul'){
	$html = "\n<{$which} {$props}>\n";
	$format = '<li><a href="%1$s>%2$s</a></li>' . PHP_EOL;
	foreach ($data as $value => $title){
		$value .= $value == $current ? '" active' : '"';
		$html .= sprintf($format, $value, $title);
	}
	return $html . "</{$which}>\n";
}

function getPage($where){
	if (is_numeric($where)){
		$what = 'id';
		$data['data'] = (int)$where;
	} else {
		$what = 'fullpath';
		$data['data'] = "/$where";
	}
	$data['locale'] = Core::$siteConfig['locale'];
	$stmt = Core::$db -> prepare("SELECT pages.*, pc.title, pc.content FROM pages, pages_content as pc WHERE pages.status > 0 AND pages.{$what} = :data AND pc.locale = :locale AND pc.page_id = pages.id");
	$stmt->execute($data);
	$data = $stmt -> fetch(PDO::FETCH_ASSOC);
	
	if (!$data){
		return false;
	}

	$locale = count(Core::$siteConfig['locales']) > 1 ? Core::$siteConfig['locale'] . '/' : '';
	$data['fullpath'] = $locale . substr($data['fullpath'], 1);
	
	$stmt = Core::$db -> query("SELECT key, value FROM config_meta WHERE page_id = 1");
	$metas = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$stmt = Core::$db -> query("SELECT key, value FROM pages_meta WHERE page_id = {$data['id']}");
	$metas = array_merge($metas, $stmt -> fetchAll(PDO::FETCH_ASSOC));
	
	$meta = array();
	foreach($metas as $m){
		if (isset($m['key'][2]) and $m['key'][2] == '_' and substr($m['key'], 0, 2) == Core::$siteConfig['locale']){
			$meta['meta:' . substr($m['key'], 3)] = $m['value'];
		} else {
			$meta['meta:' . $m['key']] = $m['value'];
		}
	}

	return array_merge($meta, $data);
}

function getPages($filter, $myorder, $mylimit = 0, $skip = 0){
	$limit = $mylimit ? " LIMIT {$skip}, {$mylimit}" : '';
	$order = $myorder ? " ORDER BY {$myorder}" : '';
	$where = implode(' AND ', (array)$filter);
	
	$stmt =  Core::$db -> query("SELECT pages.id, pages.parent_id, pages.fullpath, pages_content.title, pages_content.content
	FROM pages, pages_content 
	WHERE {$where} AND pages.id = pages_content.page_id AND pages_content.locale = '" . Core::$siteConfig['locale'] . "' 
	{$order}{$limit}");
	$data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	if (!$data){
		return false;
	}
	
	$locale = count(Core::$siteConfig['locales']) > 1 ? Core::$siteConfig['locale'] . '/' : '';
	foreach($data as $d){
		$id[] = $d['id'];
		$d['fullpath'] = $locale . substr($d['fullpath'], 1);
		$pages[$d['id']] = $d;
	}
	$id = implode(',', $id);
	$stmt = Core::$db -> query("SELECT page_id, key, value FROM pages_meta WHERE page_id IN({$id})");
	$meta = $stmt -> fetchAll(PDO::FETCH_ASSOC);

	foreach($meta as $m){
		if (isset($m['key'][2]) and $m['key'][2] == '_' and substr($m['key'], 0, 2) == Core::$siteConfig['locale']){
			$pages[$m['page_id']]['meta:' . substr($m['key'], 3)] = $m['value'];
		} else {
			$pages[$m['page_id']]['meta:' . $m['key']] = $m['value'];
		}
	}
	return $pages;
}

function getPagesCount($filter){
	$where = implode(' AND ', (array)$filter);

	$stmt =  Core::$db -> query("SELECT count(pages.id)
	FROM pages, pages_content 
	WHERE {$where} AND pages.id = pages_content.page_id AND pages_content.locale = '" . Core::$siteConfig['locale'] . "'");
	$data = $stmt -> fetchColumn();
	return $data;
}

function getIblock($page, $iblock, $params=false){
	if ($params){
		$params = str_replace(array('?', '&amp;'), array('', '&'), $params);
		parse_str($params); // string like a "first=value&arr[]=foo+bar&arr[]=baz"
	}
	ob_start();
	include IBLOCK_DIR . $iblock . '.iblock';
	return ob_get_clean();
}

function addFiles($type, $files){
	$format = $type == 'css' ? '<link href="%1$s" rel="stylesheet" media="all">' : '<script src="%1$s"></script>';
	$html = '';
	foreach($files as $file){
		$html .= sprintf($format, $file) . PHP_EOL;
	}
	return $html;
}

function getBreadcrumbs($crumbs){
	$html = '<ol class="breadcrumb"><li><a href=".">' . lang('home') . '</a></li>';
	foreach ($crumbs as $crumb){
		if (isset($crumb['url'])){
			$html .= '<li><a href="' . $crumb['url'] . '">' . $crumb['title'] . '</a></li>';
		} else {
			$html .= '<li class="active">' . $crumb['title'] . '</li>';
		}
	}
	return $html . '</ol>';
}

function getCheckbox($name, $checked, $text){
	$checked = $checked ? ' checked' : '';
	return '<div class="checkbox"><label><input name="' . $name . '" type="checkbox" value="1"' . $checked . '>' . $text . '</label></div>';
}

function clearCache(){
	$cache = glob(CACHE_DIR . Admin::$site . '_*');
	if ($cache){
		foreach($cache as $file){
			unlink($file);
		}
	}
}

function issetTable($table){
	return (bool)Admin::$db -> query("SELECT 1 FROM {$table} WHERE 1");
}

function getForm($data){
	$form = '';
	foreach($data as $name => $input){
		if($input['tag'] == 'checkbox'){
			$form .= getCheckbox($name, $input['val'], $input['title']);
		} else {
			$form .= '<div class="form-group"><label for="' . $name . '">' . $input['title'] . '</label>';
			if($input['tag'] == 'textarea'){
				$form .= '<textarea name="' . $name . '" id="' . $name . '" ' . $input['props'] . '>' . $input['val'] . '</textarea>';
			} elseif ($input['tag'] == 'select'){
				$form .= getSelect(array_combine($input['values'], $input['values']), $input['val'], 'name="' . $name . '[]" ' . $input['props']);
			} else {
				$form .= '<input type="' . $input['tag'] . '" name="' . $name . '" id="' . $name . '" value="' . $input['val'] . '" ' . $input['props'] . '>';
			}
			$form .= '</div>';
		}
	}
	return $form . PHP_EOL;
}
?>