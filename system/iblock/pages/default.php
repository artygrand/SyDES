<? foreach($result as $item){ ?>
<div class="media">
	<div class="media-left">
		<a href="<?=$item['fullpath'];?>">
			<img class="media-object" src="<?=$item['image'];?>" alt="<?=$item['title'];?>">
		</a>
	</div>
	<div class="media-body">
		<span class="date"><?=$item['cdate'];?><span>
		<h4 class="media-heading"><?=$item['title'];?></h4>
		<?=$item['preview'];?>
		<a href="<?=$item['fullpath'];?>" class="btn btn-sm btn-primary">More...</a>
	</div>
</div>
<? } ?>

<?=H::pagination($page['fullpath'], $count, $skip, $args['limit']);?>