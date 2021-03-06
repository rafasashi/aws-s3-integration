( function( $, as3iModal ) {

	var savedSettings = {};
	var bucketNamePattern = /[^a-z0-9.-]/;

	var $body = $( 'body' );
	var $tabs = $( '.as3i-tab' );
	var $settings = $( '.as3i-settings' );
	var $activeTab;

	/**
	 * Return the serialized string of the settings form
	 * excluding the bucket and region inputs as they get saved via AJAX
	 *
	 * @param string tab
	 *
	 * @returns {string}
	 */
	function serializedForm( tab ) {
		return $( '#' + tab + ' .as3i-main-settings form' ).find( 'input:not(.no-compare)' ).serialize();
	}

	/**
	 * Set checkbox
	 *
	 * @param string checkbox_wrap
	 */
	function setCheckbox( checkbox_wrap ) {
		var $switch = $activeTab.find( '#' + checkbox_wrap );
		var $checkbox = $switch.find( 'input[type=checkbox]' );

		$switch.toggleClass( 'on' ).find( 'span' ).toggleClass( 'checked' );
		var switchOn = $switch.find( 'span.on' ).hasClass( 'checked' );
		$checkbox.prop( 'checked', switchOn ).trigger( 'change' );
	}

	/**
	 * Validate custom domain
	 *
	 * @param {object} $input
	 */
	function validateCustomDomain( $input ) {
		var $error = $input.next( '.as3i-validation-error' );
		var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );
		var pattern = /[^a-zA-Z0-9\.\-]/;

		if ( pattern.test( $input.val() ) ) {
			$error.show();
			$submit.prop( 'disabled', true );
		} else {
			$error.hide();
			$submit.prop( 'disabled', false );
		}
	}

	as3i.tabs = {
		defaultTab: 'media',
		/**
		 * Toggle settings tab
		 *
		 * @param string hash
		 * @param boolean persist_updated_notice
		 */
		toggle: function( hash, persist_updated_notice ) {
			hash = as3i.tabs.sanitizeHash( hash );

			$tabs.hide();
			$activeTab = $( '#tab-' + hash );
			$activeTab.show();
			$( '.nav-tab' ).removeClass( 'nav-tab-active' );
			$( 'a.nav-tab[data-tab="' + hash + '"]' ).addClass( 'nav-tab-active' );
			$( '.as3i-main' ).data( 'tab', hash );
			if ( $activeTab.data( 'prefix' ) ) {
				as3iModal.prefix = $activeTab.data( 'prefix' );
			}
			if ( ! persist_updated_notice ) {
				$( '.as3i-updated' ).removeClass( 'show' );
			}

			if ( 'support' === hash ) {
				as3i.tabs.getDiagnosticInfo();
			}
		},

		/**
		 * Update display of diagnostic info.
		 */
		getDiagnosticInfo: function() {
			var $debugLog = $( '.debug-log-textarea' );

			$debugLog.html( as3i.strings.get_diagnostic_info );

			var data = {
				action: 'as3i-get-diagnostic-info',
				_nonce: as3i.nonces.get_diagnostic_info
			};

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'JSON',
				data: data,
				error: function( jqXHR, textStatus, errorThrown ) {
					$debugLog.html( errorThrown );
				},
				success: function( data, textStatus, jqXHR ) {
					if ( 'undefined' !== typeof data[ 'success' ] ) {
						$debugLog.html( data[ 'diagnostic_info' ] );
					} else {
						$debugLog.html( as3i.strings.get_diagnostic_info_error );
						$debugLog.append( data[ 'error' ] );
					}
				}
			} );
		},

		/**
		 * Sanitize hash to ensure it references a real tab.
		 *
		 * @param string hash
		 *
		 * @return string
		 */
		sanitizeHash: function( hash ) {
			var $newTab = $( '#tab-' + hash );

			if ( 0 === $newTab.length ) {
				hash = as3i.tabs.defaultTab;
			}

			return hash;
		}
	};

	/**
	 * Handle the bucket selection, either inline or in a modal
	 */
	as3i.buckets = {

		/**
		 * Buckets must be at least this many characters
		 */
		validLength: 3,

		/**
		 * Process lock for setting a bucket
		 */
		bucketSelectLock: false,

		/**
		 * Load bucket list
		 *
		 * @param {boolean} [forceUpdate]
		 */
		loadList: function( forceUpdate ) {
			if ( 'undefined' === typeof forceUpdate ) {
				forceUpdate = false;
			}

			var $selectBucketForm = $( '.as3i-bucket-container.' + as3iModal.prefix + ' .as3i-bucket-select' );
			var $selectBucketRegion = $selectBucketForm.find( '.bucket-select-region' );
			var $bucketList = $selectBucketForm.find( '.as3i-bucket-list' );
			var selectedBucket = $( '#' + as3iModal.prefix + '-bucket' ).val();

			if ( false === forceUpdate && $bucketList.find( 'li' ).length > 1 ) {
				$( '.as3i-bucket-list a' ).removeClass( 'selected' );
				$( '.as3i-bucket-list a[data-bucket="' + selectedBucket + '"]' ).addClass( 'selected' );

				this.scrollToSelected();
				return;
			}

			$bucketList.html( '<li class="loading">' + $bucketList.data( 'working' ) + '</li>' );

			// Stop accidental submit while reloading list.
			this.disabledButtons();

			var data = {
				action: as3iModal.prefix + '-get-buckets',
				_nonce: window[ as3iModal.prefix.replace( /-/g, '_' ) ].nonces.get_buckets
			};

			if ( $selectBucketRegion.val() ) {
				data[ 'region' ] = $selectBucketRegion.val();
			}

			var that = this;

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'JSON',
				data: data,
				error: function( jqXHR, textStatus, errorThrown ) {
					$bucketList.html( '' );
					that.showError( as3i.strings.get_buckets_error, errorThrown, 'as3i-bucket-select' );
				},
				success: function( data, textStatus, jqXHR ) {
					$bucketList.html( '' );

					if ( 'undefined' !== typeof data[ 'success' ] ) {
						$( '.as3i-bucket-error' ).hide();

						if ( 0 === data[ 'buckets' ].length ) {
							$bucketList.html( '<li class="loading">' + $bucketList.data( 'nothing-found' ) + '</li>' );
						} else {
							$( data[ 'buckets' ] ).each( function( idx, bucket ) {
								var bucketClass = bucket.Name === selectedBucket ? 'selected' : '';
								$bucketList.append( '<li><a class="' + bucketClass + '" href="#" data-bucket="' + bucket.Name + '"><span class="bucket"><span class="dashicons dashicons-portfolio"></span> ' + bucket.Name + '</span><span class="spinner"></span></span></a></li>' );
							} );

							that.scrollToSelected();
							that.disabledButtons();
						}
					} else {
						that.showError( as3i.strings.get_buckets_error, data[ 'error' ], 'as3i-bucket-select' );
					}
				}
			} );
		},

		/**
		 * Scroll to selected bucket
		 */
		scrollToSelected: function() {
			if ( ! $( '.as3i-bucket-list a.selected' ).length ) {
				return;
			}

			var offset = $( 'ul.as3i-bucket-list li' ).first().position().top + 150;

			$( '.as3i-bucket-list' ).animate( {
				scrollTop: $( 'ul.as3i-bucket-list li a.selected' ).position().top - offset
			} );
		},

		/**
		 * Set the selected bucket in list.
		 *
		 * @param {object} $link
		 */
		setSelected: function( $link ) {
			$( '.as3i-bucket-list a' ).removeClass( 'selected' );
			$link.addClass( 'selected' );
			$( '#' + as3iModal.prefix + '-bucket-select-name' ).val( $link.data( 'bucket' ) );
		},

		/**
		 * Disable bucket buttons
		 */
		disabledButtons: function() {
			var $createBucketForm = $( '.as3i-bucket-container.' + as3iModal.prefix + ' .as3i-bucket-create' );
			var $manualBucketForm = $( '.as3i-bucket-container.' + as3iModal.prefix + ' .as3i-bucket-manual' );
			var $selectBucketForm = $( '.as3i-bucket-container.' + as3iModal.prefix + ' .as3i-bucket-select' );

			if ( 0 === $createBucketForm.length && 0 === $manualBucketForm.length && 0 === $selectBucketForm.length ) {
				return;
			}

			if ( 0 < $createBucketForm.length && this.isValidName( $createBucketForm.find( '.as3i-bucket-name' ).val() ) ) {
				$createBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$createBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}

			if ( 0 < $manualBucketForm.length && this.isValidName( $manualBucketForm.find( '.as3i-bucket-name' ).val() ) ) {
				$manualBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$manualBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}

			if ( 0 < $selectBucketForm.length && 1 === $selectBucketForm.find( '.as3i-bucket-list a.selected' ).length ) {
				$selectBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$selectBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}
		},

		/**
		 * Show bucket error
		 *
		 * @param {string} title
		 * @param {string} error
		 * @param {string} [context]
		 */
		showError: function( title, error, context ) {
			var $activeView = $( '.as3i-bucket-container' ).children( ':visible' );
			var $bucketError = $activeView.find( '.as3i-bucket-error' );

			context = ( 'undefined' === typeof context ) ? null : context;

			if ( context && ! $activeView.hasClass( context ) ) {
				return;
			}

			$bucketError.find( 'span.title' ).html( title + ' &mdash;' );
			$bucketError.find( 'span.message' ).html( error );
			$bucketError.show();

			// Unlock setting the bucket
			this.bucketSelectLock = false;
		},

		/**
		 * Check for a valid bucket name
		 *
		 * Bucket names must be at least 3 and no more than 63 characters long.
		 * They can contain lowercase letters, numbers, periods and hyphens.
		 *
		 * @param {string} bucketName
		 *
		 * @return boolean
		 */
		isValidName: function( bucketName ) {
			if ( bucketName.length < 3 || bucketName.length > 63 ) {
				return false;
			}
			if ( true === bucketNamePattern.test( bucketName ) ) {
				return false;
			}

			return true;
		},

		/**
		 * Update invalid bucket name notice
		 *
		 * @param {string} bucketName
		 */
		updateNameNotice: function( bucketName ) {
			var message = null;

			if ( true === bucketNamePattern.test( bucketName ) ) {
				message = as3i.strings.create_bucket_invalid_chars;
			} else if ( bucketName.length < 3 ) {
				message = as3i.strings.create_bucket_name_short;
			} else if ( bucketName.length > 63 ) {
				message = as3i.strings.create_bucket_name_long;
			}

			if ( message && bucketName.length > 0 ) {
				$( '.as3i-invalid-bucket-name' ).html( message );
			} else {
				$( '.as3i-invalid-bucket-name' ).html( '' );
			}
		}

	};

	/**
	 * Reload the page, and show the persistent updated notice.
	 *
	 * Intended for use on plugin settings page.
	 */
	as3i.reloadUpdated = function() {
		var url = location.pathname + location.search;

		if ( ! location.search.match( /[?&]updated=/ ) ) {
			url += '&updated=1';
		}

		url += location.hash;

		location.assign( url );
	};

	/**
	 * Show the standard "Settings saved." notice if not already visible.
	 */
	as3i.showSettingsSavedNotice = function() {
		if ( 0 < $( '#setting-error-settings_updated:visible' ).length || 0 < $( '#as3i-settings_updated:visible' ).length ) {
			return;
		}
		var settingsUpdatedNotice = '<div id="as3i-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>' + as3i.strings.settings_saved + '</strong></p></div>';
		$( 'h2.nav-tab-wrapper' ).after( settingsUpdatedNotice );
		$( document ).trigger( 'wp-updates-notice-added' ); // Hack to run WP Core's makeNoticesDismissible() function.
	};

	/**
	 * Get the link to the bucket on the AWS Console and update the DOM
	 *
	 * @returns {string}
	 */
	function setBucketLink() {
		var bucket = $( '#' + as3iModal.prefix + '-bucket' ).val();
		var $objectPrefix = $activeTab.find( 'input[name="object-prefix"]' );
		var prefix = $objectPrefix.val();

		if ( '' !== prefix ) {
			prefix = as3i.provider_console_url_prefix_param + encodeURIComponent( prefix );
		}

		var url = as3i.provider_console_url + bucket + prefix;

		$( '#' + as3iModal.prefix + '-view-bucket' ).attr( 'href', url );
	}

	/*
	 * Toggle the lost files notice
	 */
	function toggleLostFilesNotice() {
		if ( $( '#as3i-remove-local-file' ).is( ':checked' ) && $( '#as3i-serve-from-s3' ).is( ':not(:checked)' ) ) {
			$( '#as3i-lost-files-notice' ).show();
		} else {
			$( '#as3i-lost-files-notice' ).hide();
		}
	}

	/*
	 * Toggle the remove local files notice
	 */
	function toggleRemoveLocalNotice() {
		if ( $( '#as3i-remove-local-file' ).is( ':checked' ) ) {
			$( '#as3i-remove-local-notice' ).show();
		} else {
			$( '#as3i-remove-local-notice' ).hide();
		}
	}

	/*
	 * Toggle the seo friendly url notice.
	 */
	function toggleSEOFriendlyURLNotice( seo_friendly ) {
		if ( true !== seo_friendly ) {
			$( '#as3i-seo-friendly-url-notice' ).show();
		} else {
			$( '#as3i-seo-friendly-url-notice' ).hide();
		}
	}

	/**
	 * Generate URL preview
	 */
	function generateUrlPreview() {
		$( '.as3i-url-preview' ).html( 'Generating...' );

		var data = {
			_nonce: as3i.nonces.get_url_preview
		};

		$.each( $( '#tab-' + as3i.tabs.defaultTab + ' .as3i-main-settings form' ).serializeArray(), function( i, o ) {
			var n = o.name,
				v = o.value;
			n = n.replace( '[]', '' );
			data[ n ] = undefined === data[ n ] ? v : $.isArray( data[ n ] ) ? data[ n ].concat( v ) : [ data[ n ], v ];
		} );

		// Overwrite the save action stored in the form
		data[ 'action' ] = 'as3i-get-url-preview';

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			dataType: 'JSON',
			data: data,
			error: function( jqXHR, textStatus, errorThrown ) {
				alert( as3i.strings.get_url_preview_error + errorThrown );
			},
			success: function( data, textStatus, jqXHR ) {
				if ( 'undefined' !== typeof data[ 'success' ] ) {
					$( '.as3i-url-preview' ).html( data[ 'url' ] );
					toggleSEOFriendlyURLNotice( data[ 'seo_friendly' ] );
				} else {
					alert( as3i.strings.get_url_preview_error + data[ 'error' ] );
				}
			}
		} );
	}

	/**
	 * Update the UI with the current active tab set in the URL hash.
	 */
	function renderCurrentTab() {

		// If rendering the default tab, or a bare hash clean the hash.
		if ( '#' + as3i.tabs.defaultTab === location.hash ) {
			location.hash = '';

			return;
		}

		as3i.tabs.toggle( location.hash.replace( '#', '' ), true );

		$( document ).trigger( 'as3i.tabRendered', [ location.hash.replace( '#', '' ) ] );
	}

	$( document ).ready( function() {

		// Tabs
		// --------------------
		renderCurrentTab();

		/**
		 * Set the hashchange callback to update the rendered active tab.
		 */
		window.onhashchange = function( event ) {

			// Strip the # if still on the end of the URL
			if ( 'function' === typeof history.replaceState && '#' === location.href.slice( -1 ) ) {
				history.replaceState( {}, '', location.href.slice( 0, -1 ) );
			}

			renderCurrentTab();
		};

		// Move any compatibility errors below the nav tabs
		var $navTabs = $( '.as3i-main .nav-tab-wrapper' );
		$( '.as3i-compatibility-notice, div.updated, div.error, div.notice' ).not( '.below-h2, .inline' ).insertAfter( $navTabs );

		// Settings
		// --------------------

		// Save the original state of the forms for comparison later
		if ( $tabs.length ) {
			$tabs.each( function( i, tab ) {
				savedSettings[ tab.id ] = serializedForm( tab.id );
			} );
		}

		// Prompt user with dialog if leaving the settings page with unsaved changes
		$( window ).on( 'beforeunload.as3i-settings', function() {
			if ( $.isEmptyObject( savedSettings ) ) {
				return;
			}

			var tab = $activeTab.attr( 'id' );

			if ( serializedForm( tab ) !== savedSettings[ tab ] ) {
				return as3i.strings.save_alert;
			}
		} );

		// Let the save settings submit happen as normal
		$( document ).on( 'submit', '.as3i-main-settings form', function( e ) {

			// Disable unload warning
			$( window ).off( 'beforeunload.as3i-settings' );
		} );

		$( '.as3i-switch' ).on( 'click', function( e ) {
			if ( ! $( this ).hasClass( 'disabled' ) ) {
				setCheckbox( $( this ).attr( 'id' ) );
			}
		} );

		$tabs.on( 'change', '.sub-toggle', function( e ) {
			var setting = $( this ).attr( 'id' );
			$( '.as3i-setting.' + setting ).toggleClass( 'hide' );
		} );

		$( '.as3i-domain' ).on( 'change', 'input[type="radio"]', function( e ) {
			var $selected = $( this ).closest( 'input:radio[name="domain"]:checked' );
			var domain = $selected.val();
			var $cloudfront = $( this ).parents( '.as3i-domain' ).find( '.as3i-setting.cloudfront' );
			var cloudfrontSelected = ( 'cloudfront' === domain );
			$cloudfront.toggleClass( 'hide', ! cloudfrontSelected );
		} );

		$( '.url-preview' ).on( 'change', 'input', function( e ) {
			generateUrlPreview();
		} );

		toggleLostFilesNotice();
		$( '#as3i-serve-from-s3,#as3i-remove-local-file' ).on( 'change', function( e ) {
			toggleLostFilesNotice();
		} );

		toggleRemoveLocalNotice();
		$( '#as3i-remove-local-file' ).on( 'change', function( e ) {
			toggleRemoveLocalNotice();
		} );

		// Don't allow 'enter' key to submit form on text input settings
		$( '.as3i-setting input[type="text"]' ).keypress( function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();

				return false;
			}
		} );

		// Validate custom domain
		$( 'input[name="cloudfront"]' ).on( 'keyup', function( e ) {
			validateCustomDomain( $( this ) );
		} );

		// Re-enable submit button on domain change
		$( 'input[name="domain"]' ).on( 'change', function( e ) {
			var $input = $( this );
			var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );

			if ( 'cloudfront' !== $input.val() ) {
				$submit.prop( 'disabled', false );
			} else {
				validateCustomDomain( $input.next( '.as3i-setting' ).find( 'input[name="cloudfront"]' ) );
			}
		} );

		// Change bucket link when custom path changes
		$( 'input[name="object-prefix"]' ).on( 'change', function( e ) {
			setBucketLink();
		} );

		// Bucket select
		// --------------------

		// Move bucket errors
		$( '#tab-media > .as3i-bucket-error' ).detach().insertAfter( '.as3i-bucket-container h3' );

		// Enable/disable change bucket's save buttons.
		as3i.buckets.disabledButtons();

		// Bucket list refresh handler
		$body.on( 'click', '.bucket-action-refresh', function( e ) {
			e.preventDefault();
			as3i.buckets.loadList( true );
		} );

		// Bucket list refresh on region change handler
		$body.on( 'change', '.bucket-select-region', function( e ) {
			e.preventDefault();
			as3i.buckets.loadList( true );
		} );

		// If select bucket form is available on load, populate its list.
		if ( 0 < $( '.as3i-bucket-container.' + as3iModal.prefix + ' .as3i-bucket-select' ).length ) {
			as3i.buckets.loadList( true );
		}

		// Bucket list click handler
		$body.on( 'click', '.as3i-bucket-list a', function( e ) {
			e.preventDefault();
			as3i.buckets.setSelected( $( this ) );
			as3i.buckets.disabledButtons();
		} );

		// External links click handler
		$( '.as3i-bucket-container' ).on( 'click', 'a.js-link', function( e ) {
			e.preventDefault();
			window.open( $( this ).attr( 'href' ) );

			return false;
		} );

		// Validate bucket name on create
		$body.on( 'input keyup', '.as3i-bucket-create .as3i-bucket-name', function( e ) {
			var bucketName = $( this ).val();
			as3i.buckets.updateNameNotice( bucketName );
			as3i.buckets.disabledButtons();
		} );

		$body.on( 'input keyup', '.as3i-bucket-manual .as3i-bucket-name', function( e ) {
			var bucketName = $( this ).val();
			as3i.buckets.updateNameNotice( bucketName );
			as3i.buckets.disabledButtons();
		} );

		// Don't allow 'enter' key to submit form on text input settings
		$( '.as3i-bucket-container input[type="text"]' ).keypress( function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();

				return false;
			}
		} );
	} );

} )( jQuery, as3iModal );
