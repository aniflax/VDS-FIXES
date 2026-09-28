<?php

if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/../../../');

/** Loads the WordPress Environment and Template */
require_once( ABSPATH . '/wp-config.php' );
global $wpdb;
if (!defined('DTCUSTOMSANKALPALOG')) define('DTCUSTOMSANKALPALOG', ABSPATH . '../logs/api_get_customSankalpa.log');
$eventid = urldecode($_GET['eventid']);
error_log("\n Event ID :  $eventid ", 3, DTCUSTOMSANKALPALOG);

require_once get_stylesheet_directory() . '/helpers/f-standardfunctions.php';
$eventjson = get_event_details($eventid);
$eventDetailsData = json_decode($eventjson, true);
$additionalSankalpa = intval($eventDetailsData[0]['additionalSankalpa']);
error_log("\n Additional Sankalpa :  $additionalSankalpa ", 3, DTCUSTOMSANKALPALOG);
$customSankalpas = array();
if ($additionalSankalpa == 1){
	$customSankalpas = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'vds_sankalpa_additional WHERE `event_id`=%s AND `show_sankalpa` = 1',$eventid),ARRAY_A);

	// Recurring-event fix: pass the EVENT's recurrence to the popup so the date
	// calendar shows for daily-recurring events that use custom sankalpas (e.g. E79280).
	foreach ($customSankalpas as $k => $row) {
		$customSankalpas[$k]['event_start_date'] = $eventDetailsData[0]['event_start_date'];
		$customSankalpas[$k]['repeat_frequency']  = $eventDetailsData[0]['repeat_frequency'];
	}
}else{
	$mySankalpaObj = new stdClass();
	$mySankalpaObj->id = 0;
	$mySankalpaObj->sankalpa_description = $eventDetailsData[0]['sankalpaDesc'];
	$mySankalpaObj->sankalpa_amount = $eventDetailsData[0]['sevaamt'];
	$mySankalpaObj->sankalpa_pass = $eventDetailsData[0]['sankalpaEntryPass'];
	$mySankalpaObj->event_start_date = $eventDetailsData[0]['event_start_date'];
	$mySankalpaObj->repeat_frequency = $eventDetailsData[0]['repeat_frequency']; //daily nonRecurring 
	$mySankalpaObj->tomorrow = date("m/d/Y", strtotime("+1 day"));
			
	$customSankalpas[] = $mySankalpaObj;
	unset($mySankalpaObj);	

	$wpdb->flush();
}
echo json_encode($customSankalpas);
?>
