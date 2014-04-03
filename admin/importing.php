<?php function wb_import() {
global $wpdb;
$url = wb_get_url('import'); //Current page url

/******************
 Common Requests
******************/
$step = $_REQUEST['step'];
if(!$step) $step = 1; //Reset to Step1

$status = $_REQUEST['status'];
if(!$status && $step==1) $status = 'info'; //Reset to Status for Step1

$msg = $_REQUEST['msg'];
if(!$msg && $step==1) $msg = 1; //Reset to Msg for Step1


/**************
 Step1 Setup
***************/
if(isset($_REQUEST['import_step'])) {
	if(!empty($_REQUEST['wb_import_xml'])) {
		wb_import_xml($_REQUEST['wb_import_xml']);
	}
}

/**************
 Step2 Setup
***************/
if(isset($_REQUEST['update_step'])) {
	if(!empty($_REQUEST['update_step'])) {
		wb_update_events_addresses();
	}
}

/**************
 Step3 Setup
***************/
if(isset($_REQUEST['commit_step'])) {
	if(!empty($_REQUEST['commit_step'])) {
		$eventsList = $_REQUEST['commit_fields'];
		if(!empty($eventsList)) {
			$eventsArray = explode(",",$eventsList);
			wb_start_commit($eventsArray);
		}
		else {
			echo '<script>location.href="'.wb_go_to_step(3).'&status=alert&msg=3";</script>'; //Redirect to Step3
		}
	}
}

/**************
 Step4 Setup
***************/
if(isset($_REQUEST['publish_step'])) {
	if(!empty($_REQUEST['publish_step'])) {
		wb_publish_events();
	}
}

/**************
 Step5 Setup
***************/
if(isset($_REQUEST['ticket_publish_step'])) {
	if(!empty($_REQUEST['ticket_publish_step'])) {
		wb_publish_event_tickets();
	}
}


?>
<div class="wrap wb_wrap">
		﻿<div class="add_new"><div id="icon-edit-pages" class="icon32"><br></div><h2>WP Bookmans - Import XML</h2></div>

		<div class="wb_tabs">
		<?php switch($step) {
					case 1: ?>
			<div class="wb_tab">
				<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
				<div class="wb_tab_content">
					<?php wb_status_info($step,$status,$msg); ?>
					<form method="post" id="import_form_step" class="wb_steps_form" name="import_form_step" action="">
					<?php $settings = get_option( 'wb_settings_options' ); ?>
						<div class="relative">
							<label class="description" for="wb_import_xml"><?php _e( 'Insert XML URL'); ?></label> <input id="wb_import_xml" class="wb_import_xml regular-text large data-required" type="text" name="wb_import_xml" value="<?php esc_attr_e( $settings['xml'] ); ?>" /><input id="button_wb_import_xml" class="meta_upload button-primary" name="button_wb_import_xml" type="button" value="Upload XML" style="width: auto;" />
						</div>				
						<div class="aligncenter spaceMargin">OR</div>
						<div class="" id="previous_xml">
						<label class="description" for="previous_xml_list">Choose from past XML files</label>
							<select name="previous_xml" id="previous_xml_list" class="long-select">
								<option value="">Please select</option>
								<?php $xml_files = wb_get_xml_files();
								foreach($xml_files as $xml_file) { ?>
								<option value="<?php echo $xml_file->xml_file; ?>"><?php echo $xml_file->xml_file; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="submit">
							<a href="<?php echo wb_go_to_step(2); ?>" class="button wb-next-step"><?php _e( 'Skip This Step'); ?></a>
						<input type="submit" id="wb-submit" class="wb-sumit button-primary wb-next-step" name="import_step" value="<?php _e( 'Next Step'); ?>" />
						</div>
					</form>
					<div id="wb_overlay">&nbsp;</div> <!-- Preloader -->
				</div>
			</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(2); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(3); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(4); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(5); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(6); ?></div>
		</div>

	<?php break; // end step 1 here
		case 2: ?>
	<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(1); ?></div>
		</div>
		<div class="wb_tab">
			<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
			<div class="wb_tab_content">
				<?php wb_status_info($step,$status,$msg); ?>
				<form method="post" id="update_form_step" class="wb_steps_form" name="update_form_step" action="">

						<p>Update latitude and longitude for all imported events addresses with google.com map api.</p>

					<div class="submit">
						<a href="<?php echo wb_go_to_step(1); ?>" class="button-primary wb-prev-step"><?php _e( 'Prev Step'); ?></a>
						<?php if(wb_check_lat_lng()) { ?>
							<input type="submit" id="wb-submit" class="button-primary wb-next-step" name="update_step" value="<?php _e( 'Next Step'); ?>" />
						<?php } else { ?>
							<a href="<?php echo wb_go_to_step(3); ?>" class="button wb-next-step"><?php _e( 'Already Done'); ?></a>
						<?php } ?>
					</div>
				</form>
			<div id="wb_overlay">&nbsp;</div> <!-- Preloader -->
			</div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(3); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(4); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(5); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(6); ?></div>
		</div>


	<?php break; // end step 2 here
		case 3: ?>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(1); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(2); ?></div>
		</div>
		<div class="wb_tab">
			<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
			<div class="wb_tab_content">
				<?php wb_status_info($step,$status,$msg); ?>
				<form method="post" id="commit_form_step" class="wb_steps_form" name="commit_form_step" action="">
					<table class="table table-striped table-bordered" id="dyntable">
		        <colgroup>
		            <col class="con0" style="align: center; width: 4%" />
		            <col class="con1" />
		            <col class="con0" />
		            <col class="con1" />
		            <col class="con1" />
		            <col class="con1" />
		            <col class="con0" />
		        </colgroup>
		        <thead>
		            <tr>
		              	<th class="head0 nosort">Select</th>
		                <th class="head0">Event Name</th>
		                <th class="head1">Event Date</th>
		                <th class="head0">Event Location</th>
		                <th class="head1">Num. of Tickets</th>
		            </tr>
		        </thead>
		        <tbody>
						<?php $events = wb_get_events($imported=0);
									if(count($events)) { 
										foreach($events as $event) { ?>
		            <tr class="gradeX">
		              <td class="aligncenter"><span class="center">
		                <input type="checkbox" name="event<?php echo $event->id; ?>" checked="checked" value="<?php echo $event->id; ?>" />
		              </span></td>
		                <td><?php echo $event->event_name; ?></td>
		                <td><?php echo date("m/d/Y h:i:s A", $event->event_date); ?></td>
		                <td><?php echo $event->event_location_name; ?></td>
		                <td><?php echo wb_get_tickets_count($event->id); ?></td>
		            </tr>
							<?php } } ?>
		        </tbody>
      		</table>
					<div class="submit">
						<a href="<?php echo wb_go_to_step(2); ?>" class="button-primary wb-prev-step"><?php _e( 'Prev Step'); ?></a>
						<input type="submit" id="wb-submit" class="button-primary wb-next-step" name="commit_step" value="<?php _e( 'Next Step'); ?>" />
						<input type="hidden" name="commit_fields" id="commit_fields" value="" />
					</div>
				</form>
				<div id="wb_overlay">&nbsp;</div> <!-- Preloader -->
			</div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(4); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(5); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(6); ?></div>
		</div>

	<?php
			break; // end step 3 here
		case 4: ?>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(1); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(2); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(3); ?></div>
		</div>
		<div class="wb_tab">
			<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
			<div class="wb_tab_content">
				<?php wb_status_info($step,$status,$msg); ?>
				<form method="post" id="publish_form_step" class="wb_steps_form" name="publish_form_step" action="">

						<p>Now all imported events are in draft mode. Please click Next step to publish events.</p>

					<div class="submit">
						<a href="<?php echo wb_go_to_step(3); ?>" class="button-primary wb-prev-step"><?php _e( 'Prev Step'); ?></a>
						<input type="submit" id="wb-submit" class="button-primary wb-next-step" name="publish_step" value="<?php _e( 'Next Step'); ?>" />
					</div>
				</form>
				<div id="wb_overlay">&nbsp;</div> <!-- Preloader -->
			</div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(5); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(6); ?></div>
		</div>
	<?php
			break; // end step 4 here
		case 5: ?>		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(1); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(2); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(3); ?></div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(4); ?></div>
		</div>
		<div class="wb_tab">
			<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
			<div class="wb_tab_content">
				<?php wb_status_info($step,$status,$msg); ?>
				<form method="post" id="ticket_publish_form_step" class="wb_steps_form" name="ticket_publish_form_step" action="">

						<p>Now Publish tickets of all published events.</p>

					<div class="submit">
						<a href="<?php echo wb_go_to_step(4); ?>" class="button-primary wb-prev-step"><?php _e( 'Prev Step'); ?></a>
						<input type="submit" id="wb-submit" class="button-primary wb-next-step" name="ticket_publish_step" value="<?php _e( 'Next Step'); ?>" />
					</div>
				</form>
				<div id="wb_overlay">&nbsp;</div> <!-- Preloader -->
			</div>
		</div>
		<div class="wb_tab Inactive">
				<div class="wb_tab_title"><?php wb_import_step_title(6); ?></div>
		</div>
	<?php
			break; // end step 5 here
		case 6: ?>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(1); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(2); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(3); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(4); ?></div>
		</div>
		<div class="wb_tab Inactive">
			<div class="wb_tab_title"><?php wb_import_step_title(5); ?></div>
		</div>
		<div class="wb_tab">
			<div class="wb_tab_title"><?php wb_import_step_title($step); ?></div>
			<div class="wb_tab_content">
				<?php wb_status_info($step,$status,$msg); ?>
				<form method="post" id="status_form_step" class="wb_steps_form" name="status_form_step" action="">

						<p>You have done good job.</p>

					<div class="submit">
						<a href="<?php echo wb_go_to_step(1); ?>" class="button wb-next-step"><?php _e( 'Import Another'); ?></a>
					</div>
				</form>
			</div>
		</div>
	<?php
			break; // end step 6 here

			} // end switch case
	 ?>		

	</div> <!-- end wb_tabs -->
</div> <!-- end wrapper -->
<?php } ?>
