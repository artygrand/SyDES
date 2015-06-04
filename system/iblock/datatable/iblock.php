<?php

$defaults = array(
	'limit' => 20,
	'order' => 'id DESC',
	'show_pagination' => 1,
);
$args = array_merge($defaults, $args);

if (!isset($args['show'])){
	return;
}

$stmt = $this->db->query("SELECT count(*) FROM {$args['show']} WHERE status = 1");
$count = $stmt->fetchColumn();
if (!$count){
	return;
}

$skip = (isset($_GET['skip']) && (int)$_GET['skip'] > 0 && $args['show_pagination']) ? (int)$_GET['skip'] : 0;

$stmt = $this->db->query("SELECT * FROM {$args['show']} WHERE status = 1 ORDER BY {$args['order']} LIMIT {$skip}, {$args['limit']}");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

array_walk_recursive($result, function(&$value){
	$value = htmlspecialchars_decode($value);
});