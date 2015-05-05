(function(global){
	var meta = {version:'1.1.0'};
	meta.init = function(options){
		_this = this;
		this.settings = $.extend({
			module:'pages',
			page_id:0,
			permanent:[],
		}, options);

		$('#tab-meta').append('<div id="meta-permanent"><div class="row"></div></div>');
		$('.sidebar-right').append('<div id="meta-variable"></div>');
		
		this.permanent = $('#meta-permanent .row');
		this.variable = $('#meta-variable');

		$.ajax({
			url: '?route=common/meta/get&module=' + this.settings.module,
			data:{
				page_id: this.settings.page_id,
				permanent: this.settings.permanent,
			},
			complete: function(){
				_this.variable.append(window.respond.base);
				$('#keys').combobox();
				$('[data-toggle="tooltip"]').tooltip();
				for(var item in window.respond.meta){
					if ($.inArray(item, _this.settings.permanent) > -1){
						var el = $('<div/>').addClass('col-sm-6').append(window.respond.meta[item]).appendTo(_this.permanent);
					} else {
						_this.variable.append(window.respond.meta[item]);
					}
				}
			}
		})
	}

	meta.add = function(){
		var input = $('.meta-value').find(':input').eq(0), value
		if (input.is('[type="checkbox"]')){
			value = $('[name="' + input.attr('name') + '"]:checked').map(function(){return this.value;}).get().join(',')
		} else {
			value = input.val()
		}
		if (value == '' || $('#meta-key').val() == ''){
			return false
		};
		$.ajax({
			url: '?route=common/meta/add&module=' + this.settings.module,
			data:{
				page_id: this.settings.page_id,
				key: $('#meta-key').val(),
				value: value
			},
			complete: function(){
				for(var item in window.respond.meta){
					$("#meta-variable").append(window.respond.meta[item]);
				}
				$('#meta-key').val('')
				meta.load('e#eq')
				$('[data-toggle="tooltip"]').tooltip();
			}
		})
	};

	meta.update = function(id, key, value){
		if (value == '' || id === undefined || (id < 1 && this.settings.page_id < 1)){
			return
		}
		var action = (id == 0) ? 'add' : 'update',
			data = (id == 0) ? {'page_id':this.settings.page_id,'key':key,'value':value} : {'id':id,'value':value};
		$.ajax({
			url: '?route=common/meta/' + action + '&module=' + this.settings.module,
			data: data
		})
	};

	meta.delete = function(id){
		$.ajax({
			url: '?route=common/meta/delete&module=' + this.settings.module,
			data:{
				id: id
			},
			complete: function(){
				$('.meta-field[data-id="' + id + '"]').remove()
			}
		})
	};
	
	meta.load = function(key){
		if (key.length < 3){
			return;
		}
		$.ajax({
			url: '?route=common/meta/load&module=' + this.settings.module,
			data:{
				key: key
			},
			complete: function(){
				$('.meta-value').replaceWith(window.respond.meta)
			}
		})
	};

	global.meta = meta;
}(this));

$(document).on('change', '#meta-key', function(){
	if (window.lastkey != $(this).val()){
		meta.load($(this).val());
		window.lastkey = $(this).val()
	}
}).on('keyup', '#meta-key', function(){
	meta.load($(this).val());
	window.lastkey = $(this).val()
}).on('change', '[name^="meta["]', function(){
	var value, key, id = $(this).parents('.meta-field').data('id'), name = $(this).attr('name')
	if (id == 0){
		var name = name.replace(/\[(.+?)\]/, function(d,c){key = c})
	}
	if ($(this).is('[type="checkbox"]')){
		value = $('[name="' + name + '"]:checked').map(function(){return this.value;}).get().join(',')
	} else {
		value = $(this).val()
	}
	meta.update(id, key, value)
})