/*global ajaxurl */

jQuery( function ( $ ) {
	$( 'table.widefat tbody th, table.widefat tbody td' ).css( 'cursor', 'move' );

	$( 'table.widefat tbody' ).sortable( {
		items: 'tr:not(.inline-edit-row)',
		cursor: 'move',
		axis: 'y',
		containment: 'table.widefat',
		scrollSensitivity: 40,
		helper: function ( event, ui ) {
			ui.each( function () {
				$( this ).width( $( this ).width() );
			} );
			return ui;
		},
		start: function ( event, ui ) {
			ui.item.css( 'background-color', '#ffffff' );
			ui.item.children( 'td, th' ).css( 'border-bottom-width', '0' );
			ui.item.css( 'outline', '1px solid #dfdfdf' );
		},
		stop: function ( event, ui ) {
			ui.item.removeAttr( 'style' );
			ui.item.children( 'td,th' ).css( 'border-bottom-width', '1px' );
		},
		update: function ( event, ui ) {
			$( 'table.widefat tbody th, table.widefat tbody td' ).css( 'cursor', 'default' );
			$( 'table.widefat tbody' ).sortable( 'disable' );

			var postid = ui.item.find( '.check-column input' ).val();
			var prevpostid = ui.item.prev().find( '.check-column input' ).val();
			var nextpostid = ui.item.next().find( '.check-column input' ).val();

			// Show Spinner
			ui.item
					.find( '.check-column input' )
					.hide()
					.after( '<img alt="processing" src="images/wpspin_light.gif" class="waiting" style="margin-left: 6px;" />' );

			// Go do the sorting stuff via ajax
			$.post( ajaxurl, {
				action: '_enr_post_ordering',
				id: postid,
				previd: prevpostid,
				nextid: nextpostid
			}, function ( response ) {
				ui.item.find( '.check-column input' ).show().siblings( 'img' ).remove();
				$( 'table.widefat tbody th, table.widefat tbody td' ).css( 'cursor', 'move' );
				$( 'table.widefat tbody' ).sortable( 'enable' );
			} );
		}
	} );
} );
