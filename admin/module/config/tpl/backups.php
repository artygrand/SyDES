<div class="panel-group" id="accordion">
<?php 
$i = 0;
foreach($sites as $site => $data){?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#site-<?php echo $site;?>"><?php echo $data['name'];?></a>
			</h4>
		</div>
		<div id="site-<?php echo $site;?>" class="panel-collapse collapse <?php if (++$i == 1){echo 'in';}?>">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-6"><?php echo $data['archives'];?></div>
					<div class="col-md-6"><a href="?mod=config&act=backup_create&site=<?php echo $site;?>" class="btn btn-primary pull-right"><?php echo lang('create_backup');?></a></div>
				</div>
			</div>
		</div>
	</div>
<?php }?>
</div>




