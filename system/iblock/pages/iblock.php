<?php
/**
* Infoblock: Pages preview
* Gets any data from child pages with list structure
* Usage:
* {iblock:pages} - shows only from current category
* show - specify which pages to show
*   show=all - show pages from all branch
*   show=category - only current category 
*   show=3 - pages with parent_id = 3
* limit - quantity per page if show_pagination=1 or just limit when show_pagination=0
* order - sort by any main column
* columns - quantity of columns in grid template, must be a multiple of 12
* filter - filter by any meta, format - filter="featured=1"
*/

$defaults = array(
	'show' => 'category',
	'limit' => 20,
	'order' => 'status DESC, id DESC',
	'date_format' => 'd.m.Y',
	'columns' => 3,
	'filter' => '',
	'show_pagination' => 1,
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

if ($args['filter']){
	$part = explode('=', $args['filter']);
	$filter[] = "id IN (SELECT page_id FROM pages_meta WHERE key = '{$part[0]}' AND value = '{$part[1]}')";
}

$count = $this->pages_model->getCount($filter);
if (!$count) return;

$args['order'] = str_replace('position', 'position+0', $args['order']);
$skip = (isset($_GET['skip']) and (int)$_GET['skip'] > 0 and $args['show_pagination']) ? (int)$_GET['skip'] : 0;
$data = $this->pages_model->getListWithMeta($filter, $args['order'], $args['limit'], $skip);
foreach ($data as $k => $d){
	if (!isset($d['image'])){
		$d['image'] = '/upload/images/no-image.jpg';
	}
	$d['cdate'] = tDate($this->locale, $args['date_format'], $d['cdate']);
	$result[$k] = $d;
}