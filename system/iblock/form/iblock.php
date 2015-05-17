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
$this->response->script[] = '/system/iblock/form/assets/form.js';
$this->response->addJsL10n($this->load->language('module_form', false));

$form_attr = array_merge(H::parseAttr(htmlspecialchars_decode($form['form_attr'])), array('id' => 'dform-' . $form['id'], 'class' => array('dform')));

if (!isset($_SESSION['form_token_key'])){
	$_SESSION['form_token_key'] = token(rand(5, 10));
	$_SESSION['form_token_value'] = token(rand(5, 10));
}

$form['fields'] = json_decode($form['fields'], true);
foreach ($form['fields'] as $field){
	$label = ($field['hide_label'] || empty($field['label'])) ? '' : '<label>' . $field['label'] . '</label>';
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
			$attr['class'][] = 'form-control2';
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
				$attr['class'][] = 'datepicker';
				$this->response->addJsSettings(array(
					'datepicker_format' => empty($field['placeholder']) ? 'dd.mm.yyyy' : $field['placeholder'],
				));
				$this->response->script[] = '/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js';
				$this->response->script[] = '/vendor/bootstrap-datepicker/locales/bootstrap-datepicker.' . $this->locale . '.min.js';
				$this->response->style[] = '/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css';
			}
			$input = H::string(
				$field['key'],
				$field['defaults'],
				$attr
			);
	}

	$fieldh = $label . $input . $description;
	if ($field['type'] != 'hidden'){
		$fieldh = '<div class="form-group form-type-' . $field['type'] . ' form-name-' . $field['key'] . '">' . $fieldh . '</div>';
	}
	$fields[] = $fieldh;
}
$fields[] = H::hidden('form_id', $form['id']);
$fields[] = H::hidden($_SESSION['form_token_key'], $_SESSION['form_token_value']);
