<?php
/* @var \AWS_s3_Integration|\AWS_s3_Integration_Pro $this */
$provider         = $this->get_provider();
$provider_regions = $provider->get_regions();
$region_required  = $provider->region_required();

$bucket_mode = empty( $_GET['bucket_mode'] ) ? 'manual' : $_GET['bucket_mode'];
$bucket_mode = in_array( $bucket_mode, array( 'manual', 'select', 'create' ) ) ? $bucket_mode : 'manual';

$mode_args = array(
	'action' => 'change-bucket',
);

if ( ! empty( $_GET['prev_action'] ) ) {
	$mode_args['prev_action'] = $_GET['prev_action'];
}

if ( ! empty( $_GET['orig_provider'] ) ) {
	$mode_args['orig_provider'] = $_GET['orig_provider'];
}

$manual_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'manual' ) ) );
$select_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'select' ) ) );
$create_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'create' ) ) );
?>

<div style="clear:both;" class="as3i-bucket-container <?php echo $prefix; ?>">
	
	<?php
	if ( ! $this->get_setting( 'bucket' ) || ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) || ! empty( $_GET['prev_action'] ) ) {
		$back_args = $this->get_setting( 'bucket' ) ? array() : array( 'action' => 'change-provider' );
		if ( empty( $back_args['action'] ) && ! empty( $_GET['prev_action'] ) ) {
			$back_args['action'] = $_GET['prev_action'];

			if ( ! empty( $_GET['orig_provider'] ) ) {
				$back_args['orig_provider'] = $_GET['orig_provider'];
			}
		}
		echo '<a href="' . $this->get_plugin_page_url( $back_args ) . '">' . __( '&laquo;&nbsp;Back', 'aws-s3-integration' ) . '</a>';
	}

	if ( 'manual' === $bucket_mode ) {
		?>
		<div class="as3i-bucket-manual">
			
			<table class="form-table">

				<?php if ( defined( 'AWS_S3_REGION' ) || true === $region_required ) { ?>
					<tr>
						<td>
							<?php _e( 'Region:', 'aws-s3-integration' ); ?>
						</td>
						<td>
							<?php
							if ( ! defined( 'AWS_S3_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) { ?>
								<select id="<?php echo $prefix; ?>-bucket-manual-region" class="bucket-manual-region" name="region_name">
									<?php foreach ( $provider_regions as $value => $label ) {
										$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
										?>
										<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
									<?php } ?>
								</select>
							<?php } else {
								$region      = defined( 'AWS_S3_REGION' ) ? AWS_S3_REGION : $this->get_defined_setting( 'region' );
								$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
								printf( __( '%s (defined in wp-config.php)', 'aws-s3-integration' ), $region_name );
							} ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td>
						<?php _e( 'Bucket:', 'aws-s3-integration' ); ?>
					</td>
					<td>
						<input type="text" id="<?php echo $prefix; ?>-bucket-manual-name" class="as3i-bucket-name" name="bucket_name" placeholder="<?php _e( 'Existing bucket name', 'aws-s3-integration' ); ?>" value="<?php echo $selected_bucket; ?>">
						<p class="as3i-invalid-bucket-name"></p>
					</td>
				</tr>
			</table>
			<p class="bucket-actions actions manual">
				<button id="<?php echo $prefix; ?>-bucket-manual-save" type="submit" class="bucket-action-save button button-primary"><?php _e( 'Save Bucket Setting', 'aws-s3-integration' ); ?></button>
				<span><a href="<?php echo $create_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'aws-s3-integration' ); ?></a></span>
			</p>
		</div>
	<?php } elseif ( 'select' === $bucket_mode ) { ?>
		<div class="as3i-bucket-select">
			<h3><?php _e( 'Select bucket', 'aws-s3-integration' ); ?></h3>
			<?php if ( defined( 'AWS_S3_REGION' ) || false !== $this->get_defined_setting( 'region', false ) || true === $region_required ) { ?>
				<table class="form-table">
					<tr>
						<td>
							<?php _e( 'Region:', 'aws-s3-integration' ); ?>
						</td>
						<td>
							<?php
							if ( ! defined( 'AWS_S3_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) { ?>
								<select id="<?php echo $prefix; ?>-bucket-select-region" class="bucket-select-region" name="region_name">
									<?php foreach ( $provider_regions as $value => $label ) {
										$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
										?>
										<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
									<?php } ?>
								</select>
							<?php } else {
								$region      = defined( 'AWS_S3_REGION' ) ? AWS_S3_REGION : $this->get_defined_setting( 'region' );
								$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
								printf( __( '%s (defined in wp-config.php)', 'aws-s3-integration' ), $region_name );
							} ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php _e( 'Bucket:', 'aws-s3-integration' ); ?>
						</td>
						<td>
							<ul class="as3i-bucket-list" data-working="<?php _e( 'Loading...', 'aws-s3-integration' ); ?>" data-nothing-found="<?php _e( 'Nothing found', 'aws-s3-integration' ); ?>"></ul>
						</td>
					</tr>
				</table>
			<?php } else { ?>
				<ul class="as3i-bucket-list" data-working="<?php _e( 'Loading...', 'aws-s3-integration' ); ?>" data-nothing-found="<?php _e( 'Nothing found', 'aws-s3-integration' ); ?>"></ul>
			<?php } ?>
			<input id="<?php echo $prefix; ?>-bucket-select-name" type="hidden" class="no-compare" name="bucket_name" value="<?php echo esc_attr( $selected_bucket ); ?>">
			<p class="bucket-actions actions select">
				<button id="<?php echo $prefix; ?>-bucket-select-save" type="submit" class="bucket-action-save button button-primary"><?php _e( 'Save Selected Bucket', 'aws-s3-integration' ); ?></button>
				<span><a href="<?php echo $manual_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-manual" class="bucket-action-manual"><?php _e( 'Enter bucket name', 'aws-s3-integration' ); ?></a></span>
				<span><a href="<?php echo $create_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'aws-s3-integration' ); ?></a></span>
				<span><a href="#" class="bucket-action-refresh"><?php _e( 'Refresh', 'aws-s3-integration' ); ?></a></span>
			</p>
		</div>
	<?php } elseif ( 'create' === $bucket_mode ) { ?>
		<div class="as3i-bucket-create">
			<h3><?php _e( 'Create new bucket', 'aws-s3-integration' ); ?></h3>
			<table class="form-table">
				<tr>
					<td>
						<?php _e( 'Region:', 'aws-s3-integration' ); ?>
					</td>
					<td>
						<?php
						if ( ! defined( 'AWS_S3_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) {
							$selected_region = $provider->is_region_valid( $selected_region ) ? $selected_region : $provider->get_default_region();
							?>
							<select id="<?php echo $prefix; ?>-bucket-create-region" class="bucket-create-region" name="region_name">
								<?php foreach ( $provider_regions as $value => $label ) {
									$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
									?>
									<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
								<?php } ?>
							</select>
						<?php } else {
							$region      = defined( 'AWS_S3_REGION' ) ? AWS_S3_REGION : $this->get_defined_setting( 'region' );
							$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
							printf( __( '%s (defined in wp-config.php)', 'aws-s3-integration' ), $region_name );
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php _e( 'Bucket:', 'aws-s3-integration' ); ?>
					</td>
					<td>
						<input type="text" id="<?php echo $prefix; ?>-create-bucket-name" class="as3i-bucket-name" name="bucket_name" placeholder="<?php _e( 'New bucket name', 'aws-s3-integration' ); ?>">
						<p class="as3i-invalid-bucket-name"></p>
					</td>
				</tr>
			</table>
			<p class="bucket-actions actions create">
				<button id="<?php echo $prefix; ?>-bucket-create" type="submit" class="button button-primary"><?php _e( 'Create New Bucket', 'aws-s3-integration' ); ?></button>
				<span><a href="<?php echo $select_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-browse" class="bucket-action-browse"><?php _e( 'Browse existing buckets', 'aws-s3-integration' ); ?></a></span>
				<span><a href="<?php echo $manual_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-manual" class="bucket-action-manual"><?php _e( 'Enter bucket name', 'aws-s3-integration' ); ?></a></span>
			</p>
		</div>
	<?php } ?>
</div>