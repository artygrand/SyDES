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

$fields = json_decode($form['fields'], true);

if (in_array($form['template'], array('modal', 'modal_sm', 'modal_lg'))){
	$args['template'] = 'modal';
	$modal_size = '';
	if ($form['template'] == 'modal_sm'){
		$modal_size = 'modal-sm';
	} elseif($form['template'] == 'modal_lg'){
		$modal_size = 'modal-lg';
	}
}

$fields_html = '';
foreach ($fields as $field){
	$fields_html .= '';
}

$fields_html .= H::hidden('form_id', $form['id']);
$fields_html .= H::hidden('token', 'token');
$fields_html .= H::button($form['submit_button'], 'submit', 'class="btn btn-primary"');

$form_html = '<form id="dform-' . $form['id'] . '" method="post" enctype="multipart/form-data" action="/constructors/form/send">' . $fields_html . '</form>';