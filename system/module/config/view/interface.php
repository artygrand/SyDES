<div class="form-group row">
	<div class="col-sm-6">
		<label><?=t('menu_position');?></label>
		<?=H::radio('menu_pos', (int)$menu_pos, array(1 => t('left'), 0 => t('top')));?>
	</div>
	<div class="col-sm-6">
		<label><?=t('admin_language');?></label>
		<?=H::select('language', $language, $languages, $attr = 'class="form-control"');?>
	</div>
</div>

<div class="form-group row">
	<div class="col-sm-6">
		<label><?=t('open_menu_only_by_click');?></label>
		<?=H::yesNo('menu', $menu);?>
	</div>
	<div class="col-sm-6">
		<label><?=t('skin');?></label>
		<div class="skin-selector">
<? foreach ($skins as $skin){ ?>
		<a href="#" style="background:<?=$skin;?>" title="<?=$skin;?>"><?=$skin;?></a>
<? } ?>

		</div>
	</div>
</div>