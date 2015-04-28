<div>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab-fields" data-toggle="tab">Поля</a></li>
		<li class=""><a href="#tab-settings" data-toggle="tab">Настройки</a></li>
		<li class=""><a href="#tab-notice" data-toggle="tab">Оповещения</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="tab-fields">
			<div class="row">
				<div class="col-sm-8">
					<div class="panel-group ready" id="form-holder">
						<div class="onempty">Нажмите на элемент формы или перетащите сюда, что бы добавить в форму.<br>Вы можете сортировать их в форме перетаскиванием и редактировать после клика.</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="list-group insert-field">
						<a href="#" class="list-group-item" data-type="string"><?=t('input_text');?></a>
						<a href="#" class="list-group-item" data-type="textarea"><?=t('input_textarea');?></a>
						<a href="#" class="list-group-item" data-type="email"><?=t('input_email');?></a>
						<a href="#" class="list-group-item" data-type="phone"><?=t('input_phone');?></a>
						<a href="#" class="list-group-item" data-type="file"><?=t('input_file');?></a>
						<a href="#" class="list-group-item" data-type="number"><?=t('input_number');?></a>
						<a href="#" class="list-group-item" data-type="listing"><?=t('input_listing');?></a>
						<a href="#" class="list-group-item" data-type="date"><?=t('input_date');?></a>
						<a href="#" class="list-group-item" data-type="hidden"><?=t('input_hidden');?></a>
					</div>
				</div>
			</div>
		</div>


		<div class="tab-pane form-horizontal" id="tab-settings">
			<input type="hidden" name="settings[id]" value="<??>">
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('form_name');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[name]" class="form-control" value="<?=$form['name'];?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('description');?></label>
				<div class="col-sm-10">
					<textarea name="settings[description]" class="form-control" rows="3"><?=$form['description'];?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('success_text');?></label>
				<div class="col-sm-10">
					<textarea name="settings[success_text]" class="form-control" rows="3"><?=$form['success_text'];?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('submit_button_text');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[submit_button]" class="form-control" value="<?=$form['submit_button'];?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('display_as');?></label>
				<div class="col-sm-10">
					<?=H::radio('display_as', $form['display_as'], array('block', 'modal', 'none'), array('inline' => true));?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?=t('form_attributes');?></label>
				<div class="col-sm-10">
					<input type="text" name="settings[form_attr]" class="form-control" value="<?=$form['form_attr'];?>">
				</div>
			</div>
		</div>


		<div class="tab-pane form-horizontal" id="tab-notice">
<? foreach($notices as $notice){ ?>
			<div class="notice-block">
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('to');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[to][<?=$notice['id'];?>]" class="form-control" value="<?=$notice['to'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="#">Почта из другой формы</a></li>
									<li><a href="#">Поле MAIL (если создано)</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('from');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[from][<?=$notice['id'];?>]" class="form-control" value="<?=$notice['from'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="#">Почта из другой формы</a></li>
									<li><a href="#">Поле MAIL (если создано)</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('subject');?></label>
					<div class="col-sm-10">
						<div class="input-group">
							<input type="text" name="notice[subject][<?=$notice['id'];?>]" class="form-control" value="<?=$notice['subject'];?>">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?=t('set');?> <span class="caret"></span></button>
								<ul class="dropdown-menu dropdown-menu-right">
									<li><a href="#"><?=t('from_form_name');?></a></li>
								</ul>
							</div>
						</div>
					</div>
					
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?=t('message');?></label>
					<div class="col-sm-10">
						<textarea name="notice[body][<?=$notice['id'];?>]" class="form-control" rows="15"><?=$notice['body'];?></textarea>
						<button type="button" class="btn btn-default btn-xs"><?=t('generate');?></button>
					</div>
				</div>
			</div>
<? } ?>
		</div>
	</div>
</div>

<style>
.tab-pane{padding-top:15px;}
.onempty{color:#666;text-align:center;padding-top:150px;}
.onempty:not(:only-of-type){display:none;}
.panel{position:relative;}
.panel:hover .widget-tools{display:block;}
.field-remove{cursor:pointer;}
.panel-group .panel-heading{cursor:move;}
#form-holder{min-height:375px;padding-bottom:50px;border:3px dashed transparent;}
#form-holder.ready{border-color:#ddd;}
</style>


<div id="field-base" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Заголовок</label>
				<input type="text" class="form-control" name="fields[label][]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Ключ</label>
				<input type="text" class="form-control" name="fields[key][]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Описание</label>
				<input type="text" class="form-control" name="fields[description][]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>&nbsp;</label>
				<div class="checkbox"><label class="checkbox-inline"><input type="checkbox" name="fields[required][]" value="1"> Обязательное</label></div>
			</div>
		</div>
	</div>
</div>

<div id="field-attr" class="hidden">
	<div class="form-group">
		<label>Атрибуты</label>
		<input type="text" class="form-control" name="fields[attr][]">
		<p class="help-block">Классы, идентификатор, дата-атрибуты</p>
	</div>
</div>

<div id="field-text" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Плейсхолдер</label>
				<input type="text" class="form-control" name="fields[placeholder][]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Значение по умолчанию</label>
				<input type="text" class="form-control" name="fields[defaults][]">
			</div>
		</div>
	</div>
</div>

<div id="field-number" class="hidden">
	<div class="form-group">
		<label>Значения</label>
		<div class="row">
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[min][]" placeholder="Минимум">
			</div>
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[step][]" placeholder="Шаг">
			</div>
			<div class="col-sm-4">
				<input type="text" class="form-control" name="fields[max][]" placeholder="Максимум">
			</div>
		</div>
	</div>
</div>

<div id="field-text-rows" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Количество строк</label>
				<input type="text" class="form-control" name="fields[rows][]">
			</div>
		</div>
	</div>
</div>

<div id="field-listing" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Варианты ответа</label>
				<textarea class="form-control" name="fields[source][]" rows="5"></textarea>
				<p class="help-block">Вводите элементы списка по одному на строку в формате ключ|значение</p>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Значение по умолчанию</label>
				<input type="text" class="form-control" name="fields[defaults][]">
			</div>
			<div class="form-group">
				<div class="checkbox"><label><input type="checkbox" name="fields[list_type][]" value="select" checked> Выпадающий список</label></div>
				<div class="checkbox"><label><input type="checkbox" name="fields[list_type][]" value="checkbox"> Чекбоксы</label></div>
				<div class="checkbox"><label><input type="checkbox" name="fields[list_type][]" value="radio"> Радио</label></div>
			</div>
		</div>
	</div>
</div>

<div id="field-allowed-files" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Допустимые расширения файлов</label>
				<input type="text" class="form-control" name="fields[allowed-files][]">
				<p class="help-block">Вводите через запятую</p>
			</div>
		</div>
	</div>
</div>

<div id="field-hidden" class="hidden">
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<label>Ключ</label>
				<input type="text" class="form-control" name="fields[key][]">
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<label>Значение по умолчанию</label>
				<input type="text" class="form-control" name="fields[defaults][]">
			</div>
		</div>
	</div>
</div>