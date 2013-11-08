<div class="row">
	<div class="col-sm-1 col-lg-3"></div>
	<div class="col-sm-10 col-lg-6">
		<table class="table table-hover table-condensed va-middle">
			<thead>
				<tr>
					<th><?php echo lang('label');?></th>
					<th style="width:150px;"><?php echo lang('actions');?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach($iblocks as $iblock){?>
				<tr>
					<td>{iblock:<?php echo $iblock;?>}</td>
					<td>
						<div class="btn-group pull-right">
							<a class="btn btn-default" href="?mod=iblocks&act=edit&iblock=<?php echo $iblock;?>"><?php echo lang('edit');?></a>
							<div class="btn-group">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
								<ul class="dropdown-menu pull-right">
									<li><a href="?mod=iblocks&act=delete&iblock=<?php echo $iblock;?>"><?php echo lang('delete');?></a></li>
								</ul>
							</div>
						</div>
					</td>
				</tr>
<?php }?>
				<tr>
					<td> </td>
					<td><a class="btn btn-default pull-right ajaxmodal"  data-url="?mod=iblocks&act=modal_saveas&iblock=new"><?php echo lang('add');?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-sm-1 col-lg-3"></div>
</div>