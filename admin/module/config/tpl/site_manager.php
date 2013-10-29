<div class="panel-group" id="accordion">
<?php 
$i = 0;
foreach($sites as $site => $data){ $i++;?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#site-<?php echo $site;?>"><?php echo $data['name'];?></a>
			</h4>
		</div>
		<div id="site-<?php echo $site;?>" class="panel-collapse collapse <?php if ($i == 1){echo 'in';}?>">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label><?php echo lang('site_name');?></label>
							<input type="text" name="<?php echo $site;?>[site_name]" class="form-control" placeholder="<?php echo lang('my_super_site');?>" value="<?php if ($site != 'new'){echo $data['name'];}?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?php echo lang('locales');?></label>
							<input type="text" name="<?php echo $site;?>[locales]" class="form-control" placeholder="en ru de ua" data-toggle="tooltip" title="<?php echo lang('tip_locales');?>" value="<?php echo $data['locales'];?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?php echo lang('modules');?></label>
							<?php echo $data['modules'];?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label><?php echo lang('connectet_domains');?></label>
							<textarea name="<?php echo $site;?>[domains]" class="form-control" rows="5" data-toggle="tooltip" title="<?php echo lang('tip_enter_on_new_line');?>" placeholder="site.com"><?php echo $data['domains'];?></textarea>
						</div>
					</div>
<?php if ($site != 'new'){?>
					<div class="col-md-6 text-right col-md-offset-6">
						<a data-toggle="popover" title="<a href='?mod=config&act=site_delete&site=<?php echo $site;?>&token=<?php echo Admin::$token;?>' class='btn btn-danger'><?php echo lang('delete');?></a>" data-placement="top" data-content="<?php echo lang('delete_site_confirm');?>" href="#"><?php echo lang('delete_site');?></a>
					</div>
<?php }?>
				</div>
			</div>
		</div>
	</div>
<?php }?>
</div>



