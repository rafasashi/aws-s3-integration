<?php
$tr_class = ( isset( $tr_class ) ) ? $tr_class : ''; ?>
<tr class="<?php echo $tr_class; ?>">
	<td>
		<h4><?php _e( 'Domain:', 'aws-s3-integration' ) ?></h4>
	</td>
	<td>
		<?php
		$domain             = $this->get_setting( 'domain' );
		$subdomain_disabled = '';
		$subdomain_class    = '';
		if ( $this->get_setting( 'force-https' ) ) {
			if ( 'subdomain' == $domain ) {
				$domain = 'path';
			}
			$subdomain_disabled = 'disabled="disabled"';
			$subdomain_class    = 'disabled';
		}

		if ( true === $disabled ) {
			$subdomain_disabled = 'disabled="disabled"';
			$subdomain_class    = 'disabled';
		}

		echo $setting_msg;
		?>
		<div class="as3i-domain as3i-radio-group">
			<label class="subdomain-wrap <?php echo $subdomain_class; // xss ok?>">
				<input type="radio" name="domain" value="subdomain" <?php checked( $domain, 'subdomain' ); ?> <?php echo $subdomain_disabled; // xss ok ?>>
				<?php _e( 'Bucket name as subdomain', 'aws-s3-integration' ); ?>
				<p>http://bucket-name.s3.amazon.com/&hellip;</p>
			</label>
			<label>
				<input type="radio" name="domain" value="path" <?php checked( $domain, 'path' ); ?> <?php echo $disabled_attr; ?>>
				<?php _e( 'Bucket name in path', 'aws-s3-integration' ); ?>
				<p>http://s3.amazon.com/bucket-name/&hellip;</p>
			</label>
			<label>
				<input type="radio" name="domain" value="virtual-host" <?php checked( $domain, 'virtual-host' ); ?> <?php echo $disabled_attr; ?>>
				<?php _e( 'Bucket name as domain', 'aws-s3-integration' ); ?>
				<p>http://bucket-name/&hellip;</p>
			</label>
			<label>
				<input id="cloudfront" type="radio" name="domain" value="cloudfront" <?php checked( $domain, 'cloudfront' ); ?> <?php echo $disabled_attr; ?>>
				<?php _e( 'CloudFront or custom domain', 'aws-s3-integration' ); ?>
				<p class="as3i-setting cloudfront <?php echo ( 'cloudfront' == $domain ) ? '' : 'hide'; // xss ok ?>">
					<input type="text" name="cloudfront" value="<?php echo esc_attr( $this->get_setting( 'cloudfront' ) ); ?>" size="30" <?php echo $disabled_attr; ?> />
					<span class="as3i-validation-error" style="display: none;">
						<?php _e( 'Invalid character. Letters, numbers, periods and hyphens are allowed.', 'aws-s3-integration' ); ?>
					</span>
				</p>
			</label>
		</div>
	</td>
</tr>