<div id="toolbar">
	<a href="<?=ADMIN;?>/"><?=t('admin');?></a>
	<div class="btn-group">
		<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown"><?=t('add');?> <span class="caret"></span></button>
		<ul class="dropdown-menu dropdown-menu">
<?php foreach ($types as $type => $name){ ?>
			<li><a href="<?=ADMIN;?>/?route=pages/edit&type=<?=$type;?>"><?=$name;?></a></li>
<?php } ?>
		</ul>
	</div>
	<a href="<?=ADMIN;?>/?route=common/clearcache&return=<?=$request_uri;?>"><?=t('clear_cache');?></a>

	<span class="divider"></span>

<?php foreach ($menu as $item){ ?>
	<?php if ($item['link']){ ?>
		<a href="<?=$item['link'];?>"><?=$item['title'];?></a>
	<?php } else { ?>
		<div class="btn-group">
			<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown"><?=$item['title']?> <span class="caret"></span></button>
			<ul class="dropdown-menu dropdown-menu">
			<?php foreach ($item['children'] as $child){ ?>
				<li><?=$child?></li>
			<?php } ?>
			</ul>
		</div>
	<?php } ?>
<?php } ?>

	<a href="<?=ADMIN;?>/?route=user/logout" class="pull-right"><?=t('exit');?></a>
</div>
<script>
$(document).ready(function(){
	$('.block-edit').on('click', function(e){
		if ($(this).data('module') == 'iblock'){
			location.href = '/<?=ADMIN;?>/?route=iblocks/edit&iblock=' + $(this).data('item')
		} else if($(this).data('module') == 'config'){
			location.href = '/<?=ADMIN;?>/?route=config#' + $(this).data('item')
		}
	})
	$('.block-template').on('click', function(e){
		location.href = '/<?=ADMIN;?>/?route=templates/file/edit&tpl=<?=$template;?>&file=iblock/' + $(this).data('item') + '/' + $(this).data('template') + '.php'
	})
	$('body').addClass('with-toolbar');
})
</script>