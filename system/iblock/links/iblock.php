<?php
/**
* Infoblock: Links
* Shows a list of wanted pages
* Usage:
* {iblock:links} = shows a child pages
* {iblock:links?show=%id%} = shows a pages with specified parent id
* {iblock:links?show=siblings} = shows a sibling pages
* {iblock:links?limit=%num%} = %num% - limiting quantity
* {iblock:links?class=%text%} = just classname for list
* {iblock:links?order=position} = for preserve sort order
*/

$defaults = array(
	'show' => $page['id'],
	'limit' => 10,
	'order' => 'id DESC',
	'class' => 'links',
);
$args = array_merge($defaults, $args);

if ($args['show'] == 'siblings'){
	$parent = $page['parent_id'];
} elseif (is_numeric($args['show'])){
	$parent = $args['show'];
} else {
	$parent = $page['id'];
}

if ($args['order'] == 'position'){
	$args['order'] .= '+0';
}

$result = $this->pages_model->getList(array("parent_id = {$parent}", "status > 0", "type != 'trash'", "position NOT LIKE '#%'"), $args['order'], $args['limit']);
if(!$result) return;
?>

<ul class="<?=$args['class'];?>">
<? foreach($result as $item){
	$active = '';
	if ($page['id'] == $item['id']){
		$active = ' class="active"';
		$item['fullpath'] = '#';
	}
?>
	<li<?=$active;?>><a href="<?=$item['fullpath'];?>"><?=$item['title'];?></a></li>
<? } ?>
</ul>