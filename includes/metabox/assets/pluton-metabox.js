// This file doesn't actually load on the front-end...it's minified and included inline via metabox.php
( function( $ ) {
	"use strict";

	$( document ).on( 'ready', function() {

		// Tabs
		$( 'div#pluton-metabox ul.wp-tab-bar a').click( function() {
			var lis = $( '#pluton-metabox ul.wp-tab-bar li' ),
				data = $( this ).data( 'tab' ),
				tabs = $( '#pluton-metabox div.wp-tab-panel');
			$( lis ).removeClass( 'wp-tab-active' );
			$( tabs ).hide();
			$( data ).show();
			$( this ).parent( 'li' ).addClass( 'wp-tab-active' );
			return false;
		} );

		// Color picker
		$('div#pluton-metabox .pluton-mb-color-field').wpColorPicker();

		// Media uploader
		var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;

		$('div#pluton-metabox .pluton-mb-uploader').click(function(e) {
			var send_attachment_bkp	= wp.media.editor.send.attachment,
				button = $(this),
				id = button.prev();
			wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media ) {
					$( id ).val( attachment.id );
				} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
				};
			}
			wp.media.editor.open( button );
			return false;
		} );

		$( 'div#pluton-metabox .add_media' ).on('click', function() {
			_custom_media = false;
		} );

		// Reset
		$( 'div#pluton-metabox div.pluton-mb-reset a.pluton-reset-btn' ).click( function() {
			var $confirm = $( 'div.pluton-mb-reset div.pluton-reset-checkbox' ),
				$txt     = confirm.is(':visible') ? "<?php esc_html_e(  'Reset Settings', 'pluton' ); ?>" : "<?php esc_html_e(  'Cancel Reset', 'pluton' ); ?>";
			$( this ).text( $txt );
			$( 'div.pluton-mb-reset div.pluton-reset-checkbox input' ).attr('checked', false);
			$confirm.toggle();
		});

		// Show hide Custom Width options
		var widthField = $( 'div#pluton-metabox select#pluton_post_layout' ),
			widthFieldDependents = $( '#pluton_post_custom_width_tr');
		if ( widthField.val() === 'full-width' ) {
			widthFieldDependents.show();
		} else {
			widthFieldDependents.hide();
		}
		widthField.change(function () {
			if ( $(this).val() === 'full-width' ) {
				widthFieldDependents.show();
			} else {
				widthFieldDependents.hide();
			}
		} );

		// Show hide Transparent options
		var transparentField = $( 'div#pluton-metabox select#pluton_header_style' ),
			transparentFieldDependents = $( '#pluton_transparent_header_color_tr, #pluton_transparent_header_logo_tr,#pluton_transparent_header_logo_retina_tr,#pluton_transparent_header_logo_retina_height_tr');
		if ( transparentField.val() === 'transparent' ) {
			transparentFieldDependents.show();
		} else {
			transparentFieldDependents.hide();
		}
		transparentField.change(function () {
			if ( $(this).val() === 'transparent' ) {
				transparentFieldDependents.show();
			} else {
				transparentFieldDependents.hide();
			}
		} );

		// Show hide Full Screen options
		var full_screenField = $( 'div#pluton-metabox select#pluton_header_style' ),
			full_screenFieldDependents = $( '#pluton_full_screen_header_logo_tr, #pluton_full_screen_header_logo_height_tr');
		if ( full_screenField.val() === 'full_screen' ) {
			full_screenFieldDependents.show();
		} else {
			full_screenFieldDependents.hide();
		}
		full_screenField.change(function () {
			if ( $(this).val() === 'full_screen' ) {
				full_screenFieldDependents.show();
			} else {
				full_screenFieldDependents.hide();
			}
		} );

		// Show hide title options
		var titleField          = $( 'div#pluton-metabox select#pluton_disable_title' ),
			titleMainSettings   = $( '#pluton_disable_header_margin_tr, #pluton_post_subheading_tr,#pluton_post_title_style_tr'),
			titleStyleField     = $( 'div#pluton-metabox select#pluton_post_title_style' ),
			titleStyleFieldVal  = titleStyleField.val(),
			pageTitleBgSettings = $( '#pluton_post_title_background_color_tr, #pluton_post_title_background_tr,#pluton_post_title_height_tr,#pluton_post_title_background_overlay_tr,#pluton_post_title_background_overlay_opacity_tr'),
			solidColorElements  = $( '#pluton_post_title_background_color_tr');

		if ( titleField.val() === 'on' ) {
			titleMainSettings.hide();
			pageTitleBgSettings.hide();
		} else {
			titleMainSettings.show();
		}

		if ( titleStyleFieldVal === 'background-image' ) {
			pageTitleBgSettings.show();
		} else if ( titleStyleFieldVal === 'solid-color' ) {
			solidColorElements.show();
		}

		titleField.change(function () {
			if ( $(this).val() === 'on' ) {
				titleMainSettings.hide();
				pageTitleBgSettings.hide();
			} else {
				titleMainSettings.show();
				var titleStyleFieldVal = titleStyleField.val();
				if ( titleStyleFieldVal === 'background-image' ) {
					pageTitleBgSettings.show();
				} else if ( titleStyleFieldVal === 'solid-color' ) {
					solidColorElements.show();
				}
			}
		} );

		titleStyleField.change(function () {
			pageTitleBgSettings.hide();
			if ( $(this).val() == 'background-image' ) {
				pageTitleBgSettings.show();
			}
			else if ( $(this).val() === 'solid-color' ) {
				solidColorElements.show();
			}
		} );

	} );

} ) ( jQuery );	