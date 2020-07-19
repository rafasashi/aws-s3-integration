<?php
$prefix = $this->get_plugin_prefix_slug();
?>
<p class="as3i-setting <?php echo $prefix; ?>-domain <?php echo $prefix; ?>-cloudfront <?php echo ( 'cloudfront' == $domain ) ? '' : 'hide'; // xss ok ?>">
	<input type="text" name="cloudfront" value="<?php echo esc_attr( $this->get_setting( 'cloudfront' ) ); ?>" size="30" <?php echo $disabled_attr; ?> />
	<span class="as3i-validation-error" style="display: none;">
		<?php _e( 'Invalid character. Letters, numbers, periods and hyphens are allowed.', 'aws-s3-integration' ); ?>
	</span>
</p>
