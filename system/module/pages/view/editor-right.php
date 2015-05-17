<div class="form-group">
	<label><?=t('status');?></label>
	<?=$status;?>
</div>
<div class="form-group">
	<label><?=t('layout');?></label>
	<?=$layout;?>
</div>
<?php if ($show['position']){ ?>
<div class="form-group">
	<label><?=t('position');?></label>
	<input type="text" name="position" value="<?=$position;?>" class="form-control">
</div>
<?php } ?>
<?php if ($show['cdate']){ ?>
<div class="form-group">
	<label><?=t('creation_date');?></label>
	<input type="text" name="cdate" value="<?=$cdate;?>" class="form-control field-date">
</div>
<?php } ?>

<script>
$(document).ready(function(){
	meta.init({module:'pages', page_id:<?=$id;?>, permanent:[<?=$permanent_meta;?>]})
})
</script>