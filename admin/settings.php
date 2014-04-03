<?php
add_action( 'admin_init', 'wb_settings_init' );

function wb_settings_init(){
	register_setting( 'wb_settings', 'wb_settings_options', 'wb_settings_validate' );
}

function wb_settings() {
global $wpdb;
$link = wb_get_url('settings');

if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;
 ?>
<div class="wrap wb_wrap">
		﻿<div class="add_new"><div id="icon-edit-pages" class="icon32"><br></div><h2>WP Bookmans Settings</h2></div>
	<?php if ( false != $_REQUEST['settings-updated'] ) :  ?>
		<div class="updated"><p><strong><?php _e( 'Settings saved'); ?></strong></p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
		<?php settings_fields( 'wb_settings' ); 
					$settings = get_option( 'wb_settings_options' );
					wb_store_xml_file($settings['xml']); ?>
			<table id="general-tab" class="form-table wb_setting_table">
				<tr valign="top"><th scope="row"><?php _e( 'Default Import XML Link'); ?></th>
					<td>
						<div class="relative"><input id="wb_settings_options_xml" class="wb_import_xml regular-text large" type="text" name="wb_settings_options[xml]" value="<?php esc_attr_e( $settings['xml'] ); ?>" /><input id="button_wb_settings_options_xml" class="meta_upload" name="button_wb_settings_options[xml]" type="button" value="Upload XML" style="width: auto;" />
						<label class="description" for="wb_settings_options_xml"><?php _e( 'Insert XML URL'); ?></label>
						</div>				
						<div class="aligncenter spaceMargin">OR</div>
						<div class="spaceMargin"><a href="#previous_xml" class="button toggle-show">Choose from past XML files</a></div>
						<div class="toggle-hide" id="previous_xml">
							<select name="previous_xml" class="long-select">
								<option value="">Please select</option>
								<?php $xml_files = wb_get_xml_files();
								foreach($xml_files as $xml_file) { ?>
								<option value="<?php echo $xml_file->xml_file; ?>"><?php echo $xml_file->xml_file; ?></option>
								<?php } ?>
							</select>
						</div>
					</td>
				</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Settings'); ?>" />
		</p>
	</form>
</div>
<?php } 

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function wb_settings_validate( $input ) {

	// Our checkbox value is either 0 or 1
	if ( ! isset( $input['option1'] ) )

	// Say our text option must be safe text with no HTML tags
	$input['xml'] = wp_filter_nohtml_kses( $input['xml'] );

	// Say our textarea option must be safe text with the allowed tags for posts
	//$input['textarea'] = wp_filter_post_kses( $input['textarea'] );

	return $input;
}
?>
