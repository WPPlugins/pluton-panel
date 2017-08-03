(function($) {
	"use strict";

	// Select & insert image
	var _custom_media = true,
	_orig_send_attachment = wp.media.editor.send.attachment;

	$('.pluton-media-upload-button').click(function(e) {
		var send_attachment_bkp	= wp.media.editor.send.attachment,
			button = $(this),
			id = button.prev();
		wp.media.editor.send.attachment = function(props, attachment){
			if ( _custom_media ) {
				$( id ).val( attachment.id );
				var $preview = button.parent().find('.pluton-media-live-preview img');
				if ( $preview.length ) {
					$preview.attr( 'src', attachment.url );
				} else {
					$preview = button.parent().find('.pluton-media-live-preview');
					var $imgSize = $preview.data( 'image-size' ) ? $preview.data( 'image-size' ) : 'auto';
					$preview.append( '<img src="'+ attachment.url +'" style="height:'+ $imgSize +'px;width:'+ $imgSize +'px;" />' );
				}
			} else {
				return _orig_send_attachment.apply( this, [props, attachment] );
			};
		}
		wp.media.editor.open( button );
		return false;
	} );

	$( '.add_media' ).on('click', function() {
		_custom_media = false;
	} );

} ) ( jQuery );