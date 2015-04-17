<?php
/**
* Infoblock: Pages preview
* Gets preview text, title and link from child pages
* Usage:
* {iblock:pages} - shows only from current category
* {iblock:pages?show=all} - from all child categories
* {iblock:pages?show=%id%} - from selected by id page
* {iblock:pages?limit=30} - items per page
*/

$defaults = array(
	'show' => 'category',
	'limit' => 20,
	'order' => 'status, id DESC',
	'date_format' => 'd.m.Y',
);
$args = array_merge($defaults, $args);

if ($args['show'] == 'all'){
	$stmt = $this->db->query("SELECT id FROM pages WHERE position LIKE '{$page['position']}%'");
	$ids = implode(',', $stmt->fetchAll(PDO::FETCH_COLUMN));
	$parents = "parent_id IN ({$ids})";
} elseif (is_numeric($args['show'])){
	$parents = "parent_id = {$args['show']}";
} else {
	$parents = "parent_id = {$page['id']}";
}

$filter = array(
	$parents,
	"position NOT LIKE '#%'",
	"status > 0",
	"type != 'trash'"
);
$count = $this->pages_model->getCount($filter);
if (!$count) return;

$skip = (isset($_GET['skip']) and (int)$_GET['skip'] > 0) ? (int)$_GET['skip'] : 0;
$data = $this->pages_model->getListWithMeta($filter, $args['order'], $args['limit'], $skip);
foreach($data as $k => $d){
	if (!isset($d['image'])){
		$d['image'] = '/upload/images/no-image.jpg';
	}
	$d['cdate'] = $this->locale == 'ru' ? rus_date($args['date_format'], $d['cdate']) : date($args['date_format'], $d['cdate']);
	$result[$k] = $d;
}