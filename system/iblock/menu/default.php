<?php
$class = $args['class'] ? ' class="' . $args['class'] .'"' : '';

echo H::treeList(
	$result['items'],
	function($item){return '<a href="' . $item['fullpath'] . '" title="' . $item['attr_title'] . '">' . $item['title'] . '</a>';},
	'id="menu-' . $args['show'] . '"' . $class,
	$args['max_level']
);