<?php
/**
* Infoblock: Menu
* Shows a list of pages or random links
* Usage:
* {iblock:menu} = dynamic menu, shows a tree of all pages with status = 2
* {iblock:menu?show=branch} = dynamic menu, shows only current branch of a tree
* {iblock:menu?show=%type%} = dynamic menu, shows a tree of pages with specified type
* {iblock:menu?show=%id%} = %id% is id of previously created menu
* {iblock:menu?max_level=%num%} = %num% - limiting nesting menu
*/

$defaults = array(
	'show' => 'all',
	'max_level' => '2',
	'class' => '',
);
$args = array_merge($defaults, $args);
$result = array(
	'items' => array()
);

if (!isset($page['id'])){
	$page['position'] = 0;
	$page['id'] = 0;
}

if ($args['show'] == 'all'){
	foreach ($this->config_site['page_types'] as $k => $v){
		if ($v['structure'] == 'tree'){
			$tree_types[] = $k;
		}
	}
	$tree_types = "'" . implode("','", $tree_types) . "'";
	$result['items'] = $this->pages_model->getList(array("type IN ({$tree_types})", "status = 2"), 'position');
} elseif ($args['show'] == 'branch'){
	if (!$page['position']) return;

	if (strpos($page['position'], '#') !== false){
		$pos = explode('#', $page['position']);
	} else {
		$stmt = $this->db->query("SELECT position FROM pages WHERE id = {$page['parent_id']}");
		$pos = explode('#', $stmt->fetchColumn());
	}
	$result['items'] = $this->pages_model->getList(array("position LIKE '#{$pos[1]}#%'",  "status = 2"), 'position');
} elseif (is_numeric($args['show'])){
	$config = new Config('menu');
	$menu = $config->get($args['show'].':'.$this->locale);
	$result['items'] = $menu['items'];
} else {
	if ($args['show'] == 'main'){
		$args['show'] = 'page';
	}
	$result['items'] = $this->pages_model->getList(array("type = '{$args['show']}'", "status = 2"), 'position');
}

if (!$result['items']) return;

if (!is_numeric($args['show'])){
	foreach ($result['items'] as $i => $p){
		$result['items'][$i]['level'] = substr_count($p['position'],'#');
		$result['items'][$i]['attr_title'] = $result['items'][$i]['title'];
		if ($p['id'] == $page['id']){
			$result['items'][$i]['attr'] = 'class="active"';
		} elseif (strpos($page['position'], $p['position']) === 0){
			$result['items'][$i]['attr'] = 'class="opened"';
		}
	}
} elseif (isset($page['fullpath'])){
	foreach ($result['items'] as $i => $p){
		if ($p['fullpath'] == $page['fullpath']){
			$result['items'][$i]['attr'] = 'class="active"';
			break;
		}
	}
}