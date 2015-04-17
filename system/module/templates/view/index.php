<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title"><?=t('template_layouts');?></h4>
			</div>
			<table class="table table-hover table-condensed va-middle">
				<tbody>
	<? foreach($layouts as $layout => $data){ ?>
					<tr>
						<td><?=$data['name'];?></td>
						<td>
							<div class="btn-group pull-right btn-group-sm">
								<a class="btn btn-default" href="?route=templates/layout/edit<?=$template;?>&layout=<?=$layout;?>"><?=t('edit');?></a>
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a data-toggle="modal" data-target="#modal" data-size="sm" href="?route=templates/layout/cloneit<?=$template;?>&layout=<?=$layout;?>"><?=t('clone');?></a></li>
									<li class="divider"></li>
									<li><a class="danger" href="?route=templates/layout/delete<?=$template;?>&layout=<?=$layout;?>"><?=t('delete');?></a></li>
								</ul>
							</div>
						</td>
					</tr>
	<? } ?>
				</tbody>
			</table>
			<div class="panel-footer">
				<div class="input-group" style="width:100%;">
					<input type="text" name="layout" class="form-control" data-toggle="tooltip" title="<?=t('layout_key')?>" placeholder="article">
					<span class="input-group-btn"><a class="btn btn-default goto" data-url="?route=templates/layout/edit<?=$template;?>&layout="><?=t('add');?></a></span>
				</div>
			</div>
		</div>

<? if ($modules){ ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title"><?=t('module_template_override');?></h4>
			</div>
			<table class="table table-hover table-condensed va-middle">
				<tbody>
	<? foreach($modules as $module){ ?>
					<tr>
						<td><?=$module[1];?></td>
						<td><?=$module[2];?></td>
						<td>
							<div class="btn-group pull-right btn-group-sm">
								<a class="btn btn-default" href="?route=templates/file/edit<?=$template;?>&file=<?=$module[0];?>"><?=t('edit');?></a>
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a data-toggle="modal" data-target="#modal" data-size="sm" href="?route=templates/file/cloneit<?=$template;?>&file=<?=$module[0];?>"><?=t('clone');?></a></li>
									<li class="divider"></li>
									<li><a class="danger" href="?route=templates/file/delete<?=$template;?>&file=<?=$module[0];?>"><?=t('delete');?></a></li>
								</ul>
							</div>
						</td>
					</tr>
	<? } ?>
				</tbody>
			</table>
		</div>
<? } ?>
	</div>


	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title"><?=t('template_files');?></h4>
			</div>
			<table class="table table-hover table-condensed va-middle">
				<tbody>
	<? foreach($files as $file => $name){ ?>
					<tr>
						<td><?=$name;?></td>
						<td>
							<div class="btn-group pull-right btn-group-sm">
								<a class="btn btn-default" href="?route=templates/file/edit<?=$template;?>&file=<?=$file;?>"><?=t('edit');?></a>
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a data-toggle="modal" data-target="#modal" data-size="sm" href="?route=templates/file/cloneit<?=$template;?>&file=<?=$file;?>"><?=t('clone');?></a></li>
									<li class="divider"></li>
									<li><a class="danger" href="?route=templates/file/delete<?=$template;?>&file=<?=$file;?>"><?=t('delete');?></a></li>
								</ul>
							</div>
						</td>
					</tr>
	<? } ?>
				</tbody>
			</table>
			<div class="panel-footer">
				<div class="input-group" style="width:100%;">
					<input type="text" name="file" class="form-control" data-toggle="tooltip" title="<?=t('filename')?>" placeholder="home.html">
					<span class="input-group-btn"><a class="btn btn-default goto" data-url="?route=templates/file/save<?=$template;?>&file="><?=t('add');?></a></span>
				</div>
			</div>
		</div>


		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title"><?=t('iblock_template_override');?></h4>
			</div>
			<table class="table table-hover table-condensed va-middle">
				<tbody>
	<? foreach($iblocks as $iblock){ ?>
					<tr>
						<td>{iblock:<?=$iblock[1];?>?template=<?=$iblock[2];?>}</td>
						<td>
							<div class="btn-group pull-right btn-group-sm">
								<a class="btn btn-default" href="?route=templates/file/edit<?=$template;?>&file=<?=$iblock[0];?>"><?=t('edit');?></a>
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a data-toggle="modal" data-target="#modal" data-size="sm" href="?route=templates/file/cloneit<?=$template;?>&file=<?=$iblock[0];?>"><?=t('clone');?></a></li>
									<li class="divider"></li>
									<li><a class="danger" href="?route=templates/file/delete<?=$template;?>&file=<?=$iblock[0];?>"><?=t('delete');?></a></li>
								</ul>
							</div>
						</td>
					</tr>
	<? } ?>
				</tbody>
			</table>
			<div class="panel-footer">
				<div class="input-group" style="width:100%;">
					<span class="input-group-btn">
						<select class="btn">
						<? foreach($iblock_list as $iblock){ ?>
							<option value="iblock/<?=$iblock;?>/"><?=$iblock;?></option>
						<? } ?>
						</select>
					</span>
					<input type="text" name="file" class="form-control" data-toggle="tooltip" title="<?=t('template_name')?>"  placeholder="flat">
					<span class="input-group-btn"><a class="btn btn-default goto" data-url="?route=templates/file/save<?=$template;?>&file="><?=t('add');?></a></span>
				</div>
			</div>
		</div>
	</div>
</div>



<script>
$(document).ready(function(){
	$('.goto').click(function(){
		var item = $(this).parent().prev().val().match(/[a-z0-9\.-]+/)
		if (item){
			var select = $(this).parent().prev().prev().find('select')
			if (select.length){
				item = select.val() + item + '.php'
			}
			location.href = $(this).data('url') + item
		}
	})
})
</script>