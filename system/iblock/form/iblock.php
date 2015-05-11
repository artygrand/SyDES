<?php
/**
* Infoblock: Forms
* Usage:
* {iblock:form?show=%id%} = id of predefined form
*/

if (!isset($args['show'])){
	return;
}

$stmt = $this->db->prepare("SELECT * FROM forms WHERE id = :id AND status = 1");
$stmt->execute(array('id' => $args['show']));
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form){
	return;
}

if (empty($form['submit_button'])){
	$form['submit_button'] = t('submit');
}

if (in_array($form['template'], array('modal', 'modal_sm', 'modal_lg'))){
	$args['template'] = 'modal';
	$modal_size = '';
	if ($form['template'] == 'modal_sm'){
		$modal_size = 'sm';
	} elseif ($form['template'] == 'modal_lg'){
		$modal_size = 'lg';
	}
}

$form_attr = array_merge(H::parseAttr(htmlspecialchars_decode($form['form_attr'])), array('id' => 'dform-' . $form['id']));

$form['fields'] = json_decode($form['fields'], true);
foreach ($form['fields'] as $field){
	$label = !empty($field['label']) ? '<label>' . $field['label'] . '</label>' : '';
	$description = !empty($field['description']) ? '<p class="help-block">' . $field['description'] . '</p>' : '';

	$attr = array();
	if ($field['required']){
		$attr['required'] = true;
	}
	if (!empty($field['placeholder'])){
		$attr['placeholder'] = $field['placeholder'];
	}
	$attr = array_merge($attr, H::parseAttr(htmlspecialchars_decode($field['attr'])));

	switch ($field['type']){
		case 'listing':
			$attr['class'][] = $field['list_type'] == 'select' ? 'form-control' : '';
			$source = explode("\r\n", $field['source']);
			if (strpos($source[0], '|') !== false){
				$option = array();
				foreach ($source as $row){
					$row = explode('|', $row);
					$option[$row[0]] = $row[1];
				}
				$source = $option;
			}
			$input = H::$field['list_type'](
				$field['key'],
				$field['defaults'],
				$source,
				$attr
			);
			break;
		case 'textarea':
			$attr['class'][] = 'form-control';
			$attr['rows'] = empty($field['rows']) ? 3 : $field['rows'];
			$input = H::textarea(
				$field['key'],
				$field['defaults'],
				$attr
			);
			break;
		case 'number':
			$attr['min'] = $field['min'];
			$attr['step'] = $field['step'];
			$attr['max'] = $field['max'];
			$attr['class'][] = 'form-control';
			$input = H::input(
				$field['key'],
				$field['defaults'],
				'number',
				$attr
			);
			break;
		case 'email':
		case 'tel':
			$attr['class'][] = 'form-control';
			$input = H::input(
				$field['key'],
				$field['defaults'],
				$field['type'],
				$attr
			);
			break;
		case 'file':
			$input = '<input type="file" name="' . $field['key'] . '" ' . H::attr($attr) . '>';
			break;
		case 'hidden':
			$input = H::hidden(
				$field['key'],
				$field['defaults'],
				$attr
			);
			break;
		default:
			$attr['class'][] = 'form-control';
			if ($field['type'] == 'date'){
				$attr['class'][] = 'field-date';
			}
			$input = H::string(
				$field['key'],
				$field['defaults'],
				$attr
			);
	}

	$fieldh = $label . $input . $description;
	if ($field['type'] != 'hidden'){
		$fieldh = '<div class="form-group">' . $fieldh . '</div>';
	}
	$fields[] = $fieldh;
}
