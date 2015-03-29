<?php
$format = '
<li class="s-%5$s" id="%1$d">
	<div class="pagerow" data-level="%3$s">
	<div class="cell checker"><input type="checkbox" class="ids" name="id[]" value="%1$d"></div>
	<div class="cell id">#%1$d</div>
	<div class="cell title l-%3$s"><span class="type %6$s"></span> <a href="..%4$s">%2$s</a></div>
	<div class="cell pull-right">
		<div class="cell pagestatus"><div class="status-wrap">%7$s</div></div>
		<div class="cell actions">
			<div class="btn-group btn-block with-dropdown">
				<a class="btn btn-default btn-sm btn-block" href="?mod=pages&type=' . $type . '&act=edit&id=%1$d">' . lang('edit') . '</a>
				<div class="btn-group">
					<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
					<ul class="dropdown-menu pull-right">
						<li><a href="?mod=pages&type=' . $type . '&act=edit&source=%1$d">' . lang('clone') . '</a></li>
						<li><a href="?mod=pages&type=' . $type . '&act=edit&parent=%1$d">' . lang('add_subpage') . '</a></li>
						<li class="divider"></li>
						<li><a class="danger" href="?mod=pages&type=' . $type . '&act=delete&id=%1$d">' . lang('delete') . '</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	</div>
</li>';
$menu = '<div class="pagesheader">
	<div class="cell checker"><input type="checkbox" id="checkall" title="'.lang('check_all').'"></div>
	<div class="cell id">ID</div>
	<div class="cell title">'.lang('name').'</div>
	<div class="cell pull-right">
		<div class="cell pagestatus">'.lang('status').'</div>
		<div class="cell actions">'.lang('actions').'</div>
	</div>
</div>
<ul class="pagestree sortable">' . PHP_EOL;

if (empty($pages)){
	echo $menu, '<li>', lang('empty'), '</li></ul>';
	return;
}
$current = current($pages);

$prev_id = $current['id'];
$prev_parent = $current['parent_id'];

foreach($pages as $page){
	if($prev_parent != $page['parent_id']){
		if ($prev_id == $page['parent_id']){
			$menu = substr($menu, 0, -5); 
			$menu .= '<ul class="sortable">' . PHP_EOL;
		} else {
			$delta = ($prev_level - substr_count($page['fullpath'], '/')) * 5;
			$menu .= str_pad('', $delta, '</ul>') . PHP_EOL . '</li>' . PHP_EOL; 
		}
	}
	if ($page['id'] != 0){
		$type = ($page['haschilds']) ? 'catopen' : 'page';
	} else {
		$type = '';
	}
	if (empty($page['title'])) $page['title'] = lang('no_translation');
	$menu .= sprintf($format, $page['id'], $page['title'], $page['level'], $page['fullpath'], $page['status'], $type, getSelect($statuses, $page['status'], 'data-id="' . $page['id'] . '" class="form-control status input-sm"'), $page['position']);
	$prev_id = $page['id'];
	$prev_parent = $page['parent_id'];
	$prev_level = substr_count($page['fullpath'], '/');
}

$delta = ($prev_level - 2) * 5;
echo $menu, str_pad('', $delta, '</ul>'), PHP_EOL, '</ul>', PHP_EOL;
?>