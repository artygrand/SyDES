'use strict';

( function() {

	CKEDITOR.plugins.add( 'more', {
		requires: 'fakeobjects',
		lang: 'de,en,ru',
		icons: 'more',
		onLoad: function() {
			var cssStyles = ('clear:both;width:100%;border-top:#999 1px dotted;padding:0;height:1px;cursor:default;')
				.replace( /;/g, ' !important;' );
			var cssStylesBefore = (
					'content:"";' +
					'background: url(' + CKEDITOR.getUrl( this.path + 'images/more.png' ) + ') no-repeat right center;' +
					'height:14px;width:25px;position:relative;display:block;top:-8px;float:right;'
				).replace( /;/g, ' !important;' );
			CKEDITOR.addCss( 'div.cke_more{' + cssStyles + '} .cke_more:before{' + cssStylesBefore + '}' );
		},

		init: function( editor ) {
			if ( editor.blockless )
				return;
			editor.addCommand( 'more', CKEDITOR.plugins.moreCmd );
			editor.ui.addButton && editor.ui.addButton( 'More', {
				label: editor.lang.more.toolbar,
				command: 'more',
				toolbar: 'links'
			} );
			( CKEDITOR.env.opera || CKEDITOR.env.webkit ) && editor.on( 'contentDom', function() {
				editor.document.on( 'click', function( evt ) {
					var target = evt.data.getTarget();
					if ( target.is( 'div' ) && target.hasClass( 'cke_more' ) )
						editor.getSelection().selectElement( target );
				} );
			} );
		},

		afterInit: function( editor ) {
			var dataProcessor = editor.dataProcessor,
				dataFilter = dataProcessor && dataProcessor.dataFilter,
				htmlFilter = dataProcessor && dataProcessor.htmlFilter;

			function upcastMore( element ) {
				CKEDITOR.tools.extend( element.attributes, attributesSet( editor.lang.more.alt ), true );
				element.children.length = 0;
			}

			if ( htmlFilter ) {
				htmlFilter.addRules( {
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
				}, { applyToAll: true, priority: 5 } );
			}

			if ( dataFilter ) {
				dataFilter.addRules( {
					elements: {
						div: function( element ) {
							if ( element.attributes[ 'data-cke-more' ] )
								upcastMore( element );
							else if ( (/more/i ).test( element.attributes.tag ) ) {
								var child = element.children[ 0 ];
								upcastMore( element );
							}
						}
					}
				} );
			}
		}
	} );

	CKEDITOR.plugins.moreCmd = {
		exec: function( editor ) {
			var more = editor.document.createElement( 'div', {
				attributes: attributesSet( editor.lang.more.alt )
			} );

			editor.insertElement( more );
		},
		context: 'div',
		allowedContent: 'div[tag]'
	};

	function attributesSet( label ) {
		return {
			'aria-label': label,
			'class': 'cke_more',
			'tag': 'more',
			contenteditable: 'false',
			'data-cke-display-name': 'more',
			'data-cke-more': 1,
			title: label
		};
	}
} )();