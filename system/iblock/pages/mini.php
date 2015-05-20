<?php foreach ($result as $item){ ?>
<div class="media">
	<div class="media-left">
		<a href="<?=$item['fullpath'];?>">
			<img class="media-object" src="/cache/img/50_50_c<?=$item['image'];?>" alt="<?=$item['title'];?>">
		</a>
	</div>
	<div class="media-body">
		<span class="date"><?=$item['cdate'];?></span><br>
		<a href="<?=$item['fullpath'];?>"><?=$item['title'];?></a>
	</div>
</div>
<?php } ?>