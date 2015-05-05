<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class H{
	public static function select($name, $value, array $source, $attr = ''){
		if (!$source){
			$source[] = t('empty');
		}
		if (array_values($source) === $source){
			$source = array_combine($source, $source);
		}

		$html = '<select name="' . $name . '"' . self::attr($attr) . '>' . PHP_EOL;
		foreach ($source as $val => $title){
			$slct = $val == $value ? ' selected' : '';
			$html .= '<option value="' . $val . '"' . $slct . '>' . $title . '</option>' . PHP_EOL;
		}
		return $html.'</select>';
	}

	public static function checkbox($name, $value, array $source, $attr = ''){
		return self::_optionElement('checkbox', $name, $source, $value, $attr);
	}

	public static function radio($name, $value, array $source, $attr = ''){
		return self::_optionElement('radio', $name, $source, $value, $attr);
	}

	public static function yesNo($name, $status){
		return self::_optionElement('radio', $name, array('1' => t('yes'),'0' => t('no')), (int)$status, array('inline' => true));
	}

	public static function string($name, $value, $attr = ''){
		return '<input type="text" value="' . $value . '" name="' . $name . '"' . self::attr($attr) . '>';
	}

	public static function hidden($name, $value, $attr = ''){
		return '<input type="hidden" value="' . $value . '" name="' . $name . '"' . self::attr($attr) . '>';
	}

	public static function textarea($name, $value, $attr = ''){
		return '<textarea name="' . $name . '"' . self::attr($attr) . '>' . $value . '</textarea>';
	}

	public static function button($label = 'Submit', $type = 'submit', $attr = ''){
		return '<button type="' . $type . '" ' . self::attr($attr) . '>' . $label . '</button>';
	}

	public static function password($name, $attr = ''){
		return '<input type="password" name="' . $name . '"' . self::attr($attr) . '>';
	}

	public static function link($title, $href, $attr = ''){
		return '<a href="' . $href . '"' . self::attr($attr) . '>' . $title . '</a>';
	}

	public static function saveButton($file = '', $button = ''){
		if (!$file or (is_writable($file) and is_writable(dirname($file)))){
			$btn = $button ? $button : '<button type="submit" class="btn btn-primary btn-block">' . t('save') . '</button>';
		} else {
			$btn = '<button type="button" class="btn btn-primary btn-block btn-save disabled">' . t('not_writeable') . '</button>';
		}
		return '<div class="form-group">' . $btn . '</div>';
	}

	public static function breadcrumb($crumbs){
		$html = '<ol class="breadcrumb"><li><a href=".">' . t('home') . '</a></li>';
		foreach ($crumbs as $crumb){
			if (isset($crumb['url'])){
				$html .= '<li><a href="' . $crumb['url'] . '">' . $crumb['title'] . '</a></li>';
			} else {
				$html .= '<li class="active">' . $crumb['title'] . '</li>';
			}
		}
		return $html . '</ol>';
	}

	public static function pagination($url, $total, $current, $limit = 10, $class = 'pagination', $links = 3){
		$pages = ceil($total / $limit);
		if ($pages < 2) return;

		$get = Registry::getInstance()->request->get;
		unset($get['skip']);
		if (count($get)){
			$url .= '?' . str_replace('%2F', '/', http_build_query($get)) . '&';
		} else {
			$url .= '?';
		}

		$thisPage = floor($current / $limit);

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
			$skip = ($i - 1) * $limit;
			if ($current == $skip){
				$html .= '<li class="active"><span>' . $i . '</span></li>';
			} else {
				$html .= '<li><a href="' . $url . 'skip=' . $skip . '">' . $i . '</a></li>';
			}
		}
		if ($pages > ($links * 2) + 1){
			$html = '<li><a href="' . $url . 'skip=0">&laquo;</a></li>' . $html . '<li><a href="' . $url . 'skip=' . ($pages - 1) * $limit . '">&raquo;</a></li>';
		}

		return '<ul class="' . $class . '">' . $html . '</ul>';
	}

	/**
	 * Gets tree from flat array
	 * @param array $data Flat array with elements.
	 *	Each element must have at least 'level'.
	 *	Each element can have 'attr' for LI and other data
	 * @param int $max_level Maximum tree depth
	 * @param string $attr Attributes for UL
	 * @param string $callback Function name, that can return a string with html
	 * @return boolean|string
	 */
	public static function treeList(array $data, closure $callback, $attr = '', $max_level = 20){
		reset($data);
		$cur = current($data);
		$prev_level = $cur['level'];
		$html = '<ul ' . $attr . '>';
		
		foreach ($data as $item){
			if (isset($item['skip'])){
				continue;
			}
			if ($prev_level != $item['level']){
				if ($max_level < $item['level']){
					continue;
				}
				if ($prev_level < $item['level']){
					$html = substr($html, 0, -5); 
					$html .= PHP_EOL . '<ul>';
				} else {
					$delta = ($prev_level - $item['level']) * 10;
					$html .= str_pad('', $delta, '</ul></li>'); 
				}
			}
			$attr = isset($item['attr']) ? ' ' . $item['attr'] : '';
			$html .= PHP_EOL . '<li' . $attr . '>' . $callback($item) . '</li>';
			$prev_level = $item['level'];
		}

		$delta = ($prev_level - 1) * 10;
		return $html . PHP_EOL . str_pad('', $delta, '</ul></li>') . PHP_EOL . '</ul>';
	}
	
	public static function listLinks($data, $current = false, $attr = ''){
		$html = '<ul' . self::attr($attr) . '>';
		foreach ($data as $link => $title){
			$active = $current == $link ? ' class="active"' : '';
			$html .= '<li' . $active . '><a href="'.$link.'">'.$title.'</a></li>';
		}
		return $html.'</ul>';
	}

	public static function table($rows, $header = '', $attr = ''){
		$html = '<table' . self::attr($attr) . '>';
		if ($header){
			$html .= '<thead><tr>';
			foreach ($header as $col){
				$html .= '<th>'.$col.'</th>';
			}
			$html .= '</tr></thead>';
		}
		$html .= '<tbody>';
		foreach ($rows as $row){
			$html .= '<tr>';
			foreach ($row as $col){
				$html .= '<td>'.$col.'</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}

	public static function tab($data, $current = false, $position = 'top', $attr = ''){
		$titles = $contents = '';
		foreach ($data as $key => $d){
			$active = $current == $key ? ' active' : '';
			$titles .= '<li class="' . $active . '"><a href="#' . $key . '" data-toggle="tab">' . $d['title'] . '</a></li>';
			$contents .= '<div class="tab-pane' . $active . '" id="' . $key . '">' . $d['content'] . '</div>';
		}
		if ($position == 'top'){
			return '<div '. $attr . '><ul class="nav nav-tabs">' . $titles . '</ul><div class="tab-content">' . $contents . '</div></div>';
		} elseif ($position == 'left'){
			return '<div class="row tab-container"><div class="col-xs-2"><ul class="nav nav-tabs-left">' . $titles . '</ul></div><div class="col-xs-10"><div class="tab-content">' . $contents . '</div></div></div>';
		} elseif ($position == 'right'){
			return '<div class="row tab-container"><div class="col-xs-10"><div class="tab-content">' . $contents . '</div></div><div class="col-xs-2"><ul class="nav nav-tabs-right">' . $titles . '</ul></div></div>';
		}
	}

	public static function accordion($data, $current = false){
		$id = rand(0, 10000);
		$html = '<div class="panel-group" id="accordion' . $id . '">';
		foreach ($data as $key => $d){
			$active = $current == $key ? ' in' : '';
			$html .= '
	<div class="panel panel-default">
		<div class="panel-heading" data-toggle="collapse" data-parent="#accordion' . $id . '" data-target="#' . $key . '">
			<span class="panel-title">' . $d['title'] . '</span>
		</div>
		<div id="' . $key . '" class="panel-collapse collapse' . $active . '">
			<div class="panel-body">' . $d['content'] . '</div>
		</div>
	</div>';
		}
		return $html . '</div>';
	}

	public static function modal($title, $body = '', $footer = '', $form_url = ''){
		$html = '
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">' . $title . '</h4>
		</div>
		<div class="modal-body">' . $body . '</div>
		<div class="modal-footer">' . $footer . '</div>
		';
		if ($form_url){
			$html = '<form name="modal-form" method="post" enctype="multipart/form-data" action="' . $form_url . '">' . $html . '</form>';
		}
		return $html;
	}

	public static function form($data){
		$form = '';
		foreach ($data as $name => $input){
			$piece = isset($input['label']) ? '<label>' . $input['label'] . '</label>' : '';

			$type = $input['type'];
			$attr = isset($input['attr']) ? $input['attr'] : '';
			$list = isset($input['list']) ? $input['list'] : array();

			switch ($input['type']){
				case 'select':
				case 'checkbox':
				case 'radio':
					$piece .= self::$type($name, $input['value'], $list, $attr);
					break;
				case 'password':
					$piece .= self::$type($name, $attr);
					break;
				default:
					$piece .= self::$type($name, $input['value'], $attr);
			}

			$form .= $input['type'] != 'hidden' ? '<div class="form-group">' . $piece . '</div>' : $piece;
		}
		return $form;
	}

	/* Private functions */
	private static function _optionElement($type, $name, $data, $selected, $attr = array()){
		if (!$data){
			return '<div>' . t('empty') . '</div>';
		}
		if (array_values($data) === $data){
			$data = array_combine($data, $data);
		}

		$inline = false;
		if (isset($attr['inline'])){
			$inline = $attr['inline'];
			unset($attr['inline']);
		}

		if ($inline){
			$attr['class'][] = $type;
			$pre = '<label class="' . $type . '-inline">';
			$post = '</label>';
		} else {
			$pre = '<div class="' . $type . '"><label>';
			$post = '</label></div>';
		}
		$name_post = ($type == 'checkbox' and count($data) > 1) ? '[]' : '';
		$html = '<div' . self::attr($attr) . '>';
		foreach ($data as $value => $title){
			$chkd = in_array($value, (array)$selected, true) ? ' checked' : '';
			$html .= $pre . '<input type="'. $type . '" name="' .$name . $name_post . '" value="' . $value . '"' . $chkd . '> ' . $title . $post . PHP_EOL;
		}
		return $html . '</div>';
	}

	private static function attr($attr){
		if (is_string($attr)){
			return ' ' . $attr;
		}
		$str = ' ';
		foreach ($attr as $key => $values){
			if (is_string($values)){
				$str .= $key . '="' . $values . '" ';
			} else {
				$str .= $key . '="' . implode(' ', $values) . '" ';
			}
		}
		return $str;
	}
}