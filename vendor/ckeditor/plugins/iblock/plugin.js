CKEDITOR.plugins.add('iblock', {
	requires : ['richcombo'],
	lang: 'en,ru',
	init : function( editor ){
		var iblocks;
		$.ajax({
			url: '?route=common/getiblocks',
			complete: function(){
				iblocks = window.respond
			}
		})

		CKEDITOR.addCss('.iblock{background:#eee;border:1px solid #ccc;padding:5px 10px;text-align:center;}');

		editor.ui.addRichCombo('iblock',{
			label: editor.lang.iblock.toolbar,
			title: editor.lang.iblock.tip,
			voiceLabel: 'iBlock',
			className: 	'cke_format',
			multiSelect: false,
			panel:{
				css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
				voiceLabel: editor.lang.panelVoiceLabel
			},

			init: function(){
				this.startGroup(editor.lang.iblock.select);
				for (var i in iblocks){
					this.add(iblocks[i][0], iblocks[i][1], iblocks[i][2]);
				}
			},

			onClick: function(value){
				var el = editor.document.createElement('div',{'attributes':{'class':'iblock'}});
				el.appendHtml('{iblock:' + value + '}');
				editor.focus();
				editor.fire('saveSnapshot');
				editor.insertElement(el);
				editor.fire('saveSnapshot');
			}
		});
	}
});