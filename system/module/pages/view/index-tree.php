<script>var root = <?=$root;?></script>
<div class="pages-header">
	<div class="cell title"><?=t('title')?></div>
	<div class="cell status"><?=t('status')?></div>
	<div class="cell actions"><?=t('actions')?></div>
</div>

<? if ($pages){
	echo H::treeList($pages, function($item)use($type, $base){
		return '
<div class="selectitem pages-row s-' . $item['status'] . '">
	<div class="cell title"><span class="type"></span><a href="' . $base . $item['fullpath'] . '" target="_blank">' . $item['title'] . '</a></div>
	<div class="cell status"><div class="status-wrap">' . $item['status_select'] . '</div></div>
	<div class="cell actions">
		<input type="checkbox" class="ids" name="id[]" value="' . $item['id'] . '">
		<div class="btn-group btn-block btn-group-sm">
			<a class="col-xs-9 btn btn-default" href="?route=pages/edit&type=' . $type . '&id=' . $item['id'] . '">' . t('edit') . '</a>
			<button type="button" class="col-xs-3 btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
			<ol class="dropdown-menu dropdown-menu-right">
				<li><a href="?route=pages/edit&type=' . $type . '&source=' . $item['id'] . '">' . t('clone') . '</a></li>
				<li><a href="?route=pages/edit&type=' . $type . '&parent=' . $item['id'] . '">' . t('add_subpage') . '</a></li>
				<li class="divider"></li>
				<li><a class="danger" href="?route=pages/move&to=trash&type=' . $type . '&id=' . $item['id'] . '">' . t('delete') . '</a></li>
			</ol>
		</div>
	</div>
</div>';
	}, 'id="pages-tree" class="selectable idle"');
} ?>