<?php
/**
* Infoblock: Pages preview
* Gets any data from child pages with list structure
* Usage:
* {iblock:pages} - shows elements in current category or all in type
* show - specify which pages to show
*   show=news - only with page_type = news 
*   show=3 - pages with parent_id = 3
* limit - quantity per page if show_pagination=1 or just limit when show_pagination=0
* order - sort by any main column
* columns - quantity of columns in grid template, must be a multiple of 12
* filter - filter by any meta, format - filter="featured=1"
*/

$defaults = array(
	'show' => 'subs',
	'limit' => 20,
	'order' => 'status DESC, id DESC',
	'date_format' => 'd.m.Y',
	'columns' => 3,
	'filter' => '',
	'no_skip' => 0,
);
$args = array_merge($defaults, $args);

if ($args['show'] == 'subs'){
	$found_type = false;
	foreach($this->config_site['page_types'] as $type => $d){
		if ($d['root'] == $page['id']){
			$found_type = $type;
			break;
		}
	}
	if ($found_type){
		$condition = "type = '{$found_type}'";
	} else {
		$condition = "parent_id = {$page['id']}";
	}
} elseif (is_numeric($args['show'])){
	$condition = "parent_id = {$args['show']}";
} else {
	$condition = "type = '{$args['show']}'";
}

$filter = array(
	$condition,
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
$skip = (!$args['no_skip'] && isset($_GET['skip']) && (int)$_GET['skip'] > 0) ? (int)$_GET['skip'] : 0;
$data = $this->pages_model->getListWithMeta($filter, $args['order'], $args['limit'], $skip);
foreach ($data as $k => $d){
	if (!isset($d['image'])){
		$d['image'] = '/upload/images/no-image.jpg';
	}
	$d['cdate'] = tDate($this->locale, $args['date_format'], $d['cdate']);
	$result[$k] = $d;
}