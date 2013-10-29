<div class="row">
	<div class="col-md-1 col-lg-2">
	</div>
	<div class="col-sm-6 col-md-5 col-lg-4">
		<h3><?php echo lang('template_layouts');?></h3>
		<table class="table table-hover table-condensed va-middle">
			<thead>
				<tr>
					<th><?php echo lang('name');?></th>
					<th style="width:175px;"><?php echo lang('actions');?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach($layouts as $layout => $data){?>
				<tr>
					<td><?php echo $data['name'];?></td>
					<td>
						<div class="btn-group pull-right">
							<a class="btn btn-default" href="?mod=templates&act=layout_edit&layout=<?php echo $layout;?>"><?php echo lang('edit');?></a>
							<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="caret"></span>
								</button>
								<ul class="dropdown-menu pull-right">
									<li><a href="?mod=templates&act=layout_delete&layout=<?php echo $layout;?>"><?php echo lang('delete');?></a></li>
								</ul>
							</div>
						</div>
					</td>
				</tr>
<?php }?>
				<tr>
					<td> </td>
					<td>
						<a class="btn btn-default pull-right ajaxmodal" data-url="?mod=templates&act=modal_layout_new"><?php echo lang('add');?></a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-sm-6 col-md-5 col-lg-4">
		<h3><?php echo lang('template_files');?></h3>
		<table class="table table-hover table-condensed va-middle">
			<thead>
				<tr>
					<th><?php echo lang('name');?></th>
					<th style="width:150px;"><?php echo lang('actions');?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach($files as $file => $name){?>
				<tr>
					<td><?php echo $name;?></td>
					<td>
						<div class="btn-group pull-right">
							<a class="btn btn-default" href="?mod=templates&act=file_edit&file=<?php echo $file;?>"><?php echo lang('edit');?></a>
							<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="caret"></span>
								</button>
								<ul class="dropdown-menu pull-right">
									<li><a href="?mod=templates&act=file_delete&file=<?php echo $file;?>"><?php echo lang('delete');?></a></li>
								</ul>
							</div>
						</div>
					</td>
				</tr>
<?php }?>
				<tr>
					<td> </td>
					<td>
						<a class="btn btn-default pull-right ajaxmodal"  data-url="?mod=templates&act=modal_saveas&file=new.html"><?php echo lang('add');?></a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-md-1 col-lg-2">
	</div>
</div>