<?php if (!$form['hide_name']){ ?><p class="lead"><?=$form['name'];?></p><?php } ?>
<form method="post" enctype="multipart/form-data" action="/constructors/form/send" <?=H::attr($form_attr);?>>
	<?php if (!empty($form['description'])){ ?><p><?=$form['description'];?></p><?php } ?>

	<?=implode(PHP_EOL, $fields);?>

	<?=$system_fields;?>
	<div class="form-group form-type-submit">
	<?php if ($form['template'] == 'horizontal'){ ?>
		<div class="col-sm-offset-<?=$args['label_cols'];?> col-sm-<?=$args['input_cols'];?>">
			<button type="submit" class="btn btn-primary"><?=$form['submit_button'];?></button>
		</div>
	<?php } else { ?>
		<button type="submit" class="btn btn-primary"><?=$form['submit_button'];?></button> 
	<?php } ?>
		
	</div>
</form>