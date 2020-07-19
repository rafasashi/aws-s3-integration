(function( $ ) {

	var $body = $( 'body' );

	$body.on( 'click', '.as3i-notice .notice-dismiss', function( e ) {
		var id = $( this ).parents( '.as3i-notice' ).attr( 'id' );
		if ( id ) {
			var data = {
				action: 'as3i-dismiss-notice',
				notice_id: id,
				_nonce: as3i_notice.nonces.dismiss_notice
			};

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'JSON',
				data: data,
				error: function( jqXHR, textStatus, errorThrown ) {
					alert( as3i_notice.strings.dismiss_notice_error + errorThrown );
				}
			} );
		}
	} );

	$body.on( 'click', '.as3i-notice-toggle', function( e ) {
		e.preventDefault();
		var $link = $( this );
		var label = $link.data( 'hide' );

		$link.data( 'hide', $link.html() );
		$link.html( label );

		$link.closest( '.as3i-notice' ).find( '.as3i-notice-toggle-content' ).toggle();
	} );

})( jQuery );
