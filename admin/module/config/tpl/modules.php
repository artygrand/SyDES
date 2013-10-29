<table class="table table-hover">
	<thead>
		<tr>
			<th><?php echo lang('name');?></th>
			<th style="width:150px;"><?php echo lang('actions');?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($modules as $module => $data){?>
		<tr>
			<td><?php echo $data['name'];?></td>
			<td>
				<?php if($data['installed']){?>
				<div class="btn-group pull-right">
					<a class="btn btn-default ajaxmodal" href="#" data-url="?mod=config&act=modal_module_edit&module=<?php echo $module;?>"><?php echo lang('edit');?></a>
					<div class="btn-group">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						</button>
						<ul class="dropdown-menu pull-right">
							<li><a href="?mod=config&act=module_uninstall&module=<?php echo $module;?>"><?php echo lang('uninstall');?></a></li>
						</ul>
					</div>
				</div>
			<?php } else {?>
				<a class="btn btn-default pull-right" href="?mod=config&act=module_install&module=<?php echo $module;?>"><?php echo lang('install');?></a>
			<?php }?>
			</td>
		</tr>
<?php }?>
	</tbody>
</table>