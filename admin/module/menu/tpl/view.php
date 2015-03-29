<div class="row">
	<div class="col-lg-2 col-sm-1"></div>
	<div class="col-lg-3 col-sm-4">
		<h3><?=lang('created_menu');?></h3>
		<ul class="list-group">
<?php if ($menus){ ?>
	<?php foreach($menus as $m){ ?>
			<li class="list-group-item"><span class="badge"><a href="?mod=menu&act=delete&id=<?=$m['id'];?>"><span class="glyphicon glyphicon-remove" title="<?=lang('delete');?>"></span></a></span> <a href="?mod=menu&id=<?=$m['id'];?>"><?=$m['title'];?></a></li>
	<?php } ?>
<?php } ?>
		<li class="list-group-item"><a href="?mod=menu"><?=lang('create_new_menu');?></a></li>
		</ul>
	</div>
	<div class="col-lg-5 col-sm-6">
		<h3><?=lang('menu_editor');?></h3>
		<form class="form-horizontal" method="post" action="?mod=menu&act=save&id=<?=$menu['id'];?>">
			<div class="form-group">
				<label class="col-sm-3 control-label"><?=lang('menu_title');?></label>
				<div class="col-sm-9"><input type="text" name="menu_title" value="<?=$menu['title'];?>" class="form-control"></div>
			</div>
		
			<ul class="list-unstyled menu-editor sortable">
<?php foreach($menu['context'] as $m){ ?>
				<li class="form-group row">
					<div class="col-md-2"><input type="text" name="level[]" value="<?=$m['level'];?>" class="form-control input-sm" placeholder="<?=lang('level');?>"></div>
					<div class="col-md-4"><input type="text" name="title[]" value="<?=$m['title'];?>" class="form-control input-sm" placeholder="<?=lang('link_title');?>"></div>
					<div class="col-md-4"><input type="text" name="fullpath[]" value="<?=$m['fullpath'];?>" class="form-control input-sm" placeholder="<?=lang('link');?>"></div>
					<div class="col-md-2 text-right control-label">
						<span class="glyphicon glyphicon-resize-vertical"></span> &nbsp; <span class="glyphicon glyphicon-remove menu-delrow" title="<?=lang('delete');?>"></span>
					</div>
				</li>
<?php } ?>
			</ul>
			<a class="btn btn-default btn-block btn-sm menu-addrow"><?=lang('add_more');?></a><br>
			<button type="submit" class="btn btn-default"><?=lang('save');?></button>
		</form>
<?php if (isset($_GET['id'])){ ?>
	<br><br>
	<pre>{iblock:menu?show=<?=(int)$_GET['id'];?>}</pre>
<?php } ?>
	</div>
	<div class="col-lg-2 col-sm-1"></div>
</div>
<script>
$(document).ready(function(){
	$('.sortable').sortable();
})
$(document).on('click','.menu-delrow',function(){$(this).parents('li').remove()})
$(document).on('click','.menu-addrow',function(){var ul = $('.menu-editor'); ul.children(':last').clone().appendTo(ul);ul.children(':last').find('input').val('')})
</script>