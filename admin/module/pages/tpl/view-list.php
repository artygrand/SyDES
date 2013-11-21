<table class="table table-hover table-condensed va-middle">
	<thead>
		<tr>
			<th style="width:14px;"><input type="checkbox" id="checkall" title="<?php echo lang('check_all');?>"></th>
			<th style="width:40px;">ID</th>
			<th style="width:150px;"><?php echo lang('category');?> <a href="?mod=pages&type=<?php echo $type;?>&parent_id=all&skip=0">[<?php echo lang('all');?>]</a></th>
			<th><?php echo lang('name');?></th>
			<th style="width:120px;padding-left:15px"><?php echo lang('status');?></th>
			<th style="width:150px;"><?php echo lang('actions');?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($pages as $page => $data){?>
		<tr class="status-<?php echo $data['status'];?>">
			<td><input type="checkbox" class="ids" name="id[]" value="<?php echo $data['id'];?>"></td>
			<td>#<?php echo $data['id'];?></td>
			<td><a href="?mod=pages&type=<?php echo $type;?>&parent_id=<?php echo $data['parent_id'];?>&skip=0"><?php echo $data['parent_title'];?></a></td>
			<td><a href="..<?php echo $data['fullpath'];?>"><?php echo empty($data['title']) ? lang('no_translation') : $data['title'];?></a></td>
			<td><div class="status-wrap"><?php echo getSelect($statuses, $data['status'], 'data-id="' . $data['id'] . '" class="form-control status input-sm"');?></div></td>
			<td>
				<div class="btn-group btn-block with-dropdown">
					<a class="btn btn-default btn-sm btn-block" href="?mod=pages&type=<?php echo $type;?>&act=edit&id=<?php echo $data['id'];?>"><?php echo lang('edit');?></a>
					<div class="btn-group">
						<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu pull-right">
							<li><a href="?mod=pages&type=<?php echo $type;?>&act=edit&source=<?php echo $data['id'];?>"><?php echo lang('clone');?></a></li>
							<li class="divider"></li>
							<li><a class="danger" href="?mod=pages&act=send&to=trash&id=<?php echo $data['id'];?>"><?php echo lang('delete');?></a></li>
						</ul>
					</div>
				</div>
			</td>
		</tr>
<?php }?>
	</tbody>
</table>