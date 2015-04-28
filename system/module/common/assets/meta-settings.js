$(document).ready(function(){
	$('.dtable').dtable({
		'append':syd.t('add'),
		'remove': syd.t('remove')
	})

	$('.meta-table').on('change', '.meta-type', function(){
		$(this).parents('tr').find('input[type="hidden"]').val('');
		showSettingButtons();
	}).on('click', '.meta-setup', function(){
		var type = $(this).data('type'),
			input = $(this).next(),
			value = input.val();

		switch (type){
			case 'listing':
				$('#settings-modal')
					.one('show.bs.modal', function(e){
						value = value ? JSON.parse(value) : {display:'select', source:''};
						var el = $('#settings-listing').clone();
						el.find('input[name="display"][value="'+value.display+'"]').attr('checked', 'checked');
						el.find('textarea[name="source"]').text(value.source);
						$('#settings-modal .modal-body').html(el.html());

						$('.modal-apply').one('click', function(){
							var form = $(this).parents('.modal-content');
							value.display = form.find('input[name="display"]:checked').val();
							value.source = form.find('textarea[name="source"]').val();
							input.val(JSON.stringify(value));
						})
					})
					.modal('show');
				break
			default:
		}
	}).on('dt.append.row', function(){
		showSettingButtons()
	})

	showSettingButtons();

	$('#settings-modal').on('hide.bs.modal', function(){
		$('.modal-apply').off('click')
	})
	$(document).on('click', '.modal-apply', function(){
		syd.alert(syd.t('temporarily_stored'));
	})
})

function showSettingButtons(){
	$('.meta-setup').hide()
	$('.meta-table tr').each(function(){
		var type = $(this).find('select').val(),
			has_config = ['listing'];
		if (has_config.indexOf(type) > -1){
			$(this).find('.meta-setup').data('type', type).show()
		}
	})
}