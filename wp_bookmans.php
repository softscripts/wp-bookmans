<?php /*  Plugin Name: WP Bookmans  Plugin URI: https://github.com/softscripts/wp-bookmans  Description: Import Events from xml to wp events manager plugin.  Version: 1.2  Author: Softscripts  Author URI: http://www.softscripts.net */global $wpdb;$siteurl = get_bloginfo('url');define('WB_PLUGIN_URL', WP_PLUGIN_URL.'/wp_bookmans');define("WB_EVENTS_TABLE", $wpdb->prefix . "bookmans_events");define("WB_TICKETS_TABLE", $wpdb->prefix . "bookmans_tickets");define("WB_XML_TABLE", $wpdb->prefix . "bookmans_xml");/* Define WP EVENT MANGER TABLES */define("WB_EM_EVENTS", $wpdb->prefix . "em_events");define("WB_EM_LOCATIONS", $wpdb->prefix . "em_locations");define("WB_EM_TICKETS", $wpdb->prefix . "em_tickets");define("WB_POSTS", $wpdb->prefix . "posts");/* Load all functions */require_once ( 'admin/index.php' );add_action('admin_menu','wb_backend_menu');function wb_backend_menu() {	add_menu_page('WP Bookmans','WP Bookmans','manage_options','wb_import','wb_import', '', 22);	add_submenu_page('wb_import','Import','Import','manage_options','wb_import','wb_import');	add_submenu_page('wb_import','Settings','Settings','manage_options','wb_settings','wb_settings');}// this hook will cause our creation function to run when the plugin is activatedregister_activation_hook( __FILE__, 'wb_plugin_install' );function wb_plugin_install() {	global $wpdb; // do NOT forget this global	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );		if ( is_plugin_active('events-manager/events-manager.php') ) {		}		 else {			deactivate_plugins( 'wp_bookmans/wp_bookmans.php', false );		wp_die( "<strong>WP Bookmans Plugin</strong> requires <strong>Event Manager Plugin</strong> and has been deactivated! Please install/activate <strong>Event Manager Plugin</strong> and try again.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>.");		} 		if($wpdb->get_var("show tables like '". WB_EVENTS_TABLE) != WB_EVENTS_TABLE)  {			$wpdb->query("CREATE TABLE IF NOT EXISTS `". WB_EVENTS_TABLE . "` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `event_name` VARCHAR( 250 ) NOT NULL, `event_url` VARCHAR( 250 ) NOT NULL, `event_date` VARCHAR( 250 ) NOT NULL, `event_location_name` VARCHAR( 250 ) NOT NULL, `event_location_address` VARCHAR( 250 ) NOT NULL, `event_location_town` VARCHAR( 250 ) NOT NULL, `event_location_state` VARCHAR( 250 ) NOT NULL, `event_location_postcode` VARCHAR( 250 ) NOT NULL, `event_location_country` VARCHAR( 250 ) NOT NULL, `event_location_phone` VARCHAR( 250 ) NOT NULL, `event_location_fax` VARCHAR( 250 ) NOT NULL, `event_location_email` VARCHAR( 250 ) NOT NULL, `event_location_url` VARCHAR( 250 ) NOT NULL, `event_location_timezone` VARCHAR( 250 ) NOT NULL, `event_spaces` INT NOT NULL, `event_desc` LONGTEXT NOT NULL, `event_location_latitude` VARCHAR( 250 ) NOT NULL, `event_location_longitude` VARCHAR( 250 ) NOT NULL, `em_event_id` INT NOT NULL, `import_status` INT NOT NULL, `ticket_import_status` INT NOT NULL)");		}		if($wpdb->get_var("show tables like '". WB_TICKETS_TABLE) != WB_TICKETS_TABLE)  {			$wpdb->query("CREATE TABLE IF NOT EXISTS `". WB_TICKETS_TABLE . "` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `event_id` INT NOT NULL, `ticket_name` VARCHAR( 250 ) NOT NULL, `ticket_desc` LONGTEXT NOT NULL, `ticket_start` VARCHAR( 250 ) NOT NULL, `ticket_end` VARCHAR( 250 ) NOT NULL, `ticket_spaces` INT NOT NULL)");		}		if($wpdb->get_var("show tables like '". WB_XML_TABLE) != WB_XML_TABLE)  {			$wpdb->query("CREATE TABLE IF NOT EXISTS `". WB_XML_TABLE . "` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `xml_file` VARCHAR( 300 ) NOT NULL )");		}	update_option('disable_wb_admin_message',1);}add_action( 'admin_init', 'wb_check_em_plugin' );function wb_check_em_plugin() {	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );		if ( is_plugin_active('events-manager/events-manager.php') ) {		}		 else {			deactivate_plugins( 'wp_bookmans/wp_bookmans.php', false );			add_action ( 'admin_notices', 'wb_admin_notices', 100 );		}}function wb_admin_notices() {    echo "<div class='error'><p>WP Bookmans Plugin has been deactivated since it requires Event Manager Plugin. Please activate Event Manager Plugin and try again.</p></div>";}function wb_admin_messages() {	//If we're editing the events page show hello to new user	$dismiss_link_joiner = ( count($_GET) > 0 ) ? '&amp;':'?';		if( current_user_can('activate_plugins') ){		//New User Intro		if (isset ( $_GET ['disable_wb_admin_message'] ) && $_GET ['disable_wb_admin_message'] == 'true'){			// Disable Hello to new user if requested			update_option('disable_wb_admin_message',0);		}elseif ( get_option ( 'disable_wb_admin_message' ) ) {						$advice = sprintf( __("<p>WP Bookmans is ready to go! Check out the <a href='%s'>Settings Page</a>. <a href='%s' title='Don't show this advice again'>Dismiss</a></p>", 'dbwb'), wb_get_url('settings'),  $_SERVER['REQUEST_URI'].$dismiss_link_joiner.'disable_wb_admin_message=true');			?>			<div id="message" class="updated">				<?php echo $advice; ?>			</div>			<?php		}	}}add_action ( 'admin_notices', 'wb_admin_messages', 100 );// Add settings link on plugin pagefunction wb_settings_link($links) {   $settings_link = '<a href="admin.php?page=wb_settings">Settings</a>';   array_unshift($links, $settings_link);   return $links; } $plugin = plugin_basename(__FILE__); add_filter("plugin_action_links_$plugin", 'wb_settings_link' );// Add Scripts and css in backend.add_action( 'admin_enqueue_scripts', 'wb_enqueue_scripts' );function wb_enqueue_scripts( $hook_suffix ) {	  // first check that $hook_suffix is appropriate for your admin page	  wp_enqueue_script( 'wb-data-tables', WB_PLUGIN_URL . '/admin/js/jquery.dataTables.min.js', array( 'jquery' ), false, true );	  wp_enqueue_script( 'wb-settings-scripts', WB_PLUGIN_URL . '/admin/js/admin-scripts.js', array( 'jquery' ), false, true );	  wp_enqueue_style( 'wb-settings-styles', WB_PLUGIN_URL . '/admin/css/admin.css', array(), '', 'all' );		wp_enqueue_media();}// Allow permit for xml upload.add_filter('upload_mimes', 'wb_upload_xml');function wb_upload_xml($mimes) {    $mimes = array_merge($mimes, array('xml' => 'application/xml'));    return $mimes;}?>