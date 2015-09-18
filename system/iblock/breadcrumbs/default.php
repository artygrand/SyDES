<?php
if (!$args['hide_current']){
	$result['items'][] = '<span>' . $page['title'] . '</span>';
}
echo '<div class="breadcrumbs">', implode(" {$args['separator']} ", $result['items']), '</div>';