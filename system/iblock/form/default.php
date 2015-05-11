<p class="lead"><?=$form['name'];?></p>
<form method="post" enctype="multipart/form-data" action="/constructors/form/send" <?=H::attr($form_attr);?>>
	<?php if (!empty($form['description'])){ ?><p><?=$form['description'];?></p><?php } ?>

	<?=implode(PHP_EOL, $fields);?>

	<?=H::hidden('form_id', $form['id']);?> 
	<?=H::hidden('token', 'token');?> 
	<?=H::button($form['submit_button'], 'submit', 'class="btn btn-primary"');?> 
</form>