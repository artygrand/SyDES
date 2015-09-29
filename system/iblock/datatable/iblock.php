<?php

$defaults = array(
	'limit' => 20,
	'order' => 'id DESC',
	'filter' => '',
	'show_pagination' => 1,
);
$args = array_merge($defaults, $args);

if (!isset($args['show'])){
	return;
}

$filter = array(
	'status = 1'
);
if ($args['filter']){
	$part = explode('=', $args['filter']);
	$filter[] = "{$part[0]} = '{$part[1]}'";
}
$filter = implode(' AND ', $filter);

$stmt = $this->db->query("SELECT count(*) FROM {$args['show']} WHERE {$filter}");
$count = $stmt->fetchColumn();
if (!$count){
	return;
}

$skip = (isset($_GET['skip']) && (int)$_GET['skip'] > 0 && $args['show_pagination']) ? (int)$_GET['skip'] : 0;

$stmt = $this->db->query("SELECT * FROM {$args['show']} WHERE {$filter} ORDER BY {$args['order']} LIMIT {$skip}, {$args['limit']}");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

array_walk_recursive($result, function(&$value){
	$value = htmlspecialchars_decode($value);
});