<?php
$tr_class = ( isset( $tr_class ) ) ? $tr_class : '';

$change_args = array( 'action' => 'change-provider' );

if ( ! empty( $_GET['orig_provider'] ) ) {
	$change_args['orig_provider'] = $_GET['orig_provider'];
}
?>

<tr class="as3cf-provider <?php echo $tr_class; ?>">
	<td><h4><?php _e( 'Provider:', 'aws-s3-integration' ); ?></h4></td>
	<td>
		<span id="<?php echo $prefix; ?>-active-provider" class="as3cf-active-provider">
			<?php echo $this->get_provider()->get_provider_service_name(); // xss ok ?>
		</span>
		<a href="<?php echo $this->get_plugin_page_url( $change_args ); ?>" id="<?php echo $prefix; ?>-change-provider" class="as3cf-change-provider"><?php _e( 'Change', 'aws-s3-integration' ); ?></a>
	</td>
</tr>