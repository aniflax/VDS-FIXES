<?php
if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/../../../../../');

/** Loads the WordPress Environment and Template */
require_once(ABSPATH . '/wp-config.php');
if (!defined('DTERRORLOG')) define('DTERRORLOG', ABSPATH . '../logs/api_download_contrib.log');

global $wpdb;
$action = $_GET['action'];
error_log("\n\n---START @api_download_contrib--- Param Values :  $action ", 3, DTERRORLOG);

$getdata =  $_GET['employees'];
$cdata = str_replace('\\', '', $getdata);
$data = json_decode($cdata, TRUE);

error_log("\n Values : " . json_encode($data), 3, DTERRORLOG);


if ($data != null) {
	$state = $data['state'];
	$eventID = $data['eventID'];
	$fiscalyear = $data['fiscalyear'];
	$status = $data['status'];
	$datefrom = $data['datefrom'];
	$dateto = $data['dateto'];
	$delta = $data['delta'];

	if (!empty($data['emailstatus'])) {
		$emailstatus = $data['emailstatus'];
	}

	if (!empty($data['sevaType'])) {
		$sevaType = $data['sevaType'];
	}

	if (!empty($data['purpose'])) {
		$purpose = $data['purpose'];
	}

	if (!empty($data['sub_purpose_type'])) {
		$sub_purpose = $data['sub_purpose_type'];
	}

	if (!empty($data['event_date'])) {
		$event_date = $data['event_date'];
	}

	if (!empty($data['attending'])) {
		$attending = $data['attending'];
	}

	if (!empty($data['gender'])) {
		$gender = $data['gender'];
	}

	if (!empty($data['payment_type'])) {
		$payment_type = $data['payment_type'];
	}
}

error_log("\n 1.) Param values :  $state , $eventID ,$fiscalyear, $status ,$action ,$datefrom", 3, DTERRORLOG);
error_log("\n 2.) Param values :  $dateto , $emailstatus ,$sevaType, $purpose ,$sub_purpose ,$event_date ", 3, DTERRORLOG);

$whereAll = "SELECT * from wp_vds_contrib where ";

if ($status != null) {
	if ($status == '0') {
		$whereAll .= "status ='Not initiated'";
	} else if ($status == '1') {
		$whereAll .= "status ='Captured'";
	} else if ($status == '2') {
		$whereAll .= "status ='settled'";
	} else if ($status == '3') {
		$whereAll .= "status ='Failed'";
	} else if ($status == '4') {
		$whereAll .= "status IN ('Captured','settled')";
	} else if ($status == '5') {
		$whereAll .= "status ='Deleted'";
	} else {
		$whereAll .= "status IS NOT NULL";
	}
}

if ($datefrom != null && $dateto != null) {
	$cdatefrom = date("Y-m-d", strtotime($datefrom));
	$cdateto = date("Y-m-d", strtotime($dateto));
	$whereAll .= " AND post_date >= '" . $cdatefrom . "' AND post_date <= '" . $cdateto . " 23:59:59:999'";
}

if ($fiscalyear != null) {
	if ($fiscalyear == '2017-2018') {
		$whereAll .= " AND post_date >= '2017-04-01' AND post_date <= '2018-03-31'";
	} else if ($fiscalyear == '2018-2019') {
		$whereAll .= " AND post_date >= '2018-04-01' AND post_date <= '2019-03-31'";
	} else if ($fiscalyear == '2019-2020') {
		$whereAll .= " AND post_date >= '2019-04-01' AND post_date <= '2020-03-31'";
	} else if ($fiscalyear == '2020-2021') {
		$whereAll .= " AND post_date > '2020-04-01' AND post_date <= '2021-04-01'";
	} else if ($fiscalyear == '2021-2022') {
		$whereAll .= " AND post_date > '2021-04-01' AND post_date <= '2022-04-01'";
	} else if ($fiscalyear == '2022-2023') {
		$whereAll .= " AND post_date > '2022-04-01' AND post_date <= '2023-04-01'";
	} else if ($fiscalyear == '2023-2024') {
		$whereAll .= " AND post_date > '2023-04-01' AND post_date <= '2024-04-01'";
	} else if ($fiscalyear == '2024-2025') {
		$whereAll .= " AND post_date > '2024-04-01' AND post_date <= '2025-04-01'";
	} else if ($fiscalyear == 'All') {
		//$whereAll .= " AND post_date > '2017-04-01' AND post_date <= '2020-03-30'";			
	}
}

if ($state != null) {
	$whereAll .= " AND eventstate='" . $state . "'";
}

if ($eventID != null) {
	$searchForValue = ',';
	if (strpos($eventID, $searchForValue) !== false) {
		$whereAll .= " AND eventID IN (" . $eventID . ")";
	} else {
		$whereAll .= " AND eventID='" . $eventID . "'";
	}
}

if (!empty($emailstatus)) {
	$whereAll .= " AND email_status='" . $emailstatus . "'";
}

if (!empty($purpose)) {
	$whereAll .= " AND purpose='" . $purpose . "'";
}

if (!empty($sub_purpose)) {
	$whereAll .= " AND sub_purpose='" . $sub_purpose . "'";
}

if (!empty($event_date)) {
	$ts = strtotime($event_date);
	if ($ts) {
		$ed1 = date('m/d/Y', $ts); // 09/28/2026
		$ed2 = date('Y-m-d', $ts); // 2026-09-28
		$ed3 = date('j M Y', $ts); // 28 Sep 2026
		$whereAll .= " AND (eventDate='" . $ed1 . "' OR eventDate='" . $ed2 . "' OR eventDate='" . $ed3 . "')";
	} else {
		$whereAll .= " AND eventDate='" . $event_date . "'";
	}
}

if (!empty($attending)) {
	$whereAll .= " AND attending='" . $attending . "'";
}

if (!empty($gender)) {
	$whereAll .= " AND gender='" . $gender . "'";
}

if (!empty($payment_type)) {
	if ($payment_type == 'none') {
		$whereAll .= " AND paymenttype IS NULL";
	}else if ($payment_type == 'All') {
		$whereAll .= " AND paymenttype IN ('cash','card','chequedd','upi','Razorpay')";
	}else{
		$whereAll .= " AND paymenttype='" . $payment_type . "'";
	}
}
error_log("\n @SQL :  $whereAll", 3, DTERRORLOG);

if ($action == "getEmployee") {
	$getValues = $wpdb->get_results($whereAll, ARRAY_A);
	//error_log("\n Values : ".json_encode($getValues),3,DTERRORLOG);

	if ($getValues != null) {
		$downloadarray = array();
		$i = 0;
		$downloadarray[$i]['SlNo'] = 'Sl No';
		$downloadarray[$i]['gotra'] = 'Gotra';
		$downloadarray[$i]['gender'] = 'Gender';
		$downloadarray[$i]['nakshatra'] = 'Nakshatra';
		$downloadarray[$i]['rashi'] = 'Rashi';
		$downloadarray[$i]['fullname'] = 'Full Name';
		$i++;

		foreach ($getValues as $print) {
			$downloadarray[$i]['SlNo'] = $i;
			$downloadarray[$i]['gotra'] = $print['gotra'];
			$downloadarray[$i]['gender'] = $print['gender'];
			$downloadarray[$i]['nakshatra'] = $print['nakshatra'];
			$downloadarray[$i]['rashi'] = $print['rashi'];
			$downloadarray[$i]['fullname'] = $print['fullname'];
			$i++;
		}

		/* 	$today = date("F j, Y, g:i a"); 
			$output_file_name = 'Sankalpa_List_' . $eventID . "_" . $today . '.csv';
            error_log("\n @output_file_name:  $output_file_name", 3, DTERRORLOG); 
			 
	 		 ob_end_clean();
			 ob_clean();			 
		
			 header( "Content-Description: File Transfer");
			 header( "Content-Type: text/csv;charset=utf-8" );
			 header( "Content-Disposition: attachment;filename=\"$output_file_name\"" );
			 header("Pragma: no-cache");
			 header("Expires: 0");
					 
			 $fp= fopen('php://output', 'w');
             fputcsv($fp, $header);
			 
			 foreach ($downloadarray as $line) 
			 {
				fputcsv($fp, $line); 
			 }
			 fclose($fp); */
	}
	echo json_encode($downloadarray);
} else if ($action == "getCompleteData") {

	$getValues = $wpdb->get_results($whereAll, ARRAY_A);

	if ($getValues != null) {
		$downloadarray = array();
		$i = 0;
		$downloadarray[$i]['paidon'] = 'Paid On';
		$downloadarray[$i]['fullname'] = 'Full Name';
		$downloadarray[$i]['gender'] = 'Gender';
		$downloadarray[$i]['address'] = 'Address';
		$downloadarray[$i]['city'] = 'City';
		$downloadarray[$i]['state'] = 'State';
		$downloadarray[$i]['pincode'] = 'Pincode';
		$downloadarray[$i]['email'] = 'Email';
		$downloadarray[$i]['mobile'] = 'Mobile';
		$downloadarray[$i]['gotra'] = 'Gotra';
		$downloadarray[$i]['nakshatra'] = 'Nakshatra';
		$downloadarray[$i]['rashi'] = 'Rashi';
		$downloadarray[$i]['purpose'] = 'Category';
		$downloadarray[$i]['sub_purpose'] = 'Seva';
		$downloadarray[$i]['sankalpa'] = 'Sankalpa';
		$downloadarray[$i]['status'] = 'Payment Status';
		$downloadarray[$i]['bank'] = 'Bank';
		$downloadarray[$i]['rz_amount'] = 'Amount';
		$downloadarray[$i]['method'] = 'Payment Method';
		$downloadarray[$i]['transid'] = 'VDS Id';
		$downloadarray[$i]['attending'] = 'Attending';
		$downloadarray[$i]['courierPrasadam'] = 'Courier Prasadam';
		$downloadarray[$i]['dateofbirth'] = 'Date of Birth/Anniversary';
		$downloadarray[$i]['receipt_no'] = 'Receipt No';
		$downloadarray[$i]['eventDate'] = 'Event Date';
		$downloadarray[$i]['eventID'] = 'Event ID';
		$downloadarray[$i]['sankalpaReason'] = 'Sankalpa Reason';
		$i++;

		foreach ($getValues as $print) {
			$downloadarray[$i]['paidon'] = $print['post_date'];
			$downloadarray[$i]['fullname'] = $print['fullname'];
			$downloadarray[$i]['gender'] = $print['gender'];
			$downloadarray[$i]['address'] = '"' . $print['address'] . '"';
			$downloadarray[$i]['city'] = "\"" . $print['city'] . "\"";
			$downloadarray[$i]['state'] = $print['state'];
			$downloadarray[$i]['pincode'] = $print['pincode'];
			$downloadarray[$i]['email'] = $print['email'];
			$downloadarray[$i]['mobile'] = $print['mobile'];
			$downloadarray[$i]['gotra'] = $print['gotra'];
			$downloadarray[$i]['nakshatra'] = $print['nakshatra'];
			$downloadarray[$i]['rashi'] = $print['rashi'];
			$downloadarray[$i]['purpose'] = $print['purpose'];
			$downloadarray[$i]['sub_purpose'] = $print['sub_purpose'];

			$sankalpaOptionID = $print['sankalpaOptionID'];
			error_log("\n @sankalpaOptionID :  $sankalpaOptionID", 3, DTERRORLOG);
			if ($sankalpaOptionID != "") {
				if ($sankalpaOptionID === "Other Amount") {
					// If sankalpaOptionID is 'other amount', set description directly
					$sankalpaDescription = $print['sankalpaOptionIDDescription'];
				} else {
					$additionalsankalpa = $print['additionalsankalpa'];
					if ($additionalsankalpa == 1) {
						$query = $wpdb->prepare("SELECT sankalpa_description from " . $wpdb->prefix . "vds_sankalpa_additional WHERE `id` = '%d';", $sankalpaOptionID);
						$sankalpaDescription = $wpdb->get_results($query, ARRAY_N);
					} elseif ($additionalsankalpa == 2) {
						$query = $wpdb->prepare("SELECT sankalpa_description from " . $wpdb->prefix . "vds_seva_special WHERE `id` = '%d';", $sankalpaOptionID);
						$sankalpaDescription = $wpdb->get_results($query, ARRAY_N);
					}
				}
			} else {
				$additionalsankalpa = $print['additionalsankalpa'];
				if ($additionalsankalpa == 0) {
					$sankalpaDescription = "";
				}
			}
			error_log("\n @sankalpaDescription :  $sankalpaDescription", 3, DTERRORLOG);
			if ($sankalpaDescription != null) {
				$customizedSankalpa = $sankalpaDescription;
				$downloadarray[$i]['sankalpa'] = $customizedSankalpa;
			} else {
				$downloadarray[$i]['sankalpa'] = "";
			}
			error_log("\n @customizedSankalpa :" .  json_encode($customizedSankalpa), 3, DTERRORLOG);
			$downloadarray[$i]['status'] = $print['status'];
			$downloadarray[$i]['bank'] = $print['bank'];
			$downloadarray[$i]['rz_amount'] = $print['amount'] / 100;
			$downloadarray[$i]['method'] = $print['method'];
			$downloadarray[$i]['transid'] = $print['transid'];
			$downloadarray[$i]['attending'] = $print['attending'];
			$downloadarray[$i]['courierPrasadam'] = $print['courierPrasadam'];
			$downloadarray[$i]['dateofbirth'] = $print['dateofbirth'];
			$downloadarray[$i]['receipt_no'] = $print['receipt_no'];
			$downloadarray[$i]['eventDate'] = $print['eventDate'];
			$downloadarray[$i]['eventID'] = $print['eventID'];
			$downloadarray[$i]['sankalpaReason'] = $print['sankalpaReason'];
			$i++;
		}
	}
	//error_log("\n All Details : ".json_encode($downloadarray),3,DTERRORLOG);
	echo json_encode($downloadarray);
} else if ($action == "removeCartItem") {
} else if ($action == "addItem") {
}
