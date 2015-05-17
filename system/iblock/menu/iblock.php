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
	$pages = $this->pages_model->getList(array("type IN ({$tree_types})", "status = 2"), 'position');
} elseif ($args['show'] == 'branch'){
	if (!$page['position']) return;

	if (strpos($page['position'], '#') !== false){
		$pos = explode('#', $page['position']);
	} else {
		$stmt = $this->db->query("SELECT position FROM pages WHERE id = {$page['parent_id']}");
		$pos = explode('#', $stmt->fetchColumn());
	}
	$pages = $this->pages_model->getList(array("position LIKE '#{$pos[1]}#%'",  "status = 2"), 'position');
} elseif (is_numeric($args['show'])){
	$config = new Config('menu');
	$menu = $config->get($args['show']);
	$pages = $menu['items'];
} else {
	if ($args['show'] == 'main'){
		$args['show'] = 'page';
	}
	$pages = $this->pages_model->getList(array("type = '{$args['show']}'", "status = 2"), 'position');
}

if (!$pages) return;

if (!is_numeric($args['show'])){
	foreach ($pages as $i => $p){
		$pages[$i]['level'] = substr_count($p['position'],'#');
		$pages[$i]['attr_title'] = $pages[$i]['title'];
		if ($p['id'] == $page['id']){
			$pages[$i]['attr'] = 'class="active"';
		} elseif (strpos($page['position'], $p['position']) === 0){
			$pages[$i]['attr'] = 'class="opened"';
		}
	}
} else {
	foreach ($pages as $i => $p){
		if ($p['fullpath'] == $page['fullpath']){
			$pages[$i]['attr'] = 'class="active"';
			break;
		}
	}
}

$class = $args['class'] ? ' class="' . $args['class'] .'"' : '';

echo H::treeList($pages, function($item){return '<a href="' . $item['fullpath'] . '" title="' . $item['attr_title'] . '">' . $item['title'] . '</a>';}, 'id="menu-' . $args['show'] . '"' . $class, $args['max_level']);