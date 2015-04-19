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

<?=H::tab($tabs, 'tab-' . $locale, 'top', 'class="page-tabs"');?>


<script>
CKFinder.setupCKEditor(null,'/vendor/ckfinder/');
</script>