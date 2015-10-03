CKEDITOR.plugins.add('bootstrapstyles', {
	init: function(editor){
		editor.addContentsCss(this.path + 'css/bootstrap.css');
	}
});