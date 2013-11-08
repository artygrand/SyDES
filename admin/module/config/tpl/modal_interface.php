<div class="row">
	<div class="col-xs-4">
		<label><?php echo lang('open_menu');?></label>
		<?php echo getCheckbox('menu', $menu, lang('only_by_ciick'));?>
	</div>
	<div class="col-xs-4">
		<label><?php echo lang('admin_language');?></label>
		<?php echo getSelect($langs, Admin::$lang, $props = 'name="lang" class="form-control"');?>
	</div>
	<div class="col-xs-4">
		<label><?php echo lang('skin');?></label>
		<div class="skin-selector">
<?php foreach ($skins as $skin){ ?>
			<a href="#" style="background:<?php echo $skin;?>" title="<?php echo $skin;?>"><?php echo $skin;?></a>
<?php } ?>
		</div>
	</div>
</div>