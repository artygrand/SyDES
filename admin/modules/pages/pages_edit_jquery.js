$('input[name="alias"]').change(function(){
				if ($('#id').val() == '' || !$('button[type="submit"]')[0]){return false}
				$.ajax({
					type: 'POST',
					url: 'ajax.php?mod=pages&act=setnewalias',
					data: {id: $('#id').val(), alias: $(this).val()}
				})
			})
			$('select[name="parent_id"]').change(function(){
				if ($('#id').val() == '' || !$('button[type="submit"]')[0]){return false}
				$.ajax({
					type: 'POST',
					url: 'ajax.php?mod=pages&act=setnewparent',
					data: {id: $('#id').val(), parent_id: $(this).val()}
				})
			})