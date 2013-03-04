CKEDITOR.plugins.add('more',{requires:'fakeobjects',lang:'de,en,ru',icons:'more',
	onLoad: function(){
		var cssStyles = [
			'{',
				'clear: both;',
				'width:100%; _width:99.9%;',
				'border-top: #999999 1px dotted;',
				'padding:0;',
				'height: 1px;',
				'cursor: default;',
			'}'
			].join('').replace( /;/g, ' !important;' );
		CKEDITOR.addCss( 'div.cke_more' + cssStyles + '.cke_more:before{content:"";background: url(' + CKEDITOR.getUrl( this.path + 'images/more.png' ) + ') no-repeat right center;height:14px;width:25px;position:relative;display:block;top:-8px;float:right;}');
	},
	init: function( editor ) {
		if ( editor.blockless )
			return;
		editor.addCommand( 'more', CKEDITOR.plugins.moreCmd );
		editor.ui.addButton && editor.ui.addButton( 'More', {
			label: editor.lang.more.toolbar,
			command: 'more',
			toolbar: 'links'
		});
		CKEDITOR.env.opera && editor.on( 'contentDom', function() {
			editor.document.on( 'click', function( evt ) {
				var target = evt.data.getTarget();
				if ( target.is( 'div' ) && target.hasClass( 'cke_more' ) )
					editor.getSelection().selectElement( target );
			});
		});
	},

	afterInit: function( editor ) {
		var label = editor.lang.more.alt;
		var dataProcessor = editor.dataProcessor,
			dataFilter = dataProcessor && dataProcessor.dataFilter,
			htmlFilter = dataProcessor && dataProcessor.htmlFilter;
		if ( htmlFilter ) {
			htmlFilter.addRules({
				attributes: {
					'class': function( value, element ) {
						var className = value.replace( 'cke_more', '' );
						if ( className != value ) {
							element.children.length = 0;
							var attrs = element.attributes;
							delete attrs[ 'aria-label' ];
							delete attrs.contenteditable;
							delete attrs.title;
						}
						return className;
					}
				}
			}, 5 );
		}

		if ( dataFilter ) {
			dataFilter.addRules({
				elements: {
					div: function( element ) {
						var attributes = element.attributes,
							tag = attributes && attributes.tag;
						if ( ( /more/i ).test( tag )) {
							attributes.contenteditable = "false";
							attributes[ 'class' ] = "cke_more";
							attributes[ 'data-cke-display-name' ] = "more";
							attributes[ 'aria-label' ] = label;
							attributes[ 'title' ] = label;

							element.children.length = 0;
						}
					}
				}
			});
		}
	}
});
CKEDITOR.plugins.moreCmd = {
	exec: function( editor ) {
		var label = editor.lang.more.alt;
		var more = CKEDITOR.dom.element.createFromHtml( '<div tag="more" contenteditable="false" title="' + label + '" aria-label="' + label + '" data-cke-display-name="more" class="cke_more"></div>', editor.document );
		editor.insertElement(more);
	},
	context: 'div'
};
