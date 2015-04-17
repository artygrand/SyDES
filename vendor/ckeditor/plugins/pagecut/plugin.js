CKEDITOR.plugins.add('pagecut', {
	lang: 'de,en,ru',
	onLoad: function(){
		var css = ('display:block;clear:both;width:100%;border-top:#999 1px dotted;padding:0;height:1px;cursor:default;');
		var cssBefore = (
				'content:"";' +
				'background: url(' + CKEDITOR.getUrl( this.path + 'images/image.png' ) + ') no-repeat right center;' +
				'height:14px;width:25px;position:relative;display:block;top:-8px;float:right;'
			);
		CKEDITOR.addCss( '#cut{' + css + '} #cut:before{' + cssBefore + '}' );
	},
	init: function(editor) {
		editor.addCommand('insertPagecut', {
			exec: function(editor) {
				var element = CKEDITOR.dom.element.createFromHtml('<hr id="cut"/>');
				editor.insertElement(element);
			}
		});

		editor.ui.addButton('Pagecut', {
			label: editor.lang.pagecut.toolbar,
			command: 'insertPagecut',
			icon: this.path + 'images/icon.png',
			toolbar: 'links'
		});
	}
});