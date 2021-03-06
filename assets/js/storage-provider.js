(function( $ ) {
	var $body = $( 'body' );

	var as3i = as3i || {};

	/**
	 * Handle changes to the selected Storage Provider and Access Key saving.
	 */
	as3i.storageProvider = {
		changed: function() {
			var provider = $( 'input[name="provider"]:checked' ).val();

			// Hide and disable all providers.
			$( '.as3i-provider-content' ).each( function() {
				as3i.storageProvider.disableContent( this );
			} );

			// Show and enable selected provider.
			$( '.as3i-provider-content[data-provider="' + provider + '"]' ).each( function() {
				as3i.storageProvider.enableContent( this );
			} );
		},

		disableContent: function( element ) {
			$( element ).hide();
			$( element ).removeClass( 'as3i-provider-selected' );
			$( element ).find( 'input' ).prop( 'disabled', true );
			$( element ).find( 'textarea.as3i-large-input' ).prop( 'disabled', true );
		},

		enableContent: function( element ) {
			$( element ).find( 'input:not( [data-as3i-disabled="true"] )' ).prop( 'disabled', false );
			$( element ).find( 'textarea.as3i-large-input' ).prop( 'disabled', false );
			$( element ).addClass( 'as3i-provider-selected' );
			$( element ).show( 'fast', function() {
				as3i.storageProvider.setSelectedAuthMethod( this );
			} );
		},

		setSelectedAuthMethod: function( element ) {
			$( element ).find( 'input[name="authmethod"]:checked' ).prop( 'checked', true ).change();

			// If exactly one auth method isn't selected, select the first.
			var checkedCount = $( element ).find( 'input[name="authmethod"]:checked' ).length;

			if ( 1 !== checkedCount ) {
				$( element ).find( 'input[name="authmethod"]:not( [data-as3i-disabled="true"] )' ).first().prop( 'checked', true ).change();
			}
		},

		authMethodChanged: function() {
			var authMethod = $( 'input[name="authmethod"]:checked' ).val();

			// Hide all auth methods.
			$( '.asc3f-provider-authmethod-content' ).each( function() {
				as3i.storageProvider.disableAuthMethodContent( this );
			} );

			// Show selected auth method.
			$( '.asc3f-provider-authmethod-content[data-provider-authmethod="' + authMethod + '"]' ).each( function() {
				as3i.storageProvider.enableAuthMethodContent( this );
			} );
		},

		disableAuthMethodContent: function( element ) {
			$( element ).hide();
		},

		enableAuthMethodContent: function( element ) {
			$( element ).show();
		}
	};

	$( document ).ready( function() {
		// Switch displayed storage provider content.
		$body.on( 'change', 'input[name="provider"]', function( e ) {
			e.preventDefault();
			as3i.storageProvider.changed();
		} );
		// Switch displayed storage provider auth method content.
		$body.on( 'change', 'input[name="authmethod"]', function( e ) {
			e.preventDefault();
			as3i.storageProvider.authMethodChanged();
		} );
	} );

})( jQuery );
