<?php
$needs_keys      = $this->get_provider()->needs_access_keys();
$constant_bucket = strtoupper( str_replace( '-', '_', $prefix ) . '_BUCKET' );
$tr_class        = ( isset( $tr_class ) ) ? $tr_class : '';
?>

<tr class="as3i-bucket <?php echo $tr_class; ?>">
	<td><h4><?php _e( 'Bucket:', 'aws-s3-integration' ); ?></h4></td>
	<td>
		<span id="<?php echo $prefix; ?>-active-bucket" class="as3i-active-bucket">
			<?php echo $selected_bucket; // xss ok ?>
		</span>
		<a id="<?php echo $prefix; ?>-view-bucket" target="_blank" class="as3i-view-bucket" href="<?php echo $this->get_provider()->get_console_url( $selected_bucket, $selected_bucket_prefix ); ?>" title="<?php _e( 'View in provider\'s console', 'aws-s3-integration' ); ?>">
			<span class="dashicons dashicons-external"></span>
		</a>
		<?php 
		if ( defined( $constant_bucket ) || false !== $this->get_defined_setting( 'bucket', false ) ) {
			echo '<span class="as3i-defined-in-config">' . __( 'defined in wp-config.php', 'aws-s3-integration' ) . '</span>';
		} 
		elseif ( ! $needs_keys ) { ?>
			<a href="<?php echo $this->get_plugin_page_url( array( 'action' => 'change-bucket' ) ); ?>" id="<?php echo $prefix; ?>-change-bucket" class="as3i-change-bucket"><?php _e( 'Change', 'aws-s3-integration' ); ?></a>	
		<?php } ?>

		<p id="<?php echo $prefix; ?>-active-region" class="as3i-active-region" title="<?php _e( 'The region that the bucket is in.', 'aws-s3-integration' ); ?>">
			<?php echo $this->get_provider()->get_region_name( $selected_region ); // xss ok ?>
		</p>

		<input id="<?php echo $prefix; ?>-bucket" type="hidden" class="no-compare" name="bucket" value="<?php echo esc_attr( $selected_bucket ); ?>">
		<input id="<?php echo $prefix; ?>-region" type="hidden" class="no-compare" name="region" value="<?php echo esc_attr( $selected_region ); ?>">

		<?php
		$region = $this->get_setting( 'region' );
		if ( is_wp_error( $region ) ) {
			$region = '';
		} ?>
		<?php $bucket_select = $this->get_setting( 'manual_bucket' ) ? 'manual' : ''; ?>
		<input id="<?php echo $prefix; ?>-bucket-select" type="hidden" class="no-compare" value="<?php echo esc_attr( $bucket_select ); ?>">
		<?php
		if ( isset( $after_bucket_content ) ) {
			echo $after_bucket_content;
		}

		if ( ! defined( $constant_bucket ) && ! $this->get_defined_setting( 'bucket', false ) && $needs_keys ) {
			$needs_keys_notice = array(
				'message' => sprintf( __( '<strong>Bucket Select Disabled</strong> &mdash; <a href="%s">Define your access keys</a> to configure the bucket', 'aws-s3-integration' ), '#settings' ),
				'id'      => 'as3i-bucket-select-needs-keys',
				'inline'  => true,
				'type'    => 'notice-warning',
			);
			$this->render_view( 'notice', $needs_keys_notice );
		}

		$lock_bucket_args = array(
			'message' => __( '<strong>Provider &amp; Bucket Select Disabled</strong> &mdash; Provider and bucket selection has been disabled while files are copied between buckets.', 'aws-s3-integration' ),
			'id'      => 'as3i-bucket-select-locked',
			'inline'  => true,
			'type'    => 'notice-warning',
			'style'   => 'display: none',
		);
		$this->render_view( 'notice', $lock_bucket_args ); ?>
	</td>
</tr>
