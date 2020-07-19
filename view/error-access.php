<div class="error inline as3i-can-write-error as3i-error" style="<?php echo ( $can_write ) ? 'display: none;' : ''; // xss ok ?>">
	<p>
		<strong>
			<?php _e( 'Access Denied to Bucket', 'aws-s3-integration' ); ?>
		</strong>&mdash;
		<?php echo $this->get_access_denied_notice_message(); ?>
	</p>
</div>