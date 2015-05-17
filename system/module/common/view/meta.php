<div class="form-group meta-base">
	<label data-toggle="tooltip" title="<?=t('tip_meta');?>"><?=$meta_data;?></label>
	<?=$all_keys;?>
	<div class="input-group meta-value">
		<input type="text" placeholder="<?=t('value');?>" class="form-control input-sm">
		<span class="input-group-btn">
			<button class="btn btn-primary btn-sm" type="button" onclick="meta.add()" data-toggle="tooltip" data-placement="left" title="<?=t('add');?>"><span class="glyphicon glyphicon-arrow-down"></span></button>
		</span>
	</div>
</div>
<hr>