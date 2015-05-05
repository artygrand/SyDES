<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('module_form');?></h4>
	</div>
	<table class="table table-hover table-condensed va-middle">
		<thead>
			<tr>
				<th><?=t('form_name');?></th>
				<th><?=t('token');?></th>
				<th><?=t('results');?></th>
				<th style="width:150px;"><?=t('actions');?></th>
			</tr>
		</thead>
		<tbody>
	<?php foreach($forms as $form){ ?>
			<tr>
				<td><?=$form['name'];?></td>
				<td>{iblock:form?show=<?=$form['id'];?>}</td>
				<td><a href="?route=constructors/form/results&id=<?=$form['id'];?>"><?=isset($results[$form['id']]) ? $results[$form['id']] : 0;?></a></td>
				<td>
					<div class="btn-group pull-right btn-group-sm">
						<a class="btn btn-default" href="?route=constructors/form/edit&id=<?=$form['id'];?>"><?=t('edit');?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li><a href="?route=constructors/form/cloneit&id=<?=$form['id'];?>"><?=t('clone');?></a></li>
							<li class="divider"></li>
							<li><a class="danger" href="?route=constructors/form/delete&id=<?=$form['id'];?>"><?=t('delete');?></a></li>
						</ul>
					</div>
				</td>
			</tr>
	<?php } ?>
		</tbody>
	</table>
	<div class="panel-footer">
	<form action="?route=constructors/form/add" method="post">
		<div class="input-group">
			<input type="text" name="name" class="form-control" data-toggle="tooltip" title="<?=t('form_name')?>" placeholder="<?=t('form_name_example')?>" required>
			<span class="input-group-btn"><button type="submit" class="btn btn-default"><?=t('add');?></button></span>
		</div>
	</form>
	</div>
</div>