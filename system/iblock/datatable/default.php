<?php
echo H::table($result, array_keys($result[0]), array('class' => array('table')));

$last = ($skip+$args['limit']) > $count ? $count : ($skip+$args['limit']);
echo '<div>' . ($skip+1) . ' - ' . $last . ' items of ' . $count . '</div>';

if ($args['show_pagination']){
	echo H::pagination($page['fullpath'], $count, $skip, $args['limit']);
}
?>