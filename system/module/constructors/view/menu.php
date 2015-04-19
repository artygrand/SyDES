<div class="well well-sm">
	<a href="?route=constructors/menu" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span> <?=t('new_menu');?></a>
<? foreach($menus as $id => $menu){ ?>
	<a href="?route=constructors/menu&id=<?=$id;?>" class="btn btn-default"><?=$menu['title'];?></a>
<? } ?>
</div>

<input type="hidden" name="id" value="<?=$menu_id;?>">
<div class="form-group">
	<label><?=t('menu_name');?></label>
	<input type="text" name="title" value="<?=isset($menus[$menu_id]) ? $menus[$menu_id]['title'] : '';?>" class="form-control" style="width:400px;">
</div>

<label><?=t('menu_items');?></label>
<? if (isset($menus[$menu_id])){
	echo H::treeList($menus[$menu_id]['items'], function($item){
		return '
<div class="menu-item">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash item-remove" data-toggle="tooltip" data-placement="left" title="' . t('remove') . '"></span>
	</div>
	<div class="item-title" data-toggle="collapse" href="#mi-' . $item['id'] . '">' . $item['title'] . '</div>
	<div class="item-content collapse" id="mi-' . $item['id'] . '">
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label>' . t('title') . '</label><input type="text" name="item[title][]" class="form-control" value="' . $item['title'] . '"></div>
			<div class="col-sm-6"><label>' . t('attribute_title') . '</label><input type="text" name="item[attr_title][]" class="form-control" value="' . $item['attr_title'] . '"></div>
		</div>
		<div class="form-group"><label>URL</label><input type="text" name="item[fullpath][]" class="form-control input-sm" value="' . $item['fullpath'] . '"></div>
	</div>
</div>
';
	}, 'id="menu-holder"');
} else { ?>
	<ul id="menu-holder"></ul>
<? } ?>

<div class="menu-item new">
	<div class="item-title"><?=t('new_menu_item');?></div>
	<div class="item-content">
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label><?=t('title');?></label><input type="text" id="title" class="form-control"></div>
			<div class="col-sm-6"><label><?=t('attribute_title');?></label><input type="text" id="attr_title" class="form-control"></div>
		</div>
		<div class="form-group"><label>URL</label><input type="text" id="url" class="form-control input-sm"></div>
		<div class="form-group text-right"><button type="button" class="btn btn-sm btn-default item-add"><?=t('add');?></button></div>
	</div>
</div>

<a href="?route=constructors/menu/delete&id=<?=$menu_id;?>" class="btn btn-danger"><?=t('delete_menu');?></a>

<div id="item-source" class="menu-item" style="display:none;">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash item-remove" data-toggle="tooltip" data-placement="left" title="<?=t('remove');?>"></span>
	</div>
	<div class="item-title" data-toggle="collapse">title</div>
	<div class="item-content collapse">
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label><?=t('title');?></label><input type="text" name="item[title][]" class="form-control ins-title"></div>
			<div class="col-sm-6"><label><?=t('attribute_title');?></label><input type="text" name="item[attr_title][]" class="form-control ins-attr-title"></div>
		</div>
		<div class="form-group"><label>URL</label><input type="text" name="item[fullpath][]" class="form-control input-sm ins-url"></div>
	</div>
</div>

<script>
$(document).ready(function(){
	$('#title').autocomplete({
		source: '?route=pages/find',
		minLength: 2,
		select: function(event, ui){
			$('#url').val(ui.item.url);
		}
	})

	$('.item-add').click(function(){
		if ($('#title').val() == ''){
			return
		}
		var el = $('#item-source').clone().removeAttr('id'), id = Math.floor(Math.random() * 9001) + 1000;
		el.find('.ins-url').val($('#url').val());
		el.find('.ins-attr-title').val($('#attr_title').val());
		el.find('.ins-title').val($('#title').val());
		el.find('.item-title').text($('#title').val()).attr('href', '#mi-'+id);
		el.show().find('.item-content').attr('id', 'mi-'+id);
		$('#menu-holder').append($('<li>').attr('id', 'item-'+id).append(el));
		$('.new input').val('');
	})

	$('#menu-holder').nestedSortable({
		handle: 'div',
		items: 'li',
		toleranceElement: '> div',
		listType: 'ul',
		rootID: 0,
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true,
	});
})

$(document).on('click', '.item-remove', function(){
	$(this).parents('li').eq(0).remove()
})

$(document).on('submit', 'form', function(event){
	if ($('input[name="title"]').val() == ''){
		$('input[name="title"]').parent().addClass('has-error');
		return false;
	}
	if ($('.menu-item').length < 3){
		return false;
	}
	if (!$(this).find('input[name="item[level][]"]').length){
		event.preventDefault();
		$('.menu-item').not('#item-source, .new').each(function(){
			$('<input>').attr('type','hidden').attr('name','item[level][]').val($(this).parents('li').length).appendTo(this);
		})
		$(this).submit();
	}
});
</script>

<style>
#menu-holder{padding:0;}
#menu-holder li{list-style:none;overflow:hidden;}
#menu-holder ul{padding-left:20px;}
.menu-item{margin:5px 0;position:relative;width:400px;}
.menu-item:hover .widget-tools{display:block;}
.item-title{padding:8px 15px;background:#f5f5f5;border:1px solid #ddd;}
.item-content{border:1px solid #ddd;border-top:none;overflow:hidden;background:#fff;}
.item-content .form-group{padding:0 15px 0;margin-top:10px;}
.item-remove, .item-add{cursor:pointer;}
</style>