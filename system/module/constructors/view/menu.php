<h4><?=t('menu_constructor');?></h4>


<? if (isset($result['m1'])){
	echo H::treeList($result['m1'], function($item){
		return '
<div class="menu-item">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash item-remove" data-toggle="tooltip" title="' . t('remove') . '"></span>
	</div>
	<div class="item-title" data-toggle="collapse" href="#mi-' . $item['id'] . '">' . $item['title'] . '</div>
	<div class="item-content collapse" id="mi-' . $item['id'] . '">
		<div class="form-group"><label>URL</label><input type="text" class="form-control input-sm" value="' . $item['url'] . '"></div>
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label>' . t('title') . '</label><input type="text" class="form-control" value="' . $item['title'] . '"></div>
			<div class="col-sm-6"><label>' . t('attribute_title') . '</label><input type="text" class="form-control" value="' . $item['attr_title'] . '"></div>
		</div>
	</div>
</div>
';
	}, 'id="menu-holder"');
} else { ?>
	<ul id="menu-holder"></ul>
<? } ?>

<div class="well">
	<div class="row">
		<div class="col-sm-5"><input type="text" id="title" class="form-control input-sm" placeholder="<?=t('title');?>"></div>
		<div class="col-sm-5"><input type="text" id="url" class="form-control input-sm" placeholder="URL"></div>
		<div class="col-sm-2"><button type="button" class="btn btn-sm btn-default btn-block item-add"><?=t('add');?></button></div>
	</div>
</div>

<div id="item-source" class="menu-item" style="display:none;">
	<div class="widget-tools">
		<span class="glyphicon glyphicon-trash item-remove" data-toggle="tooltip" title="<?=t('remove');?>"></span>
	</div>
	<div class="item-title" data-toggle="collapse">title</div>
	<div class="item-content collapse">
		<div class="form-group"><label>URL</label><input type="text" class="form-control input-sm ins-url"></div>
		<div class="form-group form-group-sm row">
			<div class="col-sm-6"><label><?=t('title');?></label><input type="text" class="form-control ins-title"></div>
			<div class="col-sm-6"><label><?=t('attribute_title');?></label><input type="text" class="form-control"></div>
		</div>
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
			return;
		}
		var el = $('#item-source').clone(), id = Math.floor(Math.random() * 9001) + 1000
		el.find('.ins-url').val($('#url').val())
		el.find('.ins-title').val($('#title').val())
		el.find('.item-title').text($('#title').val()).attr('href', '#mi-'+id)
		el.show().find('.item-content').attr('id', 'mi-'+id)
		$('#menu-holder').append($('<li>').attr('id', 'item-'+id).append(el));
		$('#title').val('')
		$('#url').val('')
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
</script>

<style>
#menu-holder{padding:0;}
#menu-holder li{list-style:none;overflow:hidden;}
#menu-holder ul{padding-left:20px;}
.menu-item{margin:5px 0;position:relative;width:400px;}
.menu-item:hover .widget-tools{display:block;}
.item-title{padding:8px 15px;background:#f5f5f5;border:1px solid #ddd;}
.item-content{border:1px solid #ddd;border-top:none;overflow:hidden;}
.item-content .form-group{padding:0 15px 0;margin-top:10px;}
.item-remove{cursor:pointer;}
</style>