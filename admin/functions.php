<?php 
/*************************
 Get All XML past files.
 :- Args: None
**************************/
function wb_get_xml_files() {
		global $wpdb;
		$objects = array();
		$table_name = WB_XML_TABLE;
		$output = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY 'id' DESC" );
		return $output;
}

/*************************
 Store new XML file.
 :- Args: Data XML FILE
**************************/
function wb_store_xml_file($xml_file)	{
	global $wpdb;
	$table_name = WB_XML_TABLE;
	if(!empty($xml_file)) { // checking this xml file is there.
		$check = $wpdb->get_results( "SELECT * FROM $table_name WHERE xml_file='$xml_file'" );

		if(count($check)==0) { // Checking this xml file is already exists.
			$wpdb->query( $wpdb->prepare("INSERT INTO `$table_name`(`xml_file`)	VALUES ( %s)", $xml_file) );
			$wpdb->show_errors();
		}
	}
}

/***************************
 Get import or setting url
 :- Args: Data String
****************************/
function wb_get_url($page) {
	if($page) {
		return get_admin_url(get_current_blog_id(),'admin.php?page=wb_'.$page);
	}
}

/**********************
 Go to step link
 :- Args: Data Number
**********************/
function wb_go_to_step($step) {
	if($step) {
		$url = wb_get_url('import').'&step='.$step;
		return $url;
	}
}



/***********************
 Import Status cases
 :- Args: Data Strings
************************/
function wb_status_info($step,$status,$msg) {
	$status_msg = "";
	switch($msg) {
		case 1: 
			switch($status) {
				case 'info':
		    	$status_msg = '<strong>Welcome!</strong> Please insert or upload XML to import.';
				break;
				case 'alert':
		    	$status_msg = '<strong>Alert!</strong> The events in this XML file are already imported.';
				break;
				case 'success':
		    	$status_msg = '<strong>Success!</strong> You have imported XML file successfully. ('.$_REQUEST['event_c'].') Events imported.';
				break;
				case 'error':
		    	$status_msg = '<strong>Error!</strong> Your XML file was not imported. Please check your xml file.';
				break;
			}
		break;
		case 2:
			switch($status) {
				case 'info':
		    	$status_msg = '';
				break;
				case 'alert':
		    	$status_msg = '<strong>Alert!</strong> There are no events to update.';
				break;
				case 'success':
		    	$status_msg = '<strong>Success!</strong> All events adresses updated.';
				break;
				case 'error':
		    	$status_msg = '<strong>Error!</strong> Google Map api disconnected. Please try again.';
				break;
			} 
		break;
		case 3:
			switch($status) {
				case 'info':
		    	$status_msg = '';
				break;
				case 'alert':
		    	$status_msg = '<strong>Alert!</strong> There are no events to commit.';
				break;
				case 'success':
		    	$status_msg = '<strong>Success!</strong> All selected events committed.';
				break;
				case 'error':
		    	$status_msg = '<strong>Error!</strong> Some Location or Event failed to commit. Please try again.';
				break;
			} 
		break;
		case 4:
			switch($status) {
				case 'info':
		    	$status_msg = '';
				break;
				case 'alert':
		    	$status_msg = '<strong>Alert!</strong> There are no draft events to publish. Go to Next Step';
				break;
				case 'success':
		    	$status_msg = '<strong>Success!</strong> All draft events are published.';
				break;
				case 'error':
		    	$status_msg = '<strong>Error!</strong> Some Event failed to publish. Please try again.';
				break;
			} 
		break;
		case 5:
			switch($status) {
				case 'info':
		    	$status_msg = '';
				break;
				case 'alert':
		    	$status_msg = '<strong>Alert!</strong> There are no tickets of events to publish.';
				break;
				case 'success':
		    	$status_msg = '<strong>Success!</strong> All tickets of events are published.';
				break;
				case 'error':
		    	$status_msg = '<strong>Error!</strong> Some Ticket failed to publish. Please try again.';
				break;
			} 
		break;
	}
	
	if($status) {
		echo '<div class="alert alert-'.$status.'">
		  			<button type="button" class="close" data-dismiss="alert">×</button>
		      	'.$status_msg.'
		  		</div>';
	}
}


/***********************
 Import Step Titles
 :- Args: Data Number
**********************/
function wb_import_step_title($step) {
	if(!$step) $step=1;
	switch($step) {
		case 1: 
    	echo 'Step 1: Import XML';
		break;
		case 2: 
    	echo 'Step 2: Fix All Events Addresses';
		break;
		case 3: 
    	echo 'Step 3: Commit Events to WP events manager';
		break;
		case 4:
    	echo 'Step 4: Publish committed events';
		break;
		case 5:
    	echo 'Step 5: Publish Tickets of publised events';
		break;
		case 6:
    	echo 'Step 6: XML Importing completed.';
		break;
	}
}

/**********************
 Push event in to DB
 :- Args: Data Array
***********************/
function wb_push_event_return_id($data){
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$require_fields = array('event_name', 'event_date', 'event_location_name');
	$i=1;
	foreach($data as $key=>$value) {
		if(in_array($key, $require_fields)) {
			if($i <= 2) {
				$and = " and ";
			}
			else {
				$and = "";
			}
			$dataQuery = $dataQuery.$key."='".$value."'".$and;
			$i++;
		}
	}
	$check = $wpdb->get_results( "SELECT * FROM $event_table WHERE $dataQuery" );
	if(count($check)==0) { // Checking this ticket is already exists.
		$wpdb->insert( $event_table, $data );
		$output['id'] = $wpdb->insert_id;
		$output['num'] = 1;
	}
	else {
		unset($data['import_status']);
		unset($data['ticket_import_status']);
		$where = array('id'=>$check[0]->id);
		$wpdb->update( $event_table, $data, $where);
		$output['id'] = $check[0]->id;
		$output['num'] = 0;
	}
		return $output;
}


/**********************
 Push ticket in to DB
 :- Args: Data Array
***********************/
function wb_push_ticket($data){
	global $wpdb;
	$ticket_table = WB_TICKETS_TABLE;
	$output = array();
	$require_fields = array('event_id', 'ticket_name', 'ticket_start');
	$i=1;
	foreach($data as $key=>$value) {
		if(in_array($key, $require_fields)) {
			if($i <= 2) {
				$and = " and ";
			}
			else {
				$and = "";
			}
			$dataQuery = $dataQuery.$key."='".$value."'".$and;
			$i++;
		}
	}
	$check = $wpdb->get_results( "SELECT * FROM $ticket_table WHERE $dataQuery" );
	if(count($check)==0) { // Checking this ticket is already exists.
		$wpdb->insert( $ticket_table, $data );
		$output['id'] = $wpdb->insert_id;
		$output['num'] = 1;
	}
	else {
		$where = array('id'=>$check[0]->id);
		$wpdb->update( $ticket_table, $data, $where);
		$output['id'] = $check[0]->id;
		$output['num'] = 0;
	}
		return $output;
}


/********************************
 Remove single quote from string
 :- Args: String
*********************************/
function removesinglequote($string) {
	if($string) {
		return str_replace("'","",$string);
	}
}

/********************************
 WB Debug String/Array
 :- Args: Label, Value
*********************************/
function wb_debug($label, $data) {
	echo "<div style=\"margin-left: 40px;background-color:#eeeeee;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
}

/***************************
 Process XML file (STEP 1)
 :- Args: XML FILE
***************************/
function wb_import_xml($xml) {
	$wb_events = simplexml_load_file($xml); //Import XML to Array
	$wb_events_fields = array();
	$wb_ticket_fields = array();
	$final_events = array();
	if(count($wb_events) > 0) {	
		foreach($wb_events as $wb_event) {
				if (!array_key_exists("EventName",$wb_event)) {
						echo '<script>location.href="'.wb_go_to_step(1).'&status=error";</script>'; //Redirect to Step1
				} //END IF
	
				$check_tickets = count($wb_event->Performances->Performance);

				$events = array();

				if($check_tickets > 0) {

					$check_array = array();
					$filtered_array = array();
					$i=0;
					foreach($wb_event->Performances->Performance as $ticket) {
						$key = 'ticket-'.$i;
						$check_array[$key] = $ticket->VenueName.'-'.$ticket->PerformanceDateTime;
						$i++;
					}
					$filtered_array = array_unique($check_array);

					$j=0;
					foreach($wb_event->Performances->Performance as $ticket) {
						$today = strtotime("now");
						$timestamp = strtotime($ticket->PerformanceDateTime);
						if($today < $timestamp) {

							$newkey = 'ticket-'.$j;
							if (array_key_exists($newkey,$filtered_array)) {
								// Here add ticket as Event
								$createEvent['event_name'] = removesinglequote($ticket->PerformanceName);
								$createEvent['event_url']	= $wb_event->EventURL;
								$createEvent['event_date'] = strtotime($ticket->PerformanceDateTime);
								$createEvent['event_spaces'] = $ticket->TicketsAvailable;
								$createEvent['event_location_name']	= removesinglequote($ticket->VenueName);
								$createEvent['event_location_address'] = $ticket->VenueAddress1.' '.$ticket->VenueAddress2;
								$createEvent['event_location_town'] = removesinglequote($ticket->VenueCity);
								$createEvent['event_location_state'] = $ticket->VenueStateProvince;
								$createEvent['event_location_postcode']	= $ticket->VenueZip;
								$createEvent['event_location_country'] = $ticket->VenueCountry;
								$createEvent['event_location_phone'] = $ticket->VenuePhone;
								$createEvent['event_location_fax'] = $ticket->VenueFax;
								$createEvent['event_location_email'] = $ticket->VenueEmail;
								$createEvent['event_location_url'] = $ticket->VenueURL;
								$createEvent['event_location_timezone'] = $ticket->VenueTimeZone;
								$createEvent['event_desc'] = removesinglequote($ticket->VenueDescription);
								$events[$newkey] = $createEvent;
							}
							else {
								// Find EVENT and add ticket as that EVENT ticket
	
								$check_value = $ticket->VenueName.'-'.$ticket->PerformanceDateTime;
								$findKey = array_search($check_value,$filtered_array);

								$createTicket['ticket_name'] = removesinglequote($ticket->PerformanceName);
								$createTicket['ticket_desc'] = removesinglequote($ticket->VenueDescription);
								$createTicket['ticket_start'] = strtotime($ticket->PerformanceDateTime);
								$createTicket['ticket_end'] = strtotime($ticket->PerformanceDateTime);
								$createTicket['ticket_spaces'] = $ticket->TicketsAvailable;
								$events[$findKey]['tickets'][] = $createTicket;
								
							}

						}	//END IF

						$j++;

					} //END FOREACH
						foreach($events as $event) {
								array_push($final_events, $event);
						}			
				} //END IF

			} //END FOREACH

		//wb_debug("Array Data", $final_events);

		foreach($final_events as $final_event) {

		//wb_debug("Array Data", $final_event);
		$wb_events_fields = array();
		$wb_events_fields['event_name'] = $final_event['event_name'];
		$wb_events_fields['event_url'] = $final_event['event_url'];
		$wb_events_fields['event_date'] = $final_event['event_date'];
		$wb_events_fields['event_spaces'] = $final_event['event_spaces'];
		$wb_events_fields['event_location_name'] = $final_event['event_location_name'];
		$wb_events_fields['event_location_address'] = $final_event['event_location_address'];
		$wb_events_fields['event_location_town'] = $final_event['event_location_town'];
		$wb_events_fields['event_location_state'] = $final_event['event_location_state'];
		$wb_events_fields['event_location_postcode'] = $final_event['event_location_postcode'];
		$wb_events_fields['event_location_country'] = $final_event['event_location_country'];
		$wb_events_fields['event_location_phone'] = $final_event['event_location_phone'];
		$wb_events_fields['event_location_fax'] = $final_event['event_location_fax'];
		$wb_events_fields['event_location_email'] = $final_event['event_location_email'];
		$wb_events_fields['event_location_url'] = $final_event['event_location_url'];
		$wb_events_fields['event_location_timezone'] = $final_event['event_location_timzone'];
		$wb_events_fields['event_desc'] = $final_event['event_desc'];
		$wb_events_fields['import_status'] = 0;
		$wb_events_fields['ticket_import_status'] = 0;

		$event_output = wb_push_event_return_id($wb_events_fields); // Push Event to DB Here
		$event_id = $event_output['id']; // Inserted Event ID
		$events_inserted =  $events_inserted+$event_output['num']; // Inserted Events Count

			if(count($final_event['tickets']) > 0) {
				foreach($final_event['tickets']  as $final_ticket) {
					$wb_ticket_fields = array();
					$wb_ticket_fields['event_id'] = $event_id; // Connect Ticket to Event with Event ID
					$wb_ticket_fields['ticket_name'] = $final_ticket['ticket_name'];
					$wb_ticket_fields['ticket_desc'] = $final_ticket['ticket_desc'];
					$wb_ticket_fields['ticket_start'] = $final_ticket['ticket_start'];
					$wb_ticket_fields['ticket_end'] = $final_ticket['ticket_end'];
					$wb_ticket_fields['ticket_spaces'] = $final_ticket['ticket_spaces'];
					$ticket_id = wb_push_ticket($wb_ticket_fields); // Push Ticket to DB Here
				}
			}
	
		}

		wb_store_xml_file($xml); //Store xml file if its new.
		if($events_inserted == 0) {
			echo '<script>location.href="'.wb_go_to_step(2).'&status=alert&msg=1";</script>'; //Redirect to Step2
		}
		else {
			echo '<script>location.href="'.wb_go_to_step(2).'&status=success&msg=1&event_c='.$events_inserted.'";</script>'; //Redirect to Step2
		}
	} // END IF
	else {

		echo '<script>location.href="'.wb_go_to_step(1).'&status=error&msg=1";</script>'; //Redirect to Step1

	}// END ELSE IF
}

/*************************
 Get Events 
 :- Args : Data Number
*************************/
function wb_get_events($imported=0) {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$today = strtotime("now");
	$dataQuery = "event_date >= ".$today." and import_status=".$imported;	
	$output = $wpdb->get_results( "SELECT * FROM $event_table WHERE $dataQuery" );
	return $output;
}

/**********************************
 Get Events of Not imported Tickets
 :- Args : None
***********************************/
function wb_get_events_non_tickets() {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$dataQuery = "ticket_import_status=0 and import_status=1";	
	$output = $wpdb->get_results( "SELECT * FROM $event_table WHERE $dataQuery" );
	return $output;
}

/******************************
 Get Single Event 
 :- Args : Data Number-Event ID
*******************************/
function wb_get_event($event_id) {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$output = $wpdb->get_row( "SELECT * FROM $event_table WHERE id=$event_id" );
	return $output;
}

/**********************************
 Get Single Event By Any Column
 :- Args : Data String-Column Name,
					 Data Value-Column Value
**********************************/
function wb_get_event_by_column($column_name,$column_value) {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$output = $wpdb->get_row( "SELECT * FROM $event_table WHERE $column_name=$column_id" );
	return $output;
}


/*******************************
 Get Tickets of an Event 
 :- Args : Data Number-Event ID
********************************/
function wb_get_tickets($event_id) {
	global $wpdb;
	$ticket_table = WB_TICKETS_TABLE;
	$output = $wpdb->get_results( "SELECT * FROM $ticket_table WHERE event_id=$event_id" );	
	return $output;
}


/********************************
 Get Num of Tickets of an Event 
 :- Args : Data Number-Event ID
********************************/
function wb_get_tickets_count($event_id) {
	$tickets = wb_get_tickets($event_id);
	$count = count($tickets);
	if($count == 0) {
		return 'No Tickets';
	}
	else {
		return $count;
	}
}


/**********************************
 Check Tickets there for an Event?
 :- Args : Data Number-Event ID
***********************************/
function wb_has_tickets($event_id) {
	$tickets = wb_get_tickets($event_id);
	$count = count($tickets);
	if($count > 0) {
		return true;
	}
	else {
		return false;
	}
}

/******************************
	Get Total spaces of tickets.
 :- Args : Data Number-Event ID
******************************/
function wb_total_ticket_spaces($event_id) {
	global $wpdb;
	$ticket_table = WB_TICKETS_TABLE;
	$output = $wpdb->get_row( "SELECT SUM(ticket_spaces) as total_space FROM $ticket_table WHERE event_id=$event_id" );	
	return $output->total_space;
}

/*********************************************
	Get Lat and Lng of address from Google map.
 :- Args : Data String-Event ID
**********************************************/
function wb_get_lat_lng($address) {
	if($address) {
		$output = array();
		$google_map = simplexml_load_file('http://maps.googleapis.com/maps/api/geocode/xml?address='.$address.'&sensor=true');
		$output['event_location_latitude'] = $google_map->result->geometry->location->lat;
		$output['event_location_longitude'] = $google_map->result->geometry->location->lng;
		return $output;
	}
}

/*********************************************
	Check addresses of events.
 :- Args : None
**********************************************/
function wb_check_lat_lng() {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$dataQuery = "event_location_latitude = '' and event_location_longitude = ''";	
	$check = $wpdb->get_results( "SELECT * FROM $event_table WHERE $dataQuery" );
	if(count($check) > 0) {
		return true;
	}
	else {
		return false;	
	}	

}


/*********************************************
	Update addresses of events. (STEP 2)
 :- Args : None
**********************************************/
function wb_update_events_addresses() {
	global $wpdb;
	$event_table = WB_EVENTS_TABLE;
	$events = wb_get_events($imported=0);
	if(count($events)) { 
		foreach($events as $event) {
			$address = $event->event_location_address.', '.$event->event_location_town.', '.$event->event_location_state.', '.$event->event_location_postcode.', '.$event->event_location_country;
			$data = wb_get_lat_lng($address);
			$where = array('id'=>$event->id);
			$wpdb->update( $event_table, $data, $where);
		}
		echo '<script>location.href="'.wb_go_to_step(3).'&status=success&msg=2";</script>'; //Redirect to Step3
	}
	else {
		echo '<script>location.href="'.wb_go_to_step(3).'&status=alert&msg=2";</script>'; //Redirect to Step2
	}

}


/****************************************
 Check and Insert and return Locatioin ID
 :- Args : Data Array
******************************************/
function wb_create_location($data) {
	global $wpdb;
	$location_table = WB_EM_LOCATIONS;
	$require_fields = array('location_name', 'location_address', 'location_town', 'location_state', 'location_postcode', 'location_country');
	$i=1;
	foreach($data as $key=>$value) {
		if(in_array($key, $require_fields)) {
			if($i < count($require_fields)) {
				$and = " and ";
			}
			else {
				$and = "";
			}
			$dataQuery = $dataQuery.$key."='".$value."'".$and;
			$i++;
		}
	}
	$check = $wpdb->get_results( "SELECT * FROM $location_table WHERE $dataQuery" );
	if(count($check)==0) { // Checking this ticket is already exists.
		
		$postarr = array();
	  $postarr['post_title'] = $data['location_name'];
		$postarr['comment_status'] = 'closed';
    $postarr['post_status'] = 'publish';
    $postarr['post_author'] = get_current_user_id();
    $postarr['post_type'] = 'location';
    $location_post_id = wp_insert_post($postarr);
		if($location_post_id != 0) {
			update_post_meta($location_post_id, '_location_address', $data['location_address']); 
			update_post_meta($location_post_id, '_location_town', $data['location_town']);
			update_post_meta($location_post_id, '_location_state', $data['location_state']);
			update_post_meta($location_post_id, '_location_postcode', $data['location_postcode']);
			update_post_meta($location_post_id, '_location_country', $data['location_country']);
			update_post_meta($location_post_id, '_location_status', $data['location_status']);
			update_post_meta($location_post_id, '_location_latitude', $data['location_latitude']);
			update_post_meta($location_post_id, '_location_longitude', $data['location_longitude']);

			$where = array('post_id'=>$location_post_id);
			$wpdb->update( $location_table, $data, $where);
			$location_id = get_post_meta($location_post_id, '_location_id', true);
			}
	}
	else {
		$location_id = $check[0]->location_id;
	}
		return $location_id;

}



/****************************************
 Check and Insert and return Event ID
 :- Args : Data Array
******************************************/
function wb_create_event($data,$wb_event_id) {
	global $wpdb;
	$event_table = WB_EM_EVENTS;
	$wb_event_table = WB_EVENTS_TABLE;
	$require_fields = array('event_name', 'event_start_time', 'event_end_time', 'event_start_date', 'event_end_date', 'location_id');
	$i=1;
	foreach($data as $key=>$value) {
		if(in_array($key, $require_fields)) {
			if($i < count($require_fields)) {
				$and = " and ";
			}
			else {
				$and = "";
			}
			$dataQuery = $dataQuery.$key."='".$value."'".$and;
			$i++;
		}
	}
	$check = $wpdb->get_results( "SELECT * FROM $event_table WHERE $dataQuery" );
	if(count($check)==0) { // Checking this ticket is already exists.
		
		$postarr = array();
	  $postarr['post_title'] = $data['event_name'];
		$postarr['post_content'] = apply_filters('comment_text', $data['post_content']);
		$postarr['comment_status'] = 'closed';
    $postarr['post_status'] = 'publish';
    $postarr['post_author'] = get_current_user_id();
    $postarr['post_type'] = 'event';
    $event_post_id = wp_insert_post($postarr);
		if($event_post_id != 0) {
			update_post_meta($event_post_id, '_event_start_time', $data['event_start_time']); 
			update_post_meta($event_post_id, '_event_end_time', $data['event_end_time']);
			update_post_meta($event_post_id, '_event_all_day', $data['event_all_day']);
			update_post_meta($event_post_id, '_event_start_date', $data['event_start_date']);
			update_post_meta($event_post_id, '_event_end_date', $data['event_end_date']);
			update_post_meta($event_post_id, '_event_rsvp', $data['event_rsvp']);
			update_post_meta($event_post_id, '_event_spaces', $data['event_spaces']);
			update_post_meta($event_post_id, '_location_id', $data['location_id']);
			update_post_meta($event_post_id, '_event_status', $data['event_status']);
			update_post_meta($event_post_id, '_event_private', $data['event_private']);
			update_post_meta($event_post_id, '_start_ts', $data['start_ts']);
			update_post_meta($event_post_id, '_end_ts', $data['end_ts']);
			update_post_meta($event_post_id, '_wb_event', $data['wb_event']);
			
			$wb_where = array('id'=>$wb_event_id);
			$wb_data = array('em_event_id'=>$event_post_id,'import_status'=>1,'ticket_import_status'=>$data['ticket_import_status']);
			$wpdb->update( $wb_event_table, $wb_data, $wb_where);

		}
	}
	else {
		$where = array('event_id'=>$check[0]->event_id);
		$wpdb->update( $event_table, $data, $where);
		$event_id = $check[0]->event_id;
		$event_post_id = $check[0]->post_id;
		$postarr = array();
		$postarr['ID'] = $event_post_id;
	  $postarr['post_title'] = $data['event_name'];
		$postarr['post_content'] = apply_filters('comment_text', $data['post_content']);
		$postarr['comment_status'] = 'closed';
    $postarr['post_status'] = 'publish';
    $postarr['post_author'] = get_current_user_id();
    $postarr['post_type'] = 'event';
    $event_post_id = wp_update_post($postarr);
		if($event_post_id != 0) {
			update_post_meta($event_post_id, '_event_start_time', $data['event_start_time']); 
			update_post_meta($event_post_id, '_event_end_time', $data['event_end_time']);
			update_post_meta($event_post_id, '_event_all_day', $data['event_all_day']);
			update_post_meta($event_post_id, '_event_start_date', $data['event_start_date']);
			update_post_meta($event_post_id, '_event_end_date', $data['event_end_date']);
			update_post_meta($event_post_id, '_event_rsvp', $data['event_rsvp']);
			update_post_meta($event_post_id, '_event_spaces', $data['event_spaces']);
			update_post_meta($event_post_id, '_location_id', $data['location_id']);
			update_post_meta($event_post_id, '_event_status', $data['event_status']);
			update_post_meta($event_post_id, '_event_private', $data['event_private']);
			update_post_meta($event_post_id, '_start_ts', $data['start_ts']);
			update_post_meta($event_post_id, '_end_ts', $data['end_ts']);
			update_post_meta($event_post_id, '_wb_event', $data['wb_event']);

			$wb_where = array('id'=>$wb_event_id);
			$wb_data = array('em_event_id'=>$event_post_id,'import_status'=>1,'ticket_import_status'=>$data['ticket_import_status']);
			$wpdb->update( $wb_event_table, $wb_data, $wb_where);
		}
		
	}
		return $event_post_id;

}


/****************************************
 Check and Insert and return Ticket ID
 :- Args : Data Array
******************************************/
function wb_create_ticket($data) {
	global $wpdb;
	$ticket_table = WB_EM_TICKETS;
	$require_fields = array('event_id', 'ticket_name', 'ticket_start', 'ticket_end');
	$i=1;
	foreach($data as $key=>$value) {
		if(in_array($key, $require_fields)) {
			if($i < count($require_fields)) {
				$and = " and ";
			}
			else {
				$and = "";
			}
			$dataQuery = $dataQuery.$key."='".$value."'".$and;
			$i++;
		}
	}
	$check = $wpdb->get_results( "SELECT * FROM $ticket_table WHERE $dataQuery" );
	if(count($check)==0) { // Checking this ticket is already exists.
		$wpdb->insert( $ticket_table, $data );
		$ticket_id = $wpdb->insert_id;
	}
	else {
		$where = array('ticket_id'=>$check[0]->ticket_id);
		$wpdb->update( $ticket_table, $data, $where);
		$ticket_id = $check[0]->ticket_id;
	}
		return $ticket_id;

}


/******************************
 Start Commit Process (STEP 3)
 :- Args : Events Array
*******************************/
function wb_start_commit($eventids=array()){
	if(count($eventids) > 0 ) {
		foreach( $eventids as $eventid) {
			$main_data = array();
			$event_data = array();
			$location_data = array();
			$main_data = wb_get_event($eventid);

			//Lets Create Location set
			$location_data['location_slug'] = sanitize_title($main_data->event_location_name);
			$location_data['location_name'] = $main_data->event_location_name;
			$location_data['location_owner'] = get_current_user_id();
			$location_data['location_address'] = $main_data->event_location_address;
			$location_data['location_town'] = $main_data->event_location_town;
			$location_data['location_state'] = $main_data->event_location_state;
			$location_data['location_postcode'] = $main_data->event_location_postcode;
			$location_data['location_country'] = $main_data->event_location_country;
			$location_data['location_status'] = 1;
			$location_data['location_private'] = 0;
			$location_data['location_latitude'] = $main_data->event_location_latitude;
			$location_data['location_longitude'] = $main_data->event_location_longitude;

			$location_id = wb_create_location($location_data);

			if ($location_id == 0) {
						echo '<script>location.href="'.wb_go_to_step(3).'&status=error&msg=3";</script>'; //Redirect to Step3
			} //END IF

			//Lets Create Event set
			$event_data['event_slug'] = sanitize_title($main_data->event_name);
			$event_data['event_owner'] = get_current_user_id();
			$event_data['event_status'] = 1;
			$event_data['event_name'] = $main_data->event_name;
			$event_data['event_start_time'] = date('H:i:s', $main_data->event_date);
			$event_data['event_end_time'] = date('H:i:s', $main_data->event_date);
			$event_data['event_all_day'] = 0;
			$event_data['event_start_date'] = date('Y-m-d', $main_data->event_date);
			$event_data['event_end_date'] = date('Y-m-d', $main_data->event_date);
			$event_data['post_content'] = $main_data->event_desc;
			$event_data['event_rsvp'] = 0;
			$event_data['wb_event'] = 1;
			if(wb_has_tickets($main_data->id)) {
				$event_data['event_spaces'] = wb_total_ticket_spaces($main_data->id);
				$event_data['ticket_import_status'] = 0;
			}
			else {
				$event_data['event_spaces'] = $main_data->event_spaces;
				$event_data['ticket_import_status'] = 1;
			}
			$event_data['event_private'] = 0;
			$event_data['location_id'] = $location_id;
			$event_data['start_ts'] = $main_data->event_date;
			$event_data['end_ts'] = $main_data->event_date;

			$event_id = wb_create_event($event_data,$eventid);
			if ($event_id == 0) {
						echo '<script>location.href="'.wb_go_to_step(3).'&status=error&msg=3";</script>'; //Redirect to Step3
			} //END IF


		} // END FOREACH
	
	echo '<script>location.href="'.wb_go_to_step(4).'&status=success&msg=3";</script>'; //Redirect to Step4

	} // END IF

	else {
		echo '<script>location.href="'.wb_go_to_step(3).'&status=alert&msg=3";</script>'; //Redirect to Step3
	} // END ELSE IF
	
}

/****************************************
 Publish committed events (STEP 4)
 :- Args : None
******************************************/
function wb_publish_events() {
	global $wpdb;
	$post_table = WB_POSTS;
	$em_events_table = WB_EM_EVENTS;

	$args = array( 'post_type' => 'event', 'posts_per_page' => -1, 'post_status' => 'draft', 'meta_key' => '_wb_event',	'meta_value'=> 1 ); 
	$events = get_posts( $args );

	//$events = $wpdb->get_results( "SELECT * FROM $post_table WHERE post_type='event' and post_status='draft'" );
	if(count($events) >0 ) {
		foreach($events as $event) {

			$wp_event = wb_get_event_by_column('em_event_id',$event->ID);

			if(wb_has_tickets($wp_event->id)) {
				$event_rsvp = 1;
				update_post_meta($event->ID, '_event_rsvp', $event_rsvp);
			}

			$postarr['ID'] = $event->ID;
			$postarr['post_status'] = 'publish';
		  $postarr['post_author'] = get_current_user_id();
		  $event_post_id = wp_update_post($postarr);

		} // END FOREACH

	echo '<script>location.href="'.wb_go_to_step(5).'&status=success&msg=4";</script>'; //Redirect to Step5

	} //END IF
	else {
		echo '<script>location.href="'.wb_go_to_step(5).'&status=alert&msg=4";</script>'; //Redirect to Step4
	} //END IF
}


/****************************************
 Publish Tickets of Publised events (STEP 5)
 :- Args : None
******************************************/
function wb_publish_event_tickets() {
	global $wpdb;
	$wb_event_table = WB_EVENTS_TABLE;
	$wp_events = wb_get_events_non_tickets();
	if(count($wp_events) > 0) {
		foreach($wp_events as $wb_event) {
			$event_id = get_post_meta($wb_event->em_event_id,'_event_id',true); // GET EM EVENT ID

			if(wb_has_tickets($wb_event->id)) {

				$event_rsvp = 1;
				update_post_meta($wb_event->em_event_id,'_event_rsvp', $event_rsvp);

				$event_tickets = wb_get_tickets($wb_event->id);
				//Explode All tickets in this event
				foreach($event_tickets as $ticket) {
					$ticket_data = array();
					$ticket_data['event_id'] = $event_id;
					$ticket_data['ticket_name'] = $ticket->ticket_name;
					$ticket_data['ticket_description'] = $ticket->ticket_desc;
					$ticket_data['ticket_start'] = date('Y-m-d H:i:s', $ticket->ticket_start);
					$ticket_data['ticket_end'] = date('Y-m-d H:i:s', $ticket->ticket_end);
					$ticket_data['ticket_spaces'] = $ticket->ticket_spaces;
					$ticket_id = wb_create_ticket($ticket_data);	
					if ($ticket_id == 0) {
								echo '<script>location.href="'.wb_go_to_step(5).'&status=error&msg=5";</script>'; //Redirect to Step5
					} //END IF

				} // END FOREACH

				$wb_where = array('id'=>$wb_event->id);
				$wb_data = array('ticket_import_status'=>1);
				$wpdb->update( $wb_event_table, $wb_data, $wb_where);

			} //END IF
			
		} // END FOREACH
		echo '<script>location.href="'.wb_go_to_step(6).'&status=success&msg=5";</script>'; //Redirect to Step6
	} // END IF
	else {
		echo '<script>location.href="'.wb_go_to_step(6).'&status=alert&msg=5";</script>'; //Redirect to Step5
	} //END IF

}
?>
