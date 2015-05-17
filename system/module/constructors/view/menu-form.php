<input type="hidden" name="id" value="<?=$menu_id;?>">

<div class="form-group">
	<label><?=t('menu_name');?></label>
	<input type="text" name="title" value="<?=isset($menus[$menu_id]) ? $menus[$menu_id]['title'] : '';?>" class="form-control" style="width:400px;" required>
</div>

<label><?=t('menu_items');?></label>
<?php if (isset($menus[$menu_id])){
	echo H::treeList($menus[$menu_id]['items'], function($item){
		return '
<div class="widget panel panel-default menu-item">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash remove-item" data-toggle="tooltip" title="' . t('remove') . '"></span>
	</div>
	<div class="panel-heading item-title" data-toggle="collapse" href="#mi-' . $item['id'] . '">' . $item['title'] . '</div>
	<div class="panel-collapse collapse" id="mi-' . $item['id'] . '">
		<div class="panel-body">
			<div class="form-group form-group-sm row">
				<div class="col-sm-6"><label>' . t('title') . '</label><input type="text" name="item[title][]" class="form-control" value="' . $item['title'] . '"></div>
				<div class="col-sm-6"><label>' . t('attribute_title') . '</label><input type="text" name="item[attr_title][]" class="form-control" value="' . $item['attr_title'] . '"></div>
			</div>
			<div class="form-group"><label>URL</label><input type="text" name="item[fullpath][]" class="form-control input-sm" value="' . $item['fullpath'] . '"></div>
		</div>
	</div>
</div>
';
	}, 'id="menu-holder"');
} else { ?>
	<ul id="menu-holder"></ul>
<?php } ?>

<div class="panel panel-default menu-item new">
	<div class="panel-heading item-title"><?=t('new_menu_item');?></div>
	<div class="panel-body">
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label><?=t('title');?></label><input type="text" id="title" class="form-control"></div>
			<div class="col-sm-6"><label><?=t('attribute_title');?></label><input type="text" id="attr_title" class="form-control"></div>
		</div>
		<div class="form-group"><label>URL</label><input type="text" id="url" class="form-control input-sm"></div>
		<div class="form-group text-right"><button type="button" class="btn btn-sm btn-default item-add"><?=t('add');?></button></div>
	</div>
</div>

<div id="item-source" class="widget panel panel-default menu-item">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash remove-item" data-toggle="tooltip" title="<?=t('remove');?>"></span>
	</div>
	<div class="panel-heading item-title" data-toggle="collapse">title</div>
	<div class="panel-collapse collapse">
		<div class="panel-body">
			<div class="form-group form-group-sm row">
				<div class="col-sm-6"><label><?=t('title');?></label><input type="text" name="item[title][]" class="form-control ins-title"></div>
				<div class="col-sm-6"><label><?=t('attribute_title');?></label><input type="text" name="item[attr_title][]" class="form-control ins-attr-title"></div>
			</div>
			<div class="form-group"><label>URL</label><input type="text" name="item[fullpath][]" class="form-control input-sm ins-url"></div>
		</div>
	</div>
</div>


<style>
#menu-holder{padding:0;}
#menu-holder li{list-style:none;}
#menu-holder ul{padding-left:20px;}
#menu-holder .panel{margin-bottom:0;margin-top:5px;}
#menu-holder .panel-heading{border-bottom:0;}
.menu-item{width:400px;}
#item-source{display:none;}
</style>