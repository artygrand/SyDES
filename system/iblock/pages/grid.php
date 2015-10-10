<div class="row">
<?php
$columns = floor(12 / $args['columns']);
$i = 1;
foreach ($result as $item){
?>
<div class="col-sm-<?=$columns;?>">
	<div class="item">
		<div class="item-image">
			<a href="<?=$item['fullpath'];?>">
				<img src="<?=$item['image'];?>" alt="<?=$item['title'];?>">
			</a>
		</div>
		<div class="item-body">
			<a href="<?=$item['fullpath'];?>"><?=$item['title'];?></a>
		</div>
	</div>
</div>
<?php
	if ($i%$args['columns'] == 0){
		echo '<div class="clearfix"></div>';
	}
	$i++;
}
?>
</div>

<?php
if (!$args['no_skip']){
	echo H::pagination($page['fullpath'], $count, $skip, $args['limit']);
}
?>