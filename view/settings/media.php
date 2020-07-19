<?php

/* @var \AWS_s3_Integration|\AWS_s3_Integration_Pro $this */
$prefix                 = $this->get_plugin_prefix_slug();
$selected_provider      = $this->get_setting( 'provider', static::$default_provider );
$selected_region        = $this->get_setting( 'region' );
$selected_bucket        = $this->get_setting( 'bucket' );
$selected_bucket_prefix = $this->get_object_prefix();

if ( $this->get_provider()->needs_access_keys() ) {
	$storage_classes = ' as3i-needs-access-keys';
} else {
	$storage_classes = ' as3i-has-access-keys';
}

if ( $selected_bucket ) {
	$storage_classes .= ' as3i-has-bucket';
} else {
	$storage_classes .= ' as3i-needs-bucket';
}

if ( ! empty( $_GET['action'] ) && 'change-provider' === $_GET['action'] ) {
	$storage_classes .= ' as3i-change-provider';
}

if ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) {
	$storage_classes .= ' as3i-change-bucket';
}

$storage_classes = apply_filters( 'as3i_media_tab_storage_classes', $storage_classes );
?>
<div id="tab-media" data-prefix="as3i" class="as3i-tab as3i-content<?php echo $storage_classes; // xss ok ?>">
	<div class="error inline as3i-bucket-error as3i-error" style="display: none;">
		<p>
			<span class="title"></span>
			<span class="message"></span>
		</p>
	</div>

	<?php
	do_action( 'as3i_pre_tab_render', 'media' );
	$can_write = $this->render_bucket_permission_errors();
	?>

	<div class="as3i-main-settings">
		<form method="post">
			<input type="hidden" name="action" value="save"/>
			<input type="hidden" name="plugin" value="<?php echo $this->get_plugin_slug(); ?>"/>
			<?php
			wp_nonce_field( $this->get_settings_nonce_key() );
			do_action( 'as3i_form_hidden_fields' );

			$this->render_view( 'bucket-select', array( 'prefix' => $prefix, 'selected_provider' => $selected_provider, 'selected_region' => $selected_region, 'selected_bucket' => $selected_bucket ) );

			do_action( 'as3i_pre_media_settings' );
			?>

			<table class="form-table as3i-media-settings">

				<?php

				$this->render_view( 'bucket-setting',
					array(
						'prefix'                 => $prefix,
						'selected_provider'      => $selected_provider,
						'selected_region'        => $selected_region,
						'selected_bucket'        => $selected_bucket,
						'selected_bucket_prefix' => $selected_bucket_prefix,
						'tr_class'               => "{$prefix}-bucket-setting",
					)
				); ?>

				<?php $args = $this->get_setting_args( 'copy-to-s3' ); ?>
				<tr class="<?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Copy Files to Bucket', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'When a file is uploaded to the Media Library, copy it to the bucket.', 'aws-s3-integration' ); ?>
						</p>

					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'enable-object-prefix' ); ?>
				<tr class="url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $args['class'] = 'sub-toggle'; ?>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Path', 'aws-s3-integration' ) ?></h4>
						<p class="object-prefix-desc">
							<?php _e( 'By default the path is the same as your local WordPress files.', 'aws-s3-integration' ); ?>
						</p>
						<p class="as3i-setting <?php echo $prefix; ?>-enable-object-prefix <?php echo ( $this->get_setting( 'enable-object-prefix' ) ) ? '' : 'hide'; // xss ok
						?>">
							<?php $args = $this->get_setting_args( 'object-prefix' ); ?>
							<input type="text" name="object-prefix" value="<?php echo esc_attr( $this->get_setting( 'object-prefix' ) ); ?>" size="30" placeholder="<?php echo $this->get_default_object_prefix(); ?>" <?php echo $args['disabled_attr']; ?> />
						</p>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'use-yearmonth-folders' ); ?>
				<tr class="url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Year/Month', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'Add the Year/Month to the end of the path above just like WordPress does by default.', 'aws-s3-integration' ); ?>
						</p>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'object-versioning' ); ?>
				<tr class="advanced-options url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Object Versioning', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'Append a timestamp to the file\'s bucket path. Recommended when using a CDN so you don\'t have to worry about cache invalidation.', 'aws-s3-integration' ); ?>
						</p>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'serve-from-s3' ); ?>
				<tr class="<?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Rewrite Media URLs', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'For Media Library files that have been copied to your bucket, rewrite the URLs so that they are served from the bucket or CDN instead of your server.', 'aws-s3-integration' ); ?>
						</p>

					</td>
				</tr>

				<?php $this->render_view( 'domain-setting' ); ?>

				<?php $args = $this->get_setting_args( 'force-https' ); ?>
				<tr class="url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Force HTTPS', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'By default we use HTTPS when the request is HTTPS and regular HTTP when the request is HTTP, but you may want to force the use of HTTPS always, regardless of the request.', 'aws-s3-integration' ); ?>
						</p>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'remove-local-file' ); ?>
				<tr class="advanced-options <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Remove Files From Server', 'aws-s3-integration' ) ?></h4>
						<p>
							<?php _e( 'Once a file has been copied to the bucket, remove it from the local server.', 'aws-s3-integration' ); ?>
						</p>
					</td>
				</tr>

				<!-- Save button for main settings -->
				<tr>
					<td colspan="2">
						<button type="submit" class="button button-primary" <?php echo $this->maybe_disable_save_button(); ?>><?php _e( 'Save Changes', 'aws-s3-integration' ); ?></button>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<?php
	if ( $this->get_provider()->needs_access_keys() ) {
		?>
		<p class="as3i-need-help">
			<span class="dashicons dashicons-info"></span>
			<?php printf( __( 'Need help getting your Access Keys? <a href="%s">Check out the Quick Start Guide &rarr;</a>', 'aws-s3-integration' ), $this->rew_url( '/wp-offload-media/doc/quick-start-guide/', array(
				'utm_campaign' => 'support+docs',
			) ) ) ?>
		</p>
		<?php
	}
	?>
</div>
