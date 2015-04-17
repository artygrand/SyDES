<script src="/vendor/ckeditor/ckeditor.js"></script>
<input type="hidden" name="id" id="id" value="<?=$page['id'];?>">
<div class="form-group">
	<div class="input-group">
		<label class="input-group-btn"><select name="parent_id" id="parent" class="btn" data-toggle="tooltip" title="<?=t('parent');?>" data-type="<?=$type;?>"><?=$parents;?></select></label>
		<input type="text" class="form-control" name="alias" placeholder="<?=t('slug');?>" value="<?=$page['alias'];?>" <? if ($page['id'] == 1){ ?>readonly<? } ?>>
<? if (isset($page['fullpath'])){ ?>
		<span class="input-group-addon">
			<a href="<?=$base;?><?=$page['fullpath'];?>" target="_blank" data-toggle="tooltip" data-placement="left" title="<?=t('tip_view_page');?>">
				<span class="glyphicon glyphicon-new-window"></span>
			</a>
		</span>
<? } ?>
	 </div>
</div>

<div>
	<ul class="nav nav-tabs">
<? $i = ' class="active"';
foreach($locales as $loc){ ?>
		<li<?=$i?>><a href="#tab-<?=$loc;?>" data-toggle="tab"><?=$loc;?></a></li>
<? $i = '';
} ?>
		<li class="pull-right"><a href="#tab-meta" data-toggle="tab"><?=t('meta_data');?></a></li>
	</ul>

	<div class="tab-content">
<? $i = ' active';
foreach($locales as $loc){ ?>
		<div class="tab-pane<?=$i?>" id="tab-<?=$loc;?>">
			<div class="form-group">
				<label><?=t('page_title');?></label>
				<input type="text" name="title[<?=$loc;?>]" class="form-control" value="<?=isset($page['title'][$loc]) ? $page['title'][$loc] : '';?>">
			</div>
			<div class="form-group">
				<label><?=t('page_content');?></label>
				<textarea class="form-control ckeditor" rows="25" name="content[<?=$loc;?>]" id="editor_<?=$loc;?>"><?=isset($page['content'][$loc]) ? $page['content'][$loc] : '';?></textarea>
			</div>
		</div>
<? $i = '';
} ?>
		<div class="tab-pane" id="tab-meta"></div>
	</div>
</div>

<script>
CKFinder.setupCKEditor(null,'/vendor/ckfinder/');
</script>