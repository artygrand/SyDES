<form action="search" class="form-search">
	<div class="input-group input-group-lg">
		<input type="<?=$locale;?>/search" class="form-control" name="s" value="<?=$query;?>">
		<span class="input-group-btn">
			<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
		</span>
	</div>
</form>

<? if ($result){ ?>

	<p class="lead"><? printf(t('search_found'), pluralize(count($result), t('match'), t('matches'), t('matches2'))); ?></p>
	<? foreach($result as $item){
		$fullpath = $locale ? $locale . $item['path'] : substr($item['path'], 1);
	?>
		<p><a href="<?=$fullpath;?>"><?=$item['title'];?></a></p>
	<? } ?>

<? } else { ?>

	<p class="lead"><?=t('search_not_found');?></p>
	
<? } ?>