<?php if (!isset($args['hide_name'])){ ?><p class="lead"><?=$form['name'];?></p><?php } ?>
<form method="post" enctype="multipart/form-data" action="/constructors/form/send" <?=H::attr($form_attr);?>>
	<?php if (!empty($form['description'])){ ?><p><?=$form['description'];?></p><?php } ?>

	<?=implode(PHP_EOL, $fields);?>

	<div class="form-group form-type-submit"><?=H::button($form['submit_button'], 'submit', 'class="btn btn-primary"');?></div>
</form>