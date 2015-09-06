<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

function pre($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function t($text, $dl = array()){
	static $l = array();
	if (!empty($dl)){
		$l = array_merge($l, $dl);
	} else {
		return isset($l[$text]) ? $l[$text] : $text;
	}
}

function arr2file($array, $filename){
	$string = '<?php return ' . var_export($array, true) . ';';
	file_put_contents($filename, $string, LOCK_EX);
	chmod($filename, 0777);
}

function getip(){
	if (!empty($_SERVER['HTTP_X_REAL_IP'])){
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function tDate($locale){
	$function = $locale . '_date';
	$args = func_get_args();
	unset($args[0]);
	if (function_exists($function)){
		return call_user_func_array($function, $args);
	} else {
		return call_user_func_array('date', $args);
	}
}

function ru_date(){
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

function elog($string){
	$string = htmlspecialchars($string);
	$date = date('r');
	$ip = getip();
	file_put_contents(DIR_SITE . date('Ym') . '.log', "$date | $ip | $string" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function render($render, $data = array()){
	if (is_file($render)){
		extract($data);
		ob_start();
		include $render;
		return ob_get_clean();
	}
}

function write_ini_file($array, $path, $process_sections = false){
	$content = '';
	if ($process_sections){
		foreach ($array as $key => $elem){
			$content .= "[{$key}]\n";
			foreach ($elem as $key2 => $elem2){
				if (is_array($elem2)){
					foreach ($elem2 as $key3 => $elem3){
						$content .= "{$key2}[{$key3}] = {$elem3}\n";
					}
				} else {
					$content .= "{$key2} = {$elem2}\n";
				}
			}
		}
	} else {
		foreach ($array as $key => $elem){
			if (is_array($elem)){
				foreach ($elem2 as $key3 => $elem3){
						$content .= "{$key2}[{$key3}] = {$elem3}\n";
					}
			} else {
				$content .= "{$key} = {$elem}\n";
			}
		}
	}

	if (!$handle = fopen($path, 'w')){
		return false;
	}

	$success = fwrite($handle, $content);
	fclose($handle);
	return $success;
}

function checkServer(){
	$paths = array('cache', 'site', 'system/iblock', 'template/default', 'upload/images');
	$wr = '';
	foreach ($paths as $path){
		if (!is_writable(DIR_ROOT . $path)){
			$wr .= "<li>{$path}</li>";
		}
	}
	if ($wr){
		$wr .= '<li>' . t('folders_in_upload') . '</li>';
	}
	
	$req_pdo = class_exists('PDO', false);
	$pdo_drv = $req_pdo ? PDO::getAvailableDrivers(): array();
	$req_sqlite = in_array('sqlite', $pdo_drv);
	$req_json = function_exists('json_encode');
	$req_rewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : true;

	$errors = version_compare(PHP_VERSION, '5.3.0') < 0 ? '<li>' . t('php_too_old') . '</li>' : '';
	$errors .= !$req_pdo     ? '<li>' . t('pdo_not_supported') . '</li>' : '';
	$errors .= !$req_sqlite  ? '<li>' . t('pdo_sqlite_not_supported') . '</li>' : '';
	$errors .= !$req_json    ? '<li>' . t('json_not_supported') . '</li>' : '';
	$errors .= !$req_rewrite ? '<li>' . t('mod_rewrite_not_supported') . '</li>' : '';

	return array($errors, $wr);
}

function convertToAscii($str, $wSlash = false){
	$sl = $wSlash ? '/' : '';
	if (preg_match('![^\w-\.' . $sl . ']!', $str)){
		$utf8 = array(
			'à'=>'a', 'ô'=>'o', 'ď'=>'d', 'ḟ'=>'f', 'ë'=>'e', 'š'=>'s', 'ơ'=>'o',
			'ß'=>'ss', 'ă'=>'a', 'ř'=>'r', 'ț'=>'t', 'ň'=>'n', 'ā'=>'a', 'ķ'=>'k',
			'ŝ'=>'s', 'ỳ'=>'y', 'ņ'=>'n', 'ĺ'=>'l', 'ħ'=>'h', 'ṗ'=>'p', 'ó'=>'o',
			'ú'=>'u', 'ě'=>'e', 'é'=>'e', 'ç'=>'c', 'ẁ'=>'w', 'ċ'=>'c', 'õ'=>'o',
			'ṡ'=>'s', 'ø'=>'o', 'ģ'=>'g', 'ŧ'=>'t', 'ș'=>'s', 'ė'=>'e', 'ĉ'=>'c',
			'ś'=>'s', 'î'=>'i', 'ű'=>'u', 'ć'=>'c', 'ę'=>'e', 'ŵ'=>'w', 'ṫ'=>'t',
			'ū'=>'u', 'č'=>'c', 'ö'=>'oe', 'è'=>'e', 'ŷ'=>'y', 'ą'=>'a', 'ł'=>'l',
			'ų'=>'u', 'ů'=>'u', 'ş'=>'s', 'ğ'=>'g', 'ļ'=>'l', 'ƒ'=>'f', 'ž'=>'z',
			'ẃ'=>'w', 'ḃ'=>'b', 'å'=>'a', 'ì'=>'i', 'ï'=>'i', 'ḋ'=>'d', 'ť'=>'t',
			'ŗ'=>'r', 'ä'=>'ae','í'=>'i', 'ŕ'=>'r', 'ê'=>'e', 'ü'=>'ue', 'ò'=>'o',
			'ē'=>'e', 'ñ'=>'n', 'ń'=>'n', 'ĥ'=>'h', 'ĝ'=>'g', 'đ'=>'d', 'ĵ'=>'j',
			'ÿ'=>'y', 'ũ'=>'u', 'ŭ'=>'u', 'ư'=>'u', 'ţ'=>'t', 'ý'=>'y', 'ő'=>'o',
			'â'=>'a', 'ľ'=>'l', 'ẅ'=>'w', 'ż'=>'z', 'ī'=>'i', 'ã'=>'a', 'ġ'=>'g',
			'ṁ'=>'m', 'ō'=>'o', 'ĩ'=>'i', 'ù'=>'u', 'į'=>'i', 'ź'=>'z', 'á'=>'a',
			'û'=>'u', 'þ'=>'th','ð'=>'dh', 'æ'=>'ae', 'µ'=>'u', 'ĕ'=>'e',
			'À'=>'A', 'Ô'=>'O', 'Ď'=>'D', 'Ḟ'=>'F', 'Ë'=>'E', 'Š'=>'S', 'Ơ'=>'O',
			'Ă'=>'A', 'Ř'=>'R', 'Ț'=>'T', 'Ň'=>'N', 'Ā'=>'A', 'Ķ'=>'K',
			'Ŝ'=>'S', 'Ỳ'=>'Y', 'Ņ'=>'N', 'Ĺ'=>'L', 'Ħ'=>'H', 'Ṗ'=>'P', 'Ó'=>'O',
			'Ú'=>'U', 'Ě'=>'E', 'É'=>'E', 'Ç'=>'C', 'Ẁ'=>'W', 'Ċ'=>'C', 'Õ'=>'O',
			'Ṡ'=>'S', 'Ø'=>'O', 'Ģ'=>'G', 'Ŧ'=>'T', 'Ș'=>'S', 'Ė'=>'E', 'Ĉ'=>'C',
			'Ś'=>'S', 'Î'=>'I', 'Ű'=>'U', 'Ć'=>'C', 'Ę'=>'E', 'Ŵ'=>'W', 'Ṫ'=>'T',
			'Ū'=>'U', 'Č'=>'C', 'Ö'=>'Oe', 'È'=>'E', 'Ŷ'=>'Y', 'Ą'=>'A', 'Ł'=>'L',
			'Ų'=>'U', 'Ů'=>'U', 'Ş'=>'S', 'Ğ'=>'G', 'Ļ'=>'L', 'Ƒ'=>'F', 'Ž'=>'Z',
			'Ẃ'=>'W', 'Ḃ'=>'B', 'Å'=>'A', 'Ì'=>'I', 'Ï'=>'I', 'Ḋ'=>'D', 'Ť'=>'T',
			'Ŗ'=>'R', 'Ä'=>'Ae','Í'=>'I', 'Ŕ'=>'R', 'Ê'=>'E', 'Ü'=>'Ue', 'Ò'=>'O',
			'Ē'=>'E', 'Ñ'=>'N', 'Ń'=>'N', 'Ĥ'=>'H', 'Ĝ'=>'G', 'Đ'=>'D', 'Ĵ'=>'J',
			'Ÿ'=>'Y', 'Ũ'=>'U', 'Ŭ'=>'U', 'Ư'=>'U', 'Ţ'=>'T', 'Ý'=>'Y', 'Ő'=>'O',
			'Â'=>'A', 'Ľ'=>'L', 'Ẅ'=>'W', 'Ż'=>'Z', 'Ī'=>'I', 'Ã'=>'A', 'Ġ'=>'G',
			'Ṁ'=>'M', 'Ō'=>'O', 'Ĩ'=>'I', 'Ù'=>'U', 'Į'=>'I', 'Ź'=>'Z', 'Á'=>'A',
			'Û'=>'U', 'Þ'=>'Th', 'Ð'=>'Dh', 'Æ'=>'Ae', 'Ĕ'=>'E',
			'А'=>'a', 'Б'=>'b', 'В'=>'v', 'Г'=>'g', 'Д'=>'d', 'Е'=>'e', 'Ж'=>'j',
			'З'=>'z', 'И'=>'i', 'Й'=>'y', 'К'=>'k', 'Л'=>'l', 'М'=>'m', 'Н'=>'n',
			'О'=>'o', 'П'=>'p', 'Р'=>'r', 'С'=>'s', 'Т'=>'t', 'У'=>'u', 'Ф'=>'f',
			'Х'=>'h', 'Ц'=>'c', 'Ч'=>'ch','Ш'=>'sh', 'Щ'=>'sch', 'Ъ'=>'', 'Ы'=>'y',
			'Ь'=>'', 'Э'=>'e', 'Ю'=>'yu', 'Я'=>'ya', 'а'=>'a', 'б'=>'b', 'в'=>'v',
			'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'j', 'з'=>'z', 'и'=>'i', 'й'=>'y',
			'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
			'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'c', 'ч'=>'ch',
			'ш'=>'sh', 'щ'=>'sch', 'ъ'=>'', 'ы'=>'y', 'ь'=>'', 'э'=>'e', 'ю'=>'yu',
			'я'=>'ya', ' '=>'-', '*'=>'-', '+'=>'-'
		); 
		$str = str_replace(array_keys($utf8), array_values($utf8), $str);
		$str = preg_replace('![^\w-\.' . $sl . ']!', '', $str);
		$str = preg_replace('!-+!', '-', $str);
	}
	return strtolower($str);
}

function token($length){
	$chars = array(
		'A','B','C','D','E','F','G','H','J','K','L','M',
		'N','P','Q','R','S','T','U','V','W','X','Y','Z',
		'a','b','c','d','e','f','g','h','i','j','k','m',
		'n','o','p','q','r','s','t','u','v','w','x','y','z',
		'1','2','3','4','5','6','7','8','9');
	if ($length < 0 || $length > 58) return null;
	shuffle($chars);
	return implode('', array_slice($chars, 0, $length));
}

function pluralize($num, $str1, $str2, $str3){
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
function lower($string){
	return mb_strtolower($string, "UTF-8");
}

if (!function_exists('fnmatch')){
	function fnmatch($pattern, $string){
		return preg_match('#^' . strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.')) . '$#i', $string);
	}
}