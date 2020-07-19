<div class="as3cf-sidebar">

	<a class="as3cf-banner" href="<?php echo $this->dbrains_url( '/wp-offload-media/', array(
		'utm_campaign' => 'WP+Offload+S3',
		'utm_content'  => 'sidebar',
	) ); ?>">
	</a>

	<div class="as3cf-upgrade-details">
		<h1>Upgrade</h1>

		<ul>
			<li><?php echo wptexturize( __( 'Offload existing Media Library items', 'aws-s3-integration' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Manage offloaded files in WordPress', 'aws-s3-integration' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Assets addon - Serve your CSS & JS from CloudFront or another CDN', 'aws-s3-integration' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'WooCommerce integration', 'aws-s3-integration' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Easy Digital Downloads integration', 'aws-s3-integration' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'PriorityExpert™ email support', 'aws-s3-integration' ) ); // xss ok ?></li>
		</ul>

		<p>
			<a href="<?php echo $this->dbrains_url( '/wp-offload-media/', array(
				'utm_campaign' => 'WP+Offload+S3',
				'utm_content'  => 'sidebar',
			) ); ?>"><?php echo __( 'Visit deliciousbrains.com &rarr;', 'aws-s3-integration' ); ?></a>
		</p>

	</div>

	<form method="post" action="<?php echo Amazon_S3_And_CloudFront::DBRAINS_URL ?>/email-subscribe/" target="_blank" class="subscribe block">
		<?php $user = wp_get_current_user(); ?>

		<h2><?php _e( 'Get 20% Off!', 'aws-s3-integration' ); ?></h2>

		<p class="intro">
			<?php echo wptexturize( __( 'Submit your name and email and we’ll send you a coupon for 20% off your upgrade.', 'aws-s3-integration' ) ); // xss ok ?>
		</p>

		<div class="field">
			<input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" placeholder="<?php _e( 'Your Email', 'aws-s3-integration' ); ?>"/>
		</div>

		<div class="field">
			<input type="text" name="first_name" value="<?php echo esc_attr( trim( $user->first_name ) ); ?>" placeholder="<?php _e( 'First Name', 'aws-s3-integration' ); ?>"/>
		</div>

		<div class="field">
			<input type="text" name="last_name" value="<?php echo esc_attr( trim( $user->last_name ) ); ?>" placeholder="<?php _e( 'Last Name', 'aws-s3-integration' ); ?>"/>
		</div>

		<input type="hidden" name="campaigns[]" value="5" />
		<input type="hidden" name="source" value="1" />

		<div class="field submit-button">
			<input type="submit" class="button" value="<?php _e( 'Send me the coupon', 'aws-s3-integration' ); ?>"/>
		</div>

		<p class="promise">
			<?php _e( 'We promise we will not use your email for anything else and you can unsubscribe with 1-click anytime.', 'aws-s3-integration' ); ?>
		</p>
	</form>

	<div class="block credits">
		<h4>Created &amp; maintained by</h4>
		<ul>
			<li>
				<a href="<?php echo $this->dbrains_url( '', array(
					'utm_campaign' => 'WP+Offload+S3',
					'utm_content'  => 'sidebar',
				) ); ?>">
					<img src="//www.gravatar.com/avatar/e62fc2e9c8d9fc6edd4fea5339036a91?size=64" alt="" width="32" height="32">
					<span>Delicious Brains Inc.</span>
				</a>
			</li>
		</ul>
	</div>
</div>
