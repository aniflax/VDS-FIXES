<?php
if ( !defined("ABSPATH") ) {
    $__root = __DIR__;
    for ( $__i = 0; $__i < 8; $__i++ ) {
        $__root = dirname($__root);
        if ( is_file($__root . "/wp-config.php") ) { define("ABSPATH", $__root . "/"); break; }
    }
}
if ( !defined("DB_HOST") && defined("ABSPATH") && is_file(ABSPATH . "wp-config.php") ) {
    require_once ABSPATH . "wp-config.php";
}


/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/../../../../../');

//require_once ABSPATH.'wp-config.php';

global $wpdb;
$id = rawurldecode($_GET['id']);
$state = rawurldecode($_GET['state']);
$employees = rawurldecode($_GET['employees']);

$data = json_decode(rawurldecode(stripslashes($_GET['employees'])), true);

if (!defined('DTERRORLOG')) define('DTERRORLOG', ABSPATH . '../logs/serverprocessingdatatable.log');

header('Content-Type: application/json; charset=utf-8');

if ($data != null) {
	error_log("\n serverprocessingseva.php data: " . print_r($data, true), 3, DTERRORLOG);
	$state = $data['state'];
	$eventID = $data['eventID'];
	$fiscalyear = $data['fiscalyear'];
	$status = $data['status'];
	$datefrom = $data['datefrom'];
	$dateto = $data['dateto'];
	$delta = $data['delta'];

	if (!empty($data['isgst'])) {
		$isgst = $data['isgst'];
	}

	if (!empty($data['gstPurpose'])) {
		$gstPurpose = $data['gstPurpose'];
	}

	if (!empty($data['emailstatus'])) {
		$emailstatus = $data['emailstatus'];
	}

	if (!empty($data['statesResponsible'])) {
		$statesResponsible = $data['statesResponsible'];
	}

	if (!empty($data['sevaType'])) {
		$sevaType = $data['sevaType'];
	}

	if (!empty($data['purpose'])) {
		$purpose = $data['purpose'];
	}

	if (!empty($data['responsiblePurpose'])) {
		$responsiblePurpose = $data['responsiblePurpose'];
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

	if (!empty($data['cashCollecterID'])) {
		$cashCollecterID = $data['cashCollecterID'];
	}

	if (!empty($data['paymenttype'])) {
		$paymenttype = $data['paymenttype'];
	}
}
// DB table to use
$table = 'wp_vds_contrib';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
	array('db' => 'postid', 'dt' => 0),
	array('db' => 'fullname',  'dt' => 1),
	array('db' => 'purpose',   'dt' => 2),
	array('db' => 'sub_purpose',   'dt' => 3),
	array(
		'db'        => 'post_date',
		'dt'        => 4,
		'formatter' => function ($d, $row) {
			return date('jS M y', strtotime($d));
		}
	),
	array(
		'db' => 'amount',
		'dt' => 5,
		'formatter' => function ($d, $row) {
			return ($d / 100);
		}
	),
	array('db' => 'paymenttype',  'dt' => 6),
	array('db' => 'bank',  'dt' => 7),
	array('db' => 'status',  'dt' => 8),
	array('db' => 'eventID',  'dt' => 9),
	array('db' => 'eventcity',  'dt' => 10),
	array('db' => 'eventstate',  'dt' => 11),
	array('db' => 'email_status',  'dt' => 12),
	array('db' => 'sms_id',  'dt' => 13),
	array('db' => 'mobile',  'dt' => 14),
	array('db' => 'city',  'dt' => 15),
	array('db' => 'state',  'dt' => 16),
	array('db' => 'settlement_id',  'dt' => 17),
	array('db' => 'razorpay_payment_id',  'dt' => 18),
	array(
		'db' => 'credit_amount',
		'dt' => 19,
		'formatter' => function ($d, $row) {
			return ($d / 100);
		}
	),
	array(
		'db' => 'fee',
		'dt' => 20,
		'formatter' => function ($d, $row) {
			return ($d / 100);
		}
	),
	array('db' => 'receipt_no',  'dt' => 21),
	array('db' => 'chqddno',  'dt' => 22),
	array('db' => 'email',  'dt' => 23),
	array('db' => 'eventDate',  'dt' => 24),
	array('db' => 'cashCollectedBy',  'dt' => 25),
	array('db' => 'gst_event',  'dt' => 26),
	array('db' => 'knowHow',  'dt' => 27),
	array('db' => 'email',  'dt' => 28),
	array('db' => 'sms_status',  'dt' => 29),
	array('db' => 'sankalpaOptionID',  'dt' => 30),
	array('db' => 'rz_fee',  'dt' => 31),
	array('db' => 'gst_rate',  'dt' => 32),
	array('db' => 'cgst',  'dt' => 33),
	array('db' => 'sgst',  'dt' => 34),
	array('db' => 'igst',  'dt' => 35),
	array('db' => 'eventAt',  'dt' => 36),
	array('db' => 'gotra',  'dt' => 37),
	array('db' => 'rashi',  'dt' => 38),
	array('db' => 'nakshatra',  'dt' => 39),
	array('db' => 'attending',  'dt' => 40),
	array('db' => 'gender',  'dt' => 41),
);

// SQL server connection information
/* $sql_details = array(
	'user' => DB_USER,
	'pass' => DB_PASSWORD,
	'db'   => DB_NAME,
	'host' => DB_HOST
); */

$sql_details = array(
    "user" => defined("DB_USER") ? DB_USER : "vdsprod",
    "pass" => defined("DB_PASSWORD") ? DB_PASSWORD : "",
    "db"   => defined("DB_NAME") ? DB_NAME : "vdsprod",
    "host" => defined("DB_HOST") ? DB_HOST : "localhost"
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
//static function complex ( $request, $conn, $table, $primaryKey, $columns, $whereResult=null, $whereAll=null )
//SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
require('ssp.class.php');

$whereAll = " 1 = 1";

if ($status != null) {
	if ($status == '0') {
		$whereAll .= " AND status ='Not initiated'";
	} else if ($status == '1') {
		$whereAll .= " AND status ='Captured'";
	} else if ($status == '2') {
		$whereAll .= " AND status ='settled'";
	} else if ($status == '3') {
		$whereAll .= " AND status ='Failed'";
	} else if ($status == '4') {
		$whereAll .= " AND status IN ('Captured','settled')";
	} else if ($status == '5') {
		$whereAll .= " AND status ='Deleted'";
	} else {
		$whereAll .= " AND status IS NOT NULL";
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
	} else if ($fiscalyear == '2025-2026') {
		$whereAll .= " AND post_date > '2025-04-01' AND post_date <= '2026-04-01'";
	} else if ($fiscalyear == '2026-2027') {
		$whereAll .= " AND post_date > '2026-04-01' AND post_date <= '2027-04-01'";
	} else if ($fiscalyear == 'All') {
		//$whereAll .= " AND post_date > '2017-04-01' AND post_date <= '2020-03-30'";			
	}
}

if ($state != null) {
	$whereAll .= " AND eventstate='" . $state . "'";
}

if ($statesResponsible != null) {
	if ($statesResponsible == 'All States') {
		//do nothing as it will fetch all states by default
	} else {
		$whereAll .= " AND eventstate IN (" . $statesResponsible . ")";
	}
}

if ($eventID != null) {
	$searchForValue = ',';
	if (strpos($eventID, $searchForValue) !== false) {
		$whereAll .= " AND eventID IN (" . $eventID . ")";
	} else {
		$whereAll .= " AND eventID='" . $eventID . "'";
	}
}

if (!empty($isgst)) {
	if ($gstPurpose == 'Jyotish') {
		$whereAll .= " AND purpose IN ('Jyotish')";
	} else if ($gstPurpose == 'Vastu') {
		$whereAll .= " AND purpose IN ('Vastu')";
	} else if ($gstPurpose == 'Weddings') {
		$whereAll .= " AND purpose IN ('Weddings')";
	} else if ($gstPurpose == 'Gaushala') {
		$whereAll .= " AND purpose IN ('Gaushala')";
	} else if ($gstPurpose == 'All') {
		$whereAll .= " AND purpose IN ('Jyotish','Vastu','Weddings','Gaushala')";
	}
}

if (!empty($emailstatus)) {
	$whereAll .= " AND email_status='" . $emailstatus . "'";
}

if (!empty($purpose)) {
	$whereAll .= " AND purpose='" . $purpose . "'";
}

if (!empty($responsiblePurpose)) {
	if ($responsiblePurpose == '') {
		//do nothing as it will fetch all states by default
	} else {
		$whereAll .= " AND purpose IN (" . $responsiblePurpose . ")";
	}
}

if (!empty($sub_purpose)) {
	$whereAll .= " AND sub_purpose='" . $sub_purpose . "'";
}

if (!empty($event_date)) {
	$whereAll .= " AND eventDate='" . $event_date . "'";
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

if (!empty($cashCollecterID)) {
	$whereAll .= " AND cashCollecterID='" . $cashCollecterID . "'";
}

/*if (!empty($paymenttype)) {

	if ($paymenttype == 'cash') {
		$whereAll .= " AND paymenttype IN ('cash')";
	} else if ($paymenttype == 'card') {
		$whereAll .= " AND paymenttype IN ('card')";
	} else if ($paymenttype == 'chequedd') {
		$whereAll .= " AND paymenttype IN ('chequedd')";
	} else if ($paymenttype == 'upi') {
		$whereAll .= " AND paymenttype IN ('upi')";
	} else if ($paymenttype == 'Razorpay') {
		$whereAll .= " AND paymenttype IN ('Razorpay')";
	} else if ($paymenttype == 'All') {
		$whereAll .= " AND paymenttype IN ('cash','card','chequedd','upi','Razorpay')";
	} else if ($paymenttype == 'none') {
		$whereAll .= " AND paymenttype IS NULL";
	}
}*/

if (!empty($delta)) {
	if ($delta == 1) {
		$whereAll .= " AND eventID='" . $eventID . "' AND email NOT IN ";
		$whereAll .= "(SELECT email FROM wp_vds_live_api_results WHERE eventID='" . $eventID . "')";
	} else {
		//error_log("\n Delta is -1 Query : $whereAll",3,DTERRORLOG);
	}
}

$searchValue = $_GET['search']['value'];

if ($searchValue != null && $searchValue != "") {
	$s = "%" . $searchValue . "%";
	$whereAll .= " AND (`eventID` LIKE '" . $s . "' OR `postid` LIKE '" . $s . "' OR `fullname` LIKE '" . $s  . "' OR `mobile` LIKE '" . $s . "' OR `purpose` LIKE '" . $s . "' OR `sub_purpose` LIKE '" . $s . "' OR `eventDate` LIKE '" . $s . "' OR `paymenttype` LIKE '" . $s . "' OR `status` LIKE '" . $s . "' OR `eventvenue` LIKE '" . $s . "' OR `eventcity` LIKE '" . $s . "' OR `eventstate` LIKE '" . $s . "' OR `status` LIKE '" . $s . "' OR `receipt_no` LIKE '" . $s . "' OR `razorpay_payment_id` LIKE '" . $s . "' OR `settlement_id` LIKE '" . $s . "')";
}

//error_log("\n serverprocessingseva.php $whereAll", 3, DTERRORLOG);

echo json_encode(
	SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, null, $whereAll)
);
