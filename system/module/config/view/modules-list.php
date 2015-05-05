<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title"><?=t('modules');?></h4>
	</div>
	<table class="table table-hover table-condensed va-middle">
		<tbody>
	<?php foreach ($modules as $key => $module){ ?>
			<tr>
				<td><?=$module['name'];?></td>
				<td>
				<?php if ($module['installed']){ ?>
					<div class="btn-group pull-right btn-group-sm">
						<a class="btn btn-default" href="?route=<?=$module['route'];?>/config"><?=t('edit');?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li><a href="?route=<?=$module['route'];?>/uninstall"><?=t('uninstall');?></a></li>
						</ul>
					</div>
				<?php } else { ?>
					<a class="btn btn-sm btn-default pull-right" href="?route=<?=$module['route'];?>/install"><?=t('install');?></a>
				<?php } ?>
				</td>
			</tr>
	<?php } ?>
		</tbody>
	</table>
</div>
