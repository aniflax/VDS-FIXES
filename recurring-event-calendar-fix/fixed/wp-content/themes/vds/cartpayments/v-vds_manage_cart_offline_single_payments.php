<?php

function vds_manage_cart_offline_single_payments($atts)
{
	global $wpdb;
	global $ultimatemember;

	if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/../../../../../');
	require_once ABSPATH . 'wp-config.php';

	um_fetch_user(get_current_user_id());
	$userid = get_current_user_id();
	$userdetails = get_user_meta($userid);
	$display_name = um_user('display_name');
	$user_info = get_userdata($userid);
	$userashram = $userdetails['ashram'][0];
	$userashramdept = $userdetails['ashram_department'][0];
	$purpose = $userdetails['purpose'][0];
	$userpurpose = $userdetails['purpose'][0];
	if (!empty($purpose)) {
		$purposeArray = explode(",", str_replace("'", '', $purpose));
	}
	$sub_purpose = $userdetails['sub_purpose'][0];
	$additionalStates = sanitize_text_field($userdetails['additionalStates'][0]);
	if (!empty($additionalStates)) {
		$additionalStatesArray = explode(",", str_replace("'", '', $additionalStates));
	}

	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		$userName = $user->display_name;
		$role = (array) $user->roles;
		$userRole = $role[0];
		if (in_array('administrator', (array) $user->roles) || in_array('um_finance', (array) $user->roles) || in_array('national_coordinator', (array) $user->roles)) {
			$generateRazorpayReceipt = 1;
		} else {
			$generateRazorpayReceipt = 0;
		}
	} else {
		$userRole = 'Organiser';
	}

	if (date('m') <= 3) { //Upto March 
		$financial_year = (date('Y') - 1) . '-' . date('Y');
	} else { //After March
		$financial_year = date('Y') . '-' . (date('Y') + 1);
	}
	$q_fiscalyear = $financial_year;
	$q_fiscalyear = 'Upcoming';
	$testingmode = 0;

	if ($testingmode == 1) {
		echo '<pre>';
		echo "<h4 align='center'>Testing Mode is ON. All payments are simulated and receipts are TEST receipts.</h4>";
		echo '</pre>';
		echo "<script>alert('Testing Mode is ON. All payments are simulated and receipts are TEST receipts. Hence donot make any actual payments at this time.');</script>";
	} else {
	}

	if (!wp_verify_nonce($_POST['amount_seva'], 'amount_eseva')) {
?>
		<html lang="en">
		<head>
			<meta charset="utf-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta content="width=device-width, initial-scale=1" name="viewport" />
			<meta name="Sankalpa" content="Sankalpa" />
			<meta name="Vaidic Events" content="vaidicpujas.org" />
			<title>Offline Payments</title>
			<!-- google font -->
			<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet" type="text/css" />
			<!-- icons -->
			<link href="/wp-content/themes/vds/assets/asset/fonts/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/fonts/material-design-icons/material-icon.css" rel="stylesheet" type="text/css" />
			<!--bootstrap -->
			<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>

			<!-- data tables -->
			<link href="/wp-content/themes/vds/assets/asset/plugins/datatables/plugins/bootstrap/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
			<!-- Material Design Lite CSS -->
			<link rel="stylesheet" href="/wp-content/themes/vds/assets/asset/plugins/material/material.min.css">
			<link rel="stylesheet" href="/wp-content/themes/vds/assets/asset/css/material_style.css">
			<!-- Theme Styles -->
			<link href="/wp-content/themes/vds/assets/asset/css/theme/light/theme_style.css" rel="stylesheet" id="rt_style_components" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/css/theme/light/style.css" rel="stylesheet" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/css/plugins.min.css" rel="stylesheet" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/css/responsive.css" rel="stylesheet" type="text/css" />
			<link href="/wp-content/themes/vds/assets/asset/css/theme/light/theme-color.css" rel="stylesheet" type="text/css" />
			<!-- favicon -->
			<link rel="shortcut icon" href="/wp-content/themes/vds/assets/asset/img/favicon.ico" />

			<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.22/pdfmake.min.js"></script>
			<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

			
			
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js"></script>

			<!--<link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
			<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>-->
			<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
			<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
			<link rel="stylesheet" href="style.css">
			<link href="/wp-content/themes/vds/css/vdsFormStyle10.css" rel="stylesheet">

			<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.all.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>

			<style>
				td.details-control {
					text-align: center;
					color: forestgreen;
					cursor: pointer;
				}

				tr.shown td.details-control {
					text-align: center;
					color: red;
				}

				input,
				label {
					display: block;
				}

				#passwordHelpBlock {
					font-size: x-small;
					display: block;
				}
			</style>

		</head>
		<!-- END HEAD -->

		<body class="page-header-fixed sidemenu-closed-hidelogo page-content-white page-md header-white white-sidebar-color logo-indigo">

			<div class="page-wrapper">
				<!-- start header -->
				<?php if (($userid != null) && ($userid != '0')) { ?>
					<div class="page-header navbar navbar-default-top" style="background-color: white; align:left;">
						<div class="page-header-inner " style="vertical-align: top">
							
							<div>
								<ul class="nav navbar-nav navbar-left in" style="margin:0px;">
									<li><a href="#" class="menu-toggler sidebar-toggler"><i class="icon-menu"></i></a></li>
								</ul>

								<!-- start mobile menu -->
								<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
									<span></span>
								</a>
							</div>
							<!-- end mobile menu -->
							<div class="top-menu" style="display: inline-block; vertical-align: top;">
								<ul class="nav navbar-nav pull-right" style="margin:0px;">
									<!-- <li><a href="javascript:;" class="fullscreen-btn"><i class="fa fa-arrows-alt"></i></a></li>
                    	<!-- start cart language menu -->
									<?php
									$tablename = $wpdb->prefix . 'vds_users_cart';
									$query = "SELECT * FROM $tablename where user_id = '" . $userid . "' ORDER BY created_on DESC;";
									$sql = $wpdb->prepare($query, ARRAY_A);
									$allCart = $wpdb->get_results($sql, ARRAY_A);
									$allCartCount = $wpdb->num_rows;
									//echo "<br> user id : " . $userid; 
									?>
									<li class="dropdown dropdown-extended dropdown-inbox" id="header_inbox_bar">
										<a href="javascript:;" id="cart_at_top" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
											<i style='font-size:14px' class="fa fa-cart-arrow-down"></i>
											<span class="badge headerBadgeColor2" id="cart_count"> <?php echo $allCartCount ?> </span>
										</a>
										<ul class="dropdown-menu">
											<li class="external">
												<h3><span class="bold">Sankalpa</span></h3>
												<span class="notification-label cyan-bgcolor" id="li_cart_count"><?php echo $allCartCount ?></span>
											</li>
											<li>
												<ul class="dropdown-menu-list small-slimscroll-style" data-handle-color="#637283">

													<li id="idvalues">

														<?php
														foreach ($allCart as $print) {
															echo '<a href="#">';
															echo '<span class="photo">';
															echo '<img src="/wp-content/themes/vds/assets/img/prof/prof10.jpg" class="img-circle" alt=""> </span>';
															echo '<span class="subject">';
															echo '<span class="from">' . $print['event_id'] . '</span>';
															echo '<span class="time" id="cart_sankalpa">' . 'No. of Sankalpa :' . $print['quantity'] . '</span>';
															echo '</span>';
															echo '<span class="message">' . $print['event_name'] . '</span>';
															echo '<span class="message">' . $print['created_on'] . '</span>';
															echo '</a>';
														} //close of the foreach loop  
														?>

													</li>

												</ul>
												<div class="dropdown-menu-footer">
													<a href="#"> Goto Cart </a>
												</div>
											</li>
										</ul>
									</li>

									<!-- end cart language menu -->
									<!-- start notification dropdown -->
									<li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
										<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
											<i style='font-size:14px' class="fa fa-bell-o"></i>
											<span class="badge headerBadgeColor1"> 1 </span>
										</a>
										<ul class="dropdown-menu">
											<li class="external">
												<h3><span class="bold">Notifications</span></h3>
												<span class="notification-label purple-bgcolor">New 1</span>
											</li>
											<li>
												<ul class="dropdown-menu-list small-slimscroll-style" data-handle-color="#637283">
													<li>
														<a href="javascript:;">
															<span class="time">just now</span>
															<span class="details">
																<span class="notification-icon circle deepPink-bgcolor"><i class="fa fa-check"></i></span> Use the new offline cart system to manage counter payments ! </span>
														</a>
													</li>
												</ul>
												<div class="dropdown-menu-footer">
													<a href="javascript:void(0)"> All notifications </a>
												</div>
											</li>
										</ul>
									</li>
									<!-- end notification dropdown -->

									<!-- start manage user dropdown -->
									<li class="dropdown dropdown-user">
										<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
											<img alt="" class="img-circle " src="/wp-content/uploads/2020/08/VDS-Devi.jpg" />
											<span class="username username-hide-on-mobile"> <?php echo um_user('display_name'); ?> </span>
											<i class="fa fa-angle-down"></i>
										</a>
										<ul class="dropdown-menu dropdown-menu-default">
											<li>
												<a href="/update_user/" target="_blank">
													<i class="icon-user"></i> Profile </a>
											</li>
											<li>
												<a href="#">
													<i class="icon-settings"></i> Settings
												</a>
											</li>
											<li>
												<a href="#">
													<i class="icon-directions"></i> Help
												</a>
											</li>
											<li class="divider"> </li>
											<li>
												<a href="/logout">
													<i class="icon-logout"></i> Logout </a>
											</li>
										</ul>
									</li>
									<!-- end manage user dropdown -->

								</ul>
							</div>
						</div>
					</div>
					<!-- end header -->

					<!-- start page container -->
					<div class="page-container">
						<!-- start sidebar menu -->
						<div class="sidebar-container">
							<div class="sidemenu-container navbar-collapse collapse fixed-menu">
								<div id="remove-scroll">
									<ul class="sidemenu  page-header-fixed" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
										<li class="sidebar-toggler-wrapper hide">
											<div class="sidebar-toggler">
												<span></span>
											</div>
										</li>
										<li class="sidebar-user-panel">
											<div class="user-panel">
												<div class="pull-left image">
													<img src="/wp-content/uploads/2020/08/VDS-Devi.jpg" class="img-circle user-img-circle" alt="User Image" />
												</div>
												<div class="pull-left info">
													<p><?php echo um_user('display_name'); ?></p>
													<a href="/logout"><i class="fa fa-circle user-online"></i><span class="txtOnline"> Logout</span></a>
												</div>

											</div>
										</li>

										<li class="nav-item">
											<a href="/cashier/" class="nav-link nav-toggle">
												<i class="material-icons">dashboard</i>
												<span class="title">Dashboard</span>
												<span class="selected"></span>
											</a>
										</li>

										<li class="nav-item active open">
											<a href="#" class="nav-link nav-toggle">
												<i class="material-icons">face</i>
												<span class="title">My Activities</span>
												<span class="arrow open"></span>
											</a>
											<ul class="sub-menu">
												<li class="nav-item">
													<a href="/managecashierseva/" class="nav-link"> <i class="fa fa-empire"></i> My Events
													</a>
												</li>

												<li class="nav-item">
													<a href="/cashierreports/" class="nav-link"><i class="fa fa-clock-o"></i> Daily Reports
													</a>
												</li>

												<li class="nav-item">
													<a href="/getpaymentdetails/" class="nav-link"> <i class="fa fa-ticket"></i> Provide Coupon
													</a>
												</li>

												<li class="nav-item active open">
													<a href="/offlinecart/" class="nav-link"> <i class="fa fa-th-list"></i> Offline Payments
														<span class="selected"></span>
													</a>
												</li>
											</ul>
										</li>

										<li class="nav-item">
											<a href="#" class="nav-link nav-toggle">
												<i class="material-icons">face</i>
												<span class="title">Events List</span>
												<span class="arrow open"></span>
											</a>
											<ul class="sub-menu">
												<li class="nav-item active">
													<a href="/eventshome/" class="nav-link "> <span class="title">All Events</span>
													</a>
												</li>
											</ul>
										</li>

										<li class="nav-item">
											<a href="#" class="nav-link nav-toggle"><i class="fa fa-cogs"></i>
												<span class="title">Settings</span><span class="arrow"></span></a>
											<ul class="sub-menu">

												<li class="nav-item">
													<a href="/update_user/" class="nav-link"><i class="fa fa-address-card-o"></i> My Profile
													</a>
												</li>

											</ul>
										</li>

									</ul>
								</div>
							</div>
						</div>
						<!-- end sidebar menu -->
					<?php } else { ?>
						<div class="sidebar-container" style="background-color:FFFFFF;">
							<!--<div class="sidemenu-container navbar-collapse collapse fixed-menu">-->
							<div class="sidemenu-container" style="background-color:FFFFFF;">
								<div id="remove-scroll">
									<div style="background-color:'#FFFFFF'; width: 95%; left: 5%; ">
										<?php echo do_shortcode("[ultimatemember form_id='267870']"); ?>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<!-- start page content -->

					<div class="page-content-wrapper">
						<div class="page-content">
							<div class="page-bar">
								<div class="page-title-breadcrumb">
									<div class=" pull-left">
										<div class="page-title">Offline Payments [Multi Registration]</div>
									</div>
									<ol class="breadcrumb page-breadcrumb pull-right">
										<li><i class="fa fa-home"></i>&nbsp;<a class="parent-item" href="/cashier/">Cashier Dashboard</a>&nbsp;<i class="fa fa-angle-right"></i>
										</li>
										<li class="active">Offline Payments</li>
									</ol>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="tabbable-line">
										<ul class="nav customtab nav-tabs" role="tablist">
											<li class="nav-item"><a href="#tab1" class="nav-link active" data-toggle="tab">Sankalpa Cart</a></li>
										</ul>
										<div class="tab-content">
											<div class="tab-pane active fontawesome-demo" id="tab1">
												<div class="form-style-10">
													<!-- <h3>Get User<span> - Assign to user</span></h3>-->
													<div id="searchUserSectin" class="section"><span><i style="font-size:14px" class="fa">&#xf002;</i></span>Search Donor by Past Payments </div>
													<div id="searchUserWrap" class="inner-wrap">
														<div class="row" id="searchUsers">
															<div class="col-md-4">
																<input type="text" id="search-mobile" class="form-control usersearch" placeholder="Mobile Number">
																<small id="passwordHelpBlock" class="form-text text-muted"><i class="fa fa-info-circle"></i> Enter Mobile Number to search and further filter by the Name. Complete Numner is not required.</small>
															</div>
															<div class="col-md-4" id="search-name-wrap" style="display:none;">
																<input type="text" id="search-name" class="form-control usersearch" placeholder="Name">
																<small id="passwordHelpBlock" class="form-text text-muted"><i class="fa fa-info-circle"></i> Enter Name to fetch the registered user or to search from our earlier payment. Press Tab or Enter and wait to get the Details </small>
															</div>
															<script>
															// Hide name search initially, show after mobile search returns results
															$(document).ready(function() {
																$('#search-name-wrap').hide();
																$('#search-mobile').on('blur', function() {
																	var mobile = $(this).val().trim();
																	var $userList = $('#userList');
																	if(mobile.length > 0) {
																		$.post('/wp-content/themes/vds/api/user_search.php', {
																			token: 'k9CQ9x!7NQv66PJ',
																			process: 'get_registered_users',
																			mobile: mobile,
																			name: ''
																		}, function(response) {
																			var userList = JSON.parse(response.trim());
																			//$userList.empty();
																			if(userList.length > 0) {
																				$('#search-name-wrap').show();
																				// Existing logic to append users (if any)
																			} else {
																				$('#search-name-wrap').hide();
																				Swal.fire({
																					icon: 'info',
																					title: 'No Donors Found',
																					text: 'No donors found for this mobile number.',
																					confirmButtonColor: '#3085d6',
																					customClass: {popup: 'swal2-classic'}
																				});
																			}
																		});
																	} else {
																		$('#search-name-wrap').show();
																		$userList.empty();
																	}
																});
															});
															</script>
															<!--
															<div class="col-md-4">
																<input type="text" id="search-email"  class="form-control usersearch" placeholder = "Email" >
																<small id="passwordHelpBlock" class="form-text text-muted"><i class="fa fa-info-circle"></i> Enter eMail id to search along with the Name entered. Complete eMail id is not required </small>
															</div>
															-->
															<table id="searchResults" class="table table-hover col-md-12">
																<thead>
																	<tr>
																		<th scope="col">Name</th>
																		<th scope="col">email</th>
																		<th scope="col">User ID</th>
																		<th scope="col">Other Details</th>
																	</tr>
																</thead>
																<tbody id="userList">
																</tbody>
															</table>
														</div>
													</div>

												</div>

												<div class="form-style-10">
													<!--<form id='edonor' action=" " method="post" novalidate="novalidate" _lpchecked="1">
														<!-- Capturing host details-->
													<div id="hostDetails" class="section"><span><i style="font-size:14px" class="fa">&#xf2bc;</i></span>Step 1 : User/Donor Details </div>
													<div id="hostDetailsWrap" class="inner-wrap">

														<div class="row">
															<div class="col-md-1 vds_label" title="(required - receipt will be sent to this email)">Email</div>
															<div class="col-md-3"><input class="vds100" type="email" maxlength="50" name="email" id="email" title="(required - receipt will be sent to this email)" value="" required></div>

															<div class="col-md-1 vds_label" title="hostid">ID</div>
															<div class="col-md-3"><input class="vds100" type="text" name="hostid" id="hostid" value="" readonly></div>

															<div class="col-md-1 vds_label" title="userlogin">Username</div>
															<div class="col-md-3"><input class="vds100" type="text" name="username" id="username" value="" readonly></div>
														</div>
														<div class="row">

															<div class="col-md-1 vds_label">Full Name</div>
															<div class="col-md-3"><input type="text" maxlength="80" name="fullname" id="fullname" value="" class="vds100" required></div>

															<div class="col-md-1 vds_label" title="Gender">Gender*</div>
															<div class="col-md-3">
																<select name="gender" class="vds100" id="gender" aria-hidden="true">
																	<option value="">--Select Gender--</option>;
																	<option value="Male">Male</option>
																	<option value="Female">Female</option>
																	<option value="Others">Others</option>
																</select>
															</div>

															<div class="col-md-1 vds_label" title="(Acknowledgement will be sent to this sms)">Mobile</div>
															<div class="col-md-3"><input title="(Acknowledgement will be sent to this sms)" class="vds100" type="text" name="mobile" id="mobile" value="" required></div>
														</div>
														<div class="row">
															<div class="col-md-1 vds_label">Address</div>
															<div class="col-md-11"><input class="vds100" type="text" maxlength="100" name="address" id="address" value=""></div>
														</div>
														<div class="row">

															<div class="col-md-1 vds_label" title="City">City</div>
															<div class="col-md-3"><input class="vds100" type="text" name="city" maxlength="30" id="city" value=""></div>

															<div class="col-md-1 vds_label" title="Select the State">State</div>
															<div class="col-md-3">
																<select class="vds100" name="state" id="state" aria-hidden="true">
																	<?php
																	$states = $wpdb->get_results("SELECT state from " . $wpdb->prefix . "vds_states", ARRAY_N);
																	$state = $userdetails['state'][0];
																	$state = '';
																	echo '<option value="">' . ' ' . '</option>';
																	foreach ($states as $stateval) {
																		$selected = '';
																		if ($state == $stateval[0]) $selected = 'selected=""';
																		echo '<option value="' . $stateval[0] . '"' . $selected . '>' . $stateval[0] . '</option>';
																	}
																	?>
																</select>
															</div>

															<div class="col-md-1 vds_label" title="Pin Code">Pin Code</div>
															<div class="col-md-3"><input type="text" class="vds100" name="pincode" maxlength="10" id="pincode" value=""></div>

														</div>

														<div class="row vds_row">
															<div class="col-md-1 vds_label" title="Gotra">Gotra</div>
															<div class="col-md-3"><input type="text" class="vds100" name='gotra' id='gotra' placeholder="Enter your Gotra"></div>
															<div class="col-md-1 vds_label" title="Nakshatra">Nakshatra</div>
															<div class="col-md-3">
																<select name="nakshatra" class="vds100" id="nakshatra" aria-hidden="true">
																	<option value="">--Select Nakshatra--</option>;
																	<option value="Aswini">Aswini</option>
																	<option value="Bharani">Bharani</option>
																	<option value="Krithika">Krithika</option>
																	<option value="Rohini">Rohini</option>
																	<option value="Mrigashirsha">Mrigashirsha</option>
																	<option value="Ardra">Ardra</option>
																	<option value="Punarvasu">Punarvasu</option>
																	<option value="Pushya">Pushya</option>
																	<option value="Ashlesha">Ashlesha</option>
																	<option value="Magha">Magha</option>
																	<option value="Purva Phalguni">Purva Phalguni</option>
																	<option value="Uttara Phalguni">Uttara Phalguni</option>
																	<option value="Hasta">Hasta</option>
																	<option value="Chitra">Chitra</option>
																	<option value="Swati">Swati</option>
																	<option value="Vishakha">Vishakha</option>
																	<option value="Anuradha">Anuradha</option>
																	<option value="Jyeshtha">Jyeshtha</option>
																	<option value="Mula">Mula</option>
																	<option value="Purva Ashadha">Purva Ashadha</option>
																	<option value="Uttara Ashadha">Uttara Ashadha</option>
																	<option value="Shravana">Shravana</option>
																	<option value="Dhanishtha">Dhanishtha</option>
																	<option value="Shatabhisha">Shatabhisha</option>
																	<option value="Purva Bhadrapada">Purva Bhadrapada</option>
																	<option value="Uttara Bhadrapada">Uttara Bhadrapada</option>
																	<option value="Revati">Revati</option>
																</select>
															</div>

															<div class="col-md-1 vds_label" title="Rashi">Rashi</div>
															<div class="col-md-3">
																<select name="rashi" class="vds100" id="rashi" aria-hidden="true">
																	<option value="">--Select Rashi--</option>;
																	<option value="Mesh">Mesh (Aries)</option>
																	<option value="Vrushabh">Vrushabh (Taurus)</option>
																	<option value="Mithuna">Mithuna (Gemini)</option>
																	<option value="Karka">Karka (Cancer)</option>
																	<option value="Simha">Simha (Leo)</option>
																	<option value="Kanya">Kanya (Virgo)</option>
																	<option value="Tula">Tula (Libra)</option>
																	<option value="Vrischika">Vrischika (Scorpio)</option>
																	<option value="Dhanur">Dhanur (Sagittarius)</option>
																	<option value="Makara">Makara (Capricorn)</option>
																	<option value="Kumbha">Kumbha (Aquarius)</option>
																	<option value="Meena">Meena (Pisces)</option>
																</select>
															</div>
														</div>

													</div>
													<!--<div class="row vds_row">
															  <div class="col-md-1" >&nbsp;</div>
															  <div class="col-md-4"><input id="createuserbutton"  type="submit" value="Create User"></div>
														</div>
														
													<!--</form>-->
												</div> <!-- close of div form style 10 of get users -->
												<!--- GETTING USERS ENDS HERE --->

												<!--- GET EVENTS STARTS HERE --->
												<div class="form-style-10">
													<!-- Capturing host details-->
													<div id="eventDetails" class="section"><span><i style="font-size:14px" class="fa">&#xf03a;</i></span>Step 2 : Select Events to the User</div>
													<div class="row">
														<div class="col-md-12">
															<div class="card card-box">
																<div class="card-head">
																	<header>Search for Events</header>
																	<div class="tools">
																		<a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
																		<a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
																		<a class="t-close btn-color fa fa-times" href="javascript:;"></a>
																	</div>
																</div>
																<div class="card-body ">
																	<div class="inner-wrap">

																		<div class="row vds_row">
																			<div class="col-md-1 vds_label" title="Select the State">Duration</div>
																			<div class="col-md-3">
																				<select style="width: 250px;" type="select" name="q_fiscalyear" id="q_fiscalyear">
																					<option value="" <?php echo ($q_fiscalyear == '') ? "selected" : ""; ?>></option>
																					<option value="Upcoming" <?php echo ($q_fiscalyear == 'Upcoming') ? "selected" : ""; ?>>Upcoming Approved Events</option>
																					<option value="CustomDates" <?php echo ($q_fiscalyear == 'CustomDates') ? "selected" : ""; ?>>Custom Range</option>
																				</select>
																			</div>

																			<div class="col-md-1 vds_label" title="sevaTypeCategory">Category</div>
																			<div class="col-md-3">
																				<select style="width: 250px;" name="purpose" id="purpose" aria-hidden="true">
																					<?php $categories = $wpdb->get_results("SELECT DISTINCT `purpose` FROM {$wpdb->prefix}vds_seva where `type` = 'samoohik' AND enabled='1' order by `sub_purpose`;", ARRAY_A);
																					echo '<option value=""></option>';
																					foreach ($categories as $category) {
																						$selected = '';
																						if ($userpurpose == $category['purpose']) $selected = 'selected';
																						echo '<option value="' . $category['purpose'] . '" ' . $selected . '>' . $category['purpose'] . '</option>';
																					} ?>
																				</select>
																			</div>

																			<div class="col-md-1 vds_label" title="sevaTypePurpose">Sub Category</div>
																			<div class="col-md-3">
																				<select style="width: 250px;" name="sub_purpose_type" id="sub_purpose_type" aria-hidden="true">
																					<?php
																					$categories = $wpdb->get_results("SELECT DISTINCT `id`,`sub_purpose` FROM {$wpdb->prefix}vds_seva where `purpose` = '" . $contrib['purpose'][0] . "' AND enabled='1' order by `sub_purpose`;", ARRAY_A);
																					foreach ($categories as $category) {
																						//$selected = ($category['id'] == $contrib['sevaid'][0]) ? 'selected' : ''; 
																						echo '<option value="' . $category['sub_purpose'] . '" >' . $category['sub_purpose'] . '</option>';
																					}
																					?>
																				</select>
																			</div>

																		</div>

																		<div id="divcustomDates">
																			<div class="row vds_row">
																				<div class="col-md-1 vds_label" title="Events From Date">Events From</div>
																				<div class="col-md-3">
																					<input title="Select the From Date" readonly style="width: 250px;" type="text" id="datefrom" name="datefrom" value="<?php echo get_query_var('datefrom'); ?>">
																				</div>
																				<div class="col-md-1 vds_label" title="Events To Date">Events To</div>
																				<div class="col-md-3">
																					<input title="Select the To Date" readonly style="width: 250px;" type="text" id="dateto" name="dateto" value="<?php echo get_query_var('dateto'); ?>">
																				</div>
																			</div>
																		</div>


																		<div class="row vds_row">
																			<div class="col-md-1 vds_label">Event ID</div>
																			<div class="col-md-3">
																				<input style="width: 250px;" type="text" id="q_eventID" name="q_eventID" value='<?php echo get_query_var('q_eventID'); ?>'> </input>
																			</div>

																			<div class="col-md-1 vds_label" title="Select the State">Event State</div>
																			<div class="col-md-3">
																				<select style="width: 250px;" name="eventState" id="eventState" aria-hidden="true">
																					<?php
																					$states = $wpdb->get_results("SELECT state from " . $wpdb->prefix . "vds_states", ARRAY_N);
																					$state = $userdetails['state'][0];
																					$state = '';
																					//echo '<option value="">'.'--All States--'.'</option>';																								
																					echo '<option value="">' . ' ' . '</option>';
																					foreach ($states as $stateval) {
																						$selected = '';
																						if ($state == $stateval[0]) $selected = 'selected=""';
																						echo '<option value="' . $stateval[0] . '"' . $selected . '>' . $stateval[0] . '</option>';
																					}
																					?>
																				</select>
																			</div>

																			<div class="col-md-1 vds_label" title="Select the Ashram">Ashram</div>
																			<div class="col-md-3">
																				<select style="width: 250px;" name="ashram" id="ashram" aria-hidden="true">
																					<option value=""></option>
																					<?php
																					//$userashram = "Bangalore Ashram";
																					$ashram = $userashram;
																					$ashrams = $wpdb->get_results("Select ashram from " . $wpdb->prefix . "vds_ashrams;", ARRAY_N);
																					foreach ($ashrams as $ashramval) {
																						$selected = '';
																						if ($ashram == $ashramval[0]) $selected = 'selected=""';
																						echo '<option value="' . $ashramval[0] . '"' . $selected . '>' . $ashramval[0] . '</option>';
																					}

																					?>
																				</select>
																			</div>
																		</div>

																	</div>
																	<div class="row vds_row">
																		<div class="col-md-1 vds_label" title="Select the Ashram"></div>
																		<button id='btnSearchEvents' type="button">Search Events</button>&nbsp;
																		<button id='btnResetSearch' type="button">Clear Search</button>
																	</div>
																</div>
															</div>

															<div class="card card-box">
																<div class="card-head">
																	<header>Details of Events</header>
																	<div class="tools">
																		<a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
																		<a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
																		<a class="t-close btn-color fa fa-times" href="javascript:;"></a>
																	</div>
																</div>

																<div class="card-body ">
																	<div id="userPayments" class="table-scrollable">
																		<table width="100%" class="table table-striped table-bordered table-hover table-checkable order-column valign-middle" id="eventsList">
																			<thead>
																				<tr>
																					<th></th><!--<input name="select_all" value="1" type="checkbox"></th>-->
																					<th>ID</th>
																					<th>Action</th>
																					<th>Name</th>
																					<th>Category</th>
																					<th>Sub-Category</th>
																					<th>Location </th>
																					<th>Date </th>
																					<th>Time</th>
																					<th>Venue</th>
																					<th>City</th>
																					<th>State</th>
																					<th>With</th>
																				</tr>
																			</thead>
																		</table>
																	</div>

																</div>
															</div>

														</div>
													</div>

												</div> <!-- Close of div get events -->
												<!--- GET EVENTS ENDS --->

												<!--- Cart ITEMS BASED UPON USER -->
												<?php
												$useridcart = $_GET['hostidcart'];
												$tablename = $wpdb->prefix . 'vds_users_cart';
												$query = "SELECT * FROM $tablename where user_id = '" . $useridcart . "' ORDER BY created_on DESC;";
												$sql = $wpdb->prepare($query, ARRAY_A);
												$allCart = $wpdb->get_results($sql, ARRAY_A);
												$allCartCount = $wpdb->num_rows;
												?>

												<div class="form-style-10">
													<div id="cartDetails" class="section"><span><i style="font-size:14px" class="fa">&#xf07a;</i></span>Step 3 : Verify Cart of the User</div>

													<div class="card card-box">
														<div class="card-head">
															<header>Details of Events</header>
															<div class="tools">
																<a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
																<a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
																<a class="t-close btn-color fa fa-times" href="javascript:;"></a>
															</div>
														</div>

														<div class="card-body ">

															<div id="userPayments" class="table-scrollable">
																<table width="100%" class="table table-striped table-bordered table-hover table-checkable order-column valign-middle" id="cartList">
																	<thead>
																		<tr>
																			<th>Action</th>
																			<th>Name</th>
																			<th>User ID</th>
																			<th>Event ID</th>
																			<th>Event Name</th>
																			<th>Sankalpa</th>
																			<th>Attending</th>
																			<th>Amount</th>
																			<th></th>
																			<th></th>
																			<th></th>
																		</tr>
																	</thead>
																	<tbody>
																	</tbody>
																	<tfoot>
																		<tr>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th></th>
																			<th style="text-align:right">Total:</th>
																		</tr>
																	</tfoot>
																</table>
															</div>

														</div>
													</div>

												</div>

												<!--- PAYMENT DETAILS BASED UPON USER -->
												<div class="form-style-10">
													<!-- Capturing host details-->
													<div id="paymentDetails" class="section"><span><i style="font-size:14px" class="fa">&#xf2b5;</i></span>Step 4 : Payment Details</div>
													<div class="row">
														<div class="col-md-12">
															<div class="card card-box">
																<div class="card-head">
																	<header>Get Payment Details</header>
																	<div class="tools">
																		<a class="fa fa-repeat btn-color box-refresh" href="javascript:;"></a>
																		<a class="t-collapse btn-color fa fa-chevron-down" href="javascript:;"></a>
																		<a class="t-close btn-color fa fa-times" href="javascript:;"></a>
																	</div>
																</div>
																<div class="card-body ">
																	<form id='eseva' action=" " method="post" novalidate="novalidate" _lpchecked="1">
																		<div class="inner-wrap">
																			<div class="card-body ">

																				<div class="row">
																					<div class="row align-items-center" style="margin-bottom:10px;">
																						<div class="col-md-4 vds_label d-flex align-items-center" style="display:flex;align-items:center;">The Receipt(s) will be generated in the Name of</div>
																						<div class="col-md-4">
																							<input class="vds100 form-control" type="text" maxlength="50" name="fullnamecart" id="fullnamecart" value="" placeholder="Enter full name">
																						</div>
																					</div>
																					<div class="vds_label" hidden>with ID </div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostidcart" id="hostidcart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostemailcart" id="hostemailcart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostmobilecart" id="hostmobilecart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostgendercart" id="hostgendercart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostaddresscart" id="hostaddresscart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostcitycart" id="hostcitycart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hoststatecart" id="hoststatecart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostpincodecart" id="hostpincodecart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostgotracart" id="hostgotracart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostnakshatracart" id="hostnakshatracart" value=""></div>
																					<div class="col-md-1"><input hidden class="vds100" type="text" name="hostrashicart" id="hostrashicart" value=""></div>
																				</div>

																				<div class="row vds_row">
																					<div class="col-md-1 vds_label" title="Amount in Rs">Amount</div>
																					<div class="col-md-2">
																						<input type="text" class="vds100" name="Amount" id="Amount" value="" readonly required>
																					</div>
																				</div>

																				<div class="row vds_row">
																					<div class="col-md-1 vds_label" title="Payment Type">Payment Type</div>
																					<div class="col-md-2">
																						<select id='paymenttype' name="paymenttype" class="vds100" aria-hidden="true" required>
																							<option value=""></option>
																							<?php if ($generateRazorpayReceipt == 1) { ?>
																								<option value="Razorpay" <?php echo $contrib['paymenttype'][0] == 'Razorpay' ? 'selected' : ''; ?>>Razorpay</option>
																							<?php } ?>
																							<!--<option value="upi" <?php echo $contrib['paymenttype'][0] == 'upi' ? 'selected' : ''; ?>>UPI - Paytm, Bhim, Phonepe etc </option>-->
																							<option value="cash" <?php echo $contrib['paymenttype'][0] == 'cash' ? 'selected' : ''; ?>>Cash</option>
																							<option value="card" <?php echo $contrib['paymenttype'][0] == 'card' ? 'selected' : ''; ?>>Card</option>
																							<option value="chequedd" <?php echo $contrib['paymenttype'][0] == 'chequedd' ? 'selected' : ''; ?>>Cheque/DD</option>
																						</select>
																					</div>
																				</div>


																				<div id="identityProof" class="row vds_row">
																					<div class="col-md-1 vds_label" title="Payment Type">Identity Proof</div>
																					<div class="col-md-3">
																						<select id='identityType' name="identityType" class="vds100" aria-hidden="true" required>
																							<option value=""></option>
																							<option value="Pancard" <?php echo $contrib['pan'][0] == 'Pancard' ? 'selected' : ''; ?>>Pancard</option>
																							<option value="Aadhar Number" <?php echo $contrib['aadhar'][0] == 'Aadhar Number' ? 'selected' : ''; ?>>Aadhar Number</option>
																						</select>
																					</div>
																					<div class="col-md-1 vds_label" title="identityValue">Identity No</div>
																					<div class="col-md-3">
																						<input type="text" class="vds100" name="identityValue" id="identityValue" value="<?php echo $contrib['Pan'][0]; ?>">
																					</div>
																				</div>

																				<div id='Razorpay' class="row vds_row">
																					<div class="table-responsive">
																						<table class="table table-bordered" id="razorpay_fields">
																							<tr>
																								<td>razorpay_payment_id<input type="text" name="razorpay_payment_id" placeholder="Razorpay Payment ID" class="form-control name_list" required="" /></td>
																								<td>razorpay_transaction_date<input type="text" id='razorpay_transaction_date' name="razorpay_transaction_date" placeholder="Razorpay Transaction On" class="form-control name_list" /></td>
																							</tr>
																							<tr>
																								<td>rz_amount <input type="text" name="rz_amount" placeholder="Razorpay Amount" class="form-control name_list" required="" /></td>
																								<td>rz_fee<input type="text" name="rz_fee" placeholder="Razorpay Fees" class="form-control name_list" required="" /></td>
																								<td>rz_taxes<input type="text" name="rz_taxes" placeholder="Razorpay Taxes" class="form-control name_list" /></td>
																							</tr>
																							<tr>
																								<td>rp_email<input type="text" name="rp_email" placeholder="Email used for Razorpay" class="form-control name_list" /></td>
																								<td>rp_phone<input type="text" name="rp_phone" placeholder="Phone used for Razorpay" class="form-control name_list" /></td>
																								<td>settlement_id<input type="text" name="settlement_id" placeholder="settlement_id" class="form-control name_list" /></td>
																							</tr>
																						</table>
																					</div>
																				</div>

																				<div id='card' class="row vds_row">
																					<div class="table-responsive">
																						<table class="table table-bordered" id="dynamic_field">
																							<tr>
																								<td>Card No.<input type="text" name="dbankCard[]" placeholder="Last 4 digits of card" class="form-control name_list" required="" /></td>
																								<td>Bank Name<input type="text" name="dbankName[]" placeholder="Enter your Bank Name" class="form-control name_list" required="" /></td>
																								<td>Amount<input type="text" name="dbankAmount[]" placeholder="Enter the Amount" class="form-control name_list" required="" /></td>
																								<td><button type="button" name="add" id="add" class="btn btn-success">Add Card</button></td>
																							</tr>
																						</table>
																					</div>
																				</div>

																				<div id='cheque' class="row vds_row">
																					<div class="col-md-1 vds_label" title="Cheque/DD Number">Cheque/DD Number</div>
																					<div class="col-md-3"><input type="text" class="vds100" name="chqddno" value="" required>
																					</div>
																					<div class="col-md-1 vds_label" title="Bank">Bank</div>
																					<div class="col-md-3"><input type="text" class="vds100" name="bank" value="" required>
																					</div>
																					<div class="col-md-1 vds_label" title="Dated">Dated</div>
																					<div class="col-md-3"><input id='chqdated' type="text" class="vds100" name="chqdated" value="" required>
																					</div>
																				</div>


																				<div class="row vds_row">
																					<div><input type="checkbox" id="iAgree" name="iAgree" value="Yes" checked required> </div>
																					<div class="vds_label"> The donor has read the <a target="_blank" href="/toc"> Terms and Conditions </a> and has understood that the <strong> payments are non-refundable and registrations are non-transferable.</strong><br></div>
																				</div>

																				<div class="row vds_row">
																					<div><input type="checkbox" id="iAgreePolicy" name="iAgreePolicy" value="Yes" checked required> </div>
																					<div class="vds_label"> The donor has read the <a target="_blank" href="/privacy"> Privacy Policy </a>and hereby <strong> agrees to the Privacy Policy</strong> and provide's <strong> his consent </strong> to receive updates about upcoming events & activities.<br> </div>
																				</div>

																				<ol class="ol-list"> </strong>
																					<strong> By clicking on the 'Proceed for Payment' button, the donor has agreed to the Privacy Policy/Notice and Terms and Conditions </strong>
																					<li>All the fields marked with * Mandatory.</li>
																					<li>Please ensure the donor account has <strong> SUFFICIENT FUNDS.</strong></li>
																					<li>Tax Invoice/Receipt will be valid, subject to realization of cheques/DD/online transactions.</li>
																					<li>A <strong>confirmation email along with the receipt</strong> will be sent to the email id against the successful payment. Kindly cross check the same to avoid duplicate transactions.</li>
																					<li>Re- Attempting to Register : If you are re trying, then first please check whether your bank account is already debited with the amount of earlier transaction .
																						If debited please do not pay again . You can report the same to us on below mentioned contact numbers.
																						Our finance team will get back to you in response to your reported issue .</li>
																					<li>As per Income tax laws, <strong>80G tax exemption is not available</strong></li>
																					<li>Please <a href="/#contact" target="_blank" rel="noopener noreferrer">contact us</a> for any queries/assistance regarding online donation.</li>
																				</ol>

																				<div class="row vds_row">
																					<div class="col-md-1">&nbsp;</div>
																					<div class="col-md-4"><input id="contribute" type="submit" value="Proceed for Payment and Generating Receipts" onClick='return confirmSubmit()'></div>
																				</div>
																				<?php wp_nonce_field('amount_eseva', 'amount_seva'); ?>

																			</div>
																		</div>
																	</form>
																</div>

															</div>
														</div>

													</div>


												</div> <!-- Close of div Payment Details -->
												<!--- PAYMENT DETAILS ENDS HERE --->

											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!--- MODAL POPUP WINDOW STARTS --->
						<div class="modal" id="DescModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog" style="width:750px;">
								<div class="modal-content" style="width:750px;">
									<div class="modal-header">
										<h3 class="modal-title w-100"> <i class="fa fa-list" aria-hidden="true"></i> Multiple Payment Options </h3>
										<button type="button" class="btn btn-default float-right" data-dismiss="modal" data-bs-dismiss="modal">
											<span aria-hidden="true">&times;</span>
											<span class="sr-only">Close</span>
										</button>
									</div>
									<div class="modal-body">

										<div class="row vds_row">
											<div class="col-md-3 vds_label">Event Name </div>
											<div class="col-md-7">
												<input class="vds100" style="border: 1px ; background-color: white;" type="text" readonly name="custom_event_name" id="custom_event_name">
											</div>
										</div>
										<div class="row vds_row">
											<div class="col-md-3 vds_label">Category </div>
											<div class="col-md-7">
												<input class="vds100" style="border: 1px ; background-color: white;" type="text" readonly name="custom_event_category" id="custom_event_category">
											</div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label">Event ID </div>
											<div class="col-md-7">
												<input style="border: 1px ; background-color: white;" type="text" readonly name="custom_event_id" id="custom_event_id">
											</div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label" id="custom_event_date_lbl">Event Date </div>
											<div class="col-md-7">
												<input style="border: 1px ; background-color: white;" type="text" readonly name="custom_event_date" id="custom_event_date">
											</div>
										</div>

										<div class="row vds_row">
											<div class="col-md-11" id="customSankalpaText">
												<h4 class="modal-title w-100"> <i class="fa fa-toggle-right " aria-hidden="true"></i> Additional Payment Options </h4>
												<small> Note : This Event has various payment options. Please select the option of your choice.</small>
											</div>
											<div class="col-md-11" id="standardSankalpaText">
												<h4 class="modal-title w-100"> <i class="fa fa-toggle-right" aria-hidden="true"></i> Standard Payment Options </h4>
											</div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label" title="Select the Option" id="sankalpaOptionsLbl">Payment Options</div>
											<div class="col-md-7">
												<select class="vds100" type="select" name="sankalpaOptions" id="sankalpaOptions">
													<option value="">--Select Options--</option>
												</select>
											</div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label" title="Description">Description</div>
											<div class="col-md-7"><input type="text" class="vds100" name="sankalpaDesc" id="sankalpaDesc" maxlength="10" value="" readonly></div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label" title="Amount">Amount</div>
											<div class="col-md-3"><input type="text" class="vds100" name="sankalpaAmount" id="sankalpaAmount" maxlength="10" value="" readonly></div>

											<div class="col-md-2 vds_label" title="Entry For">Entry For</div>
											<div class="col-md-2"><input type="text" class="vds100" name="sankalpaPass" id="sankalpaPass" maxlength="10" value="" readonly></div>
										</div>

										<div class="row vds_row">
											<div class="col-md-3 vds_label" title="Select the Option">Attending Personally?</div>
											<div class="col-md-3">
												<select class="vds100" type="select" name="attending" id="attending">
													<option value="Yes">Yes</option>
													<option value="No">No</option>
												</select>
											</div>
										</div>

										<!--
												<table  id="modal_table" class="table table-striped">
												<thead>
													<tr>
													<th>Name</th>
													<th>Position</th>
													<th>Office</th>
													</tr>
												</thead>
												</table>
												-->

									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-primary " id="save" data-dismiss="modal" data-bs-dismiss="modal"> <i class="fa fa-cart-plus" aria-hidden="true"></i> Add to Cart </button>
										<button type="button" class="btn btn-default " data-dismiss="modal" data-bs-dismiss="modal"> <i class="fa fa-close"></i> Cancel </button>
									</div>

								</div><!-- /.modal-content -->
							</div><!-- /.modal-dialog -->
						</div><!-- /.modal <!--- MODAL POPUP WINDOW ENDS --->

						<!-- end page content -->
					</div>
					<!-- end page container -->
					<!-- start footer -->
					<div class="page-footer">
						<div class="scroll-to-top">
							<i class="icon-arrow-up"></i>
						</div>
					</div>
					<!-- end footer -->
					</div>

				<?php

			} else {
				//Form submitted on click of contribute with nonce amount_seva
				require_once get_stylesheet_directory() . '/helpers/f-standardfunctions.php';
				$hostid = sanitize_text_field($_POST['hostidcart']);
				$fullname = sanitize_text_field($_POST['fullnamecart']);
				$totalPayAmount = sanitize_text_field($_POST['Amount']);
				$identityType = sanitize_text_field($_POST['identityType']);
				$identityValue = sanitize_text_field($_POST['identityValue']);
				if ($identityType == "Pancard") {
					$pannumber = $identityValue;
				} else {
					$aadhar = $identityValue;
				}

				$mode = "offline";
				$paymenttype = sanitize_text_field($_POST['paymenttype']);

				if ($hostid == null || $hostid == "") {
					$userid_forcart = $userid;
				} else {
					$userid_forcart = $hostid;
				}

				$userDetailsArray = array();
				$myObj = new stdClass();
				$myUserObj = new stdClass();
				$myUserObj->hostid = sanitize_text_field($_POST['hostidcart']);
				$myUserObj->fullname = sanitize_text_field($_POST['fullnamecart']);
				$myUserObj->email = sanitize_text_field($_POST['hostemailcart']);
				$myUserObj->mobile = sanitize_text_field($_POST['hostmobilecart']);
				$myUserObj->gender = sanitize_text_field($_POST['hostgendercart']);
				$myUserObj->address = sanitize_text_field($_POST['hostaddresscart']);
				$myUserObj->city = sanitize_text_field($_POST['hostcitycart']);
				$myUserObj->state  = sanitize_text_field($_POST['hoststatecart']);
				$myUserObj->pincode  = sanitize_text_field($_POST['hostpincodecart']);
				$myUserObj->gotra  = sanitize_text_field($_POST['hostgotracart']);
				$myUserObj->nakshatra  = sanitize_text_field($_POST['hostnakshatracart']);
				$myUserObj->rashi  = sanitize_text_field($_POST['hostrashicart']);
				$userDetailsArray[] = $myUserObj;
				$userDetailsJSON = json_encode($userDetailsArray);

				//Get the cart details
				if ($testingmode == 1) {
					echo ("<script>console.log('User ID for Cart Testing Mode: " . $userid_forcart . "');</script>");
					$tablename = $wpdb->prefix . 'vds_users_cart_testing';
				} else {
					$tablename = $wpdb->prefix . 'vds_users_cart';
				}

				$query = "SELECT * FROM $tablename where status = 0 AND user_id = '" . $userid_forcart . "' ORDER BY created_on DESC;";
				$sql = $wpdb->prepare($query, ARRAY_A);
				$allCart = $wpdb->get_results($sql, ARRAY_A);
				$allCartCount = $wpdb->num_rows;

				if ($mode == 'offline') {
					if ($paymenttype == 'cash') {
						$chqddno = 'Cash';
						$chqdated = 'NA';
						$bank = 'NA';
					} else if ($paymenttype == 'card') {
						//multiple card handing to be done...receipts are done via post and we are moving out of posts now
						$cards = ($_POST['dbankCard']);
						$banks = ($_POST['dbankName']);
						$amounts = ($_POST['dbankAmount']);

						$totalCards = count($cards);
						$chqdated = date("j M Y");
						$chqddno = implode(",", $cards);
						$bank = implode(",", $banks);
					} else if ($paymenttype == 'Razorpay') {
						$razorpay_payment_id = sanitize_text_field($_POST['razorpay_payment_id']);
						$rz_amount = sanitize_text_field($_POST['rz_amount']);
						$rz_fee = sanitize_text_field($_POST['rz_fee']);
						$rz_taxes = sanitize_text_field($_POST['rz_taxes']);
						$bank = 'Razorpay';
						$chqdated = sanitize_text_field($_POST['razorpay_transaction_date']);
						$rp_email = sanitize_text_field($_POST['rp_email']);
						$rp_phone = sanitize_text_field($_POST['rp_phone']);
						$settlement_id = sanitize_text_field($_POST['settlement_id']);
					} else if ($paymenttype == 'chequedd') {
						$chqddno = sanitize_text_field($_POST['chqddno']);
						$bank = sanitize_text_field($_POST['bank']);
						$chqdated = sanitize_text_field($_POST['chqdated']);
					}

					$paymentDetailsArray = array();

					$myObj->paymenttype = $paymenttype; //Razorpay,card,cash,chequedd					
					$myObj->method = $method; //UPI,Wallet,NetBanking, Card, Cash, Chequedd
					$myObj->chqddno = $chqddno;
					$myObj->chqdated = $chqdated;
					$myObj->bank  = $bank;
					$myObj->totalPayAmount = $totalPayAmount;
					$myObj->pannumber = $pannumber;
					$myObj->aadhar = $aadhar;

					$myObj->totalCards  = $totalCards;
					if ($totalCards > 1) {
						$myObj->cards  = implode(",", $cards);
						$myObj->banks  = implode(",", $banks);
						$myObj->amounts  = implode(",", $amounts);
					}

					$myObj->razorpay_payment_id  = $razorpay_payment_id;
					$myObj->rz_amount  = $rz_amount;
					$myObj->rz_fee  = $rz_fee;
					$myObj->rz_taxes  = $rz_taxes;
					$myObj->rp_email  = $rp_email;
					$myObj->rp_phone  = $rp_phone;
					$myObj->settlement_id  = $settlement_id;

					$cashCollectedOn = date('Y-m-d H:i:s', time());

					// The receipt generated details were never stored and used
					$myObj->receipt_generated_by  = $userName;
					$myObj->receipt_generated_role  = $userRole;
					$myObj->receipt_generated_id  = $userid;
					$myObj->receipt_generated_on  =  $cashCollectedOn;

					$myObj->cashCollectedBy  = $userName;
					$myObj->cashCollecterRole  = $userRole;
					$myObj->cashCollecterID  = $userid;
					$myObj->cashCollectedOn  = $cashCollectedOn;

					foreach ($allCart as $print) {

						$myObj->eventDate = $print['eventDate'];
						$myObj->attending = $print['attending'];

						$paymentDetailsArray[] = $myObj;
						$paymentDetailsJSON = json_encode($paymentDetailsArray);
						echo "<br> Testing Mode : " . $testingmode . " User ID for Cart : " . $userid_forcart . " Cart ID : " . $print['id'] . " Event ID : " . $print['event_id'] . " Mode : " . $mode . "<br>";
						$savedpostid = pay_and_generate_receipt($userid, $print['event_id'], $print['sankalpaOptionID'], $mode, $paymentDetailsJSON, $testingmode, $userDetailsJSON);
						echo ("<script>console.log('Generated Post in Contrib Tables for EVENT ID : " . $print['event_id'] . "');</script>");
						$cartdata = array(
							'status'     => 1,
							'postid'     => $savedpostid
						);

						update_cart($print['id'], $cartdata, $testingmode);
						$transactionStatus = "Success";
						send_receipts_from_contrib($savedpostid, $testingmode);
						$arraydivision[$print['id']] = $savedpostid;
					}
				}


				?>
					<div class="container">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td style="padding-right: 0px;padding-left: 0px;" align="center">
									<img align="center" border="0" src="//wp-content/uploads/2023/03/green-check-mark.jpg" alt="Logo" title="Logo" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: 160px;float: none;width: 27%;max-width: 162px;" width="162" class="v-src-width v-src-max-width" />
								</td>
							</tr>
						</table>
						<h2>Thank You !</h2>
						<hr>
						<p>Payment has been successfully received with the following details.</p>
						<div class="alert alert-success">
							<strong>Transaction Status : </strong> <?php echo $transactionStatus; ?>
						</div>
						<div class="alert alert-info">
							<strong>Payment Type : </strong> <?php echo $paymenttype; ?>
						</div>
						<div class="alert alert-info">
							<strong>Total Amount :</strong> Rs. <?php echo intval($totalPayAmount); ?>
						</div>
						<div class="alert alert-info">
							<strong>Event Details :</strong>
						</div>
						<div style="overflow:auto;">
							<table id="example" class="display" style="width:100%">
								<thead>
									<tr>
										<th>ID</th>
										<th>Receipt </th>
										<th>Event Name</th>
										<th>Date</th>
										<th>Sankalpa Detail</th>
										<th>Amount</th>
										<th>No. of Sankalpa</th>
									</tr>
								</thead>

								<tbody>
									<?php
									foreach ($allCart as $print) {
										$contrib_details = json_decode(get_contrib_details("", $arraydivision[$print['id']], $testingmode), true);

										echo '<tr>';
										echo '<td><a target="_blank" href="/sevadetails/?eid=' . $print['event_id'] . '">' . "E" . $print['event_id'] . '</a></td>';
										echo '<td>';
										echo '<a class="subs-button" href="/downloadreceipt/' . $arraydivision[$print['id']] . "/" . $testingmode . '">' . $contrib_details[0]['receipt_no'] . '</a>';
										echo '</td>';
										echo '<td>' . $print['event_name'] . '<br><b>Venue : </b>' . $contrib_details[0]['location'] . '</td>';
										echo '<td>' . $contrib_details[0]['eventDate'] . '</td>';
										if ($print['additionalSankalpa']) {
											echo '<td>' . $print['sankalpaOptionIDDescription']  . '</td>';
										} else {
											echo '<td>' . $print['event_name']  . '</td>';
										}
										echo '<td>' . 'Rs ' . $print['sevaamt']  . '</td>';
										echo '<td>' . $print['quantity'] . '</td>';
										echo '</tr>';
									} //close of the foreach loop 					
									?>
								</tbody>
							</table>
						</div>
						<div class="button-section" style="text-align: center; position: relative;  padding-top: 1em; border: 0px solid">
							<a href="/offlinecart"> <button title="Collect Offline Payments" type="button" class="btn btn-info" id='makeAnotherPayment'>Collect Another Payment</button></a>
							<a href="/cashier"> <button title="Goto Cashier Dashboard" type="button" class="btn btn-success" id='gotoCashierDashboard'>Goto Cashier Dashboard</button></a>
						</div>
					</div>

				<?php
			}
				?>
				<script>
					//we're going to run form validation on the #validate-form element
					jQuery("#eseva").validate({
						//specify the validation rules
						rules: {
							fullnamecart: "required",
						},
						//specify validation error messages
						messages: {
							fullnamecart: "Name on whom receipt has to be generated cannot be blank!",
						},
						submitHandler: function(form) {
							$(".submit").attr("disabled", true);
							form.submit();
						}

					});

					jQuery(document).ready(function($) {
						// Fix: jQuery UI datepicker needs $.fn.zIndex, which is missing on this page (multiple jQuery UI builds).
						if (typeof jQuery.fn.zIndex !== "function") {
							jQuery.fn.zIndex = function( zIndex ) {
								if ( zIndex !== undefined ) { return this.css( "zIndex", zIndex ); }
								if ( this.length ) {
									var elem = jQuery( this[0] ), position, value;
									while ( elem.length && elem[0] !== document ) {
										position = elem.css( "position" );
										if ( position === "absolute" || position === "relative" || position === "fixed" ) {
											value = parseInt( elem.css( "zIndex" ), 10 );
											if ( !isNaN( value ) && value !== 0 ) { return value; }
										}
										elem = elem.parent();
									}
								}
								return 0;
							};
						}
						// Managing custom date ranges visibility
						$('#divcustomDates').hide();
						$('#q_fiscalyear').on('change', function() {
							var myfiscalYear = $('#q_fiscalyear').val();
							if (myfiscalYear === 'CustomDates') {
								$('#divcustomDates').show();
							} else {
								$('#divcustomDates').hide();
							}
						});

						// Based upon the purpose selected the sub_purpose are fetched and loaded here
						$('#purpose').on('change', function() {
							var purpose = $('#purpose').val();
							var sevatype = $('#sevaType').val();
							$.ajax({
								type: "GET",
								url: "/wp-content/themes/vds/api_purpose_sevatype.php?purpose=" + purpose + '&sevatype=' + sevatype,
								dataType: 'json',
								success: function(data) {
									$('#sub_purpose_type').empty();
									$('#sub_purpose_type').append($('<option>').text('').attr('value', ''));
									$.each(data, function(i, value) {
										$('#sub_purpose_type').append($('<option>').text(value.sub_purpose).attr('value', value.sub_purpose));
									});
									$('#sub_purpose_type').change();
								}
							});
						});

						// create users in wp_users
						$('#createuserbutton').on('click', function() {
							var user_email = $('#email').val();
							var user_name = $('#username').val();

							//alert ("email : " + user_email + "\n user_name:" + user_name);
							var okcancel = confirm("Are you sure to create a new user ? \n - OK : creates user \n - CANCEL : To cancel creating");

							if (okcancel == true) {
								$.ajax({
									async: false,
									url: "/wp-content/themes/vds/api_create_wp_user.php?user_email=" + user_email + '&user_name=' + user_name,
									method: "POST",
									data: {
										user_email: user_email,
										user_name: user_name
									},
									dataType: "json",
									success: function(data) {
										alert("ID is " + data[0].user_id + " \n New User Created : " + data[0].user_created + "\n Username already available : " + data[0].username_exist + " \n useremail_exist : " + data[0].useremail_exist);

										if (data[0].user_created == "False") {
											if (data[0].username_exist == "True") {
												alert("The username " + user_name + " is already available hence new user is not created. Give a different username and try again !");
											} else if (data[0].useremail_exist == "True") {
												alert("The email " + user_email + " is already available hence new new user is not created. Give a different email and try again !");
											}
										} else {
											alert("New user with username " + user_name + " and email " + user_email + "is successfully created. ID is " + data[0].user_id);
										}
										//window.location.href=window.location.href;								
									},
									error: function(request, status, error) {
										alert("Error while creating new user!");
										console.log("There was an error: ", request.responseText);
									}
								});

								getCartListOffline();
							} else {
								alert("New User Creation is cancelled");
							}

						}); //close of create user button

						$('#Razorpay').hide();
						$('#identityProof').hide();
						$('#cheque').hide();
						$('#card').hide();

						$('#paymenttype').on('change', function() {
							if (this.value == 'card') {
								$('#cheque').hide();
								$('#Razorpay').hide();
								$('#card').show();
							} else if (this.value == 'cash') {
								$('#Razorpay').hide();
								$('#cheque').hide();
								$('#card').hide();
								var lamount = $('#Amount').val();
								if (lamount > 11000) {
									alert("Amount more than Rs.11000 cannot be collected as cash");
									//$('#contribute').prop("disabled", true);
								}
							} else if (this.value == 'chequedd') {
								$('#Razorpay').hide();
								$('#cheque').show();
								$('#card').hide();
							} else if (this.value == 'Razorpay') {
								$('#Razorpay').show();
								$('#cheque').hide();
								$('#card').hide();
							} else {
								$('#Razorpay').hide();
								$('#cheque').hide();
								$('#card').hide();
							}
						});

						// Events are loaded based upon the search parameters
						$('#getCartData').on('click', function() {
							getCartListOffline();
						}); //close of click action	

						// When we removeitems from cart action on the datatables  				
						$("#cartList").on('click', '.update', function() {
							var cart_id = $(this).attr("id");
							var action = 'removeCartItem';
							var testingMode = '<?php echo $testingmode; ?>'; //1 for testing mode 0 for live
							var okcancel = confirm("Are you sure to delete ? \n - OK : Delete the item from your list \n - CANCEL : To continue having the item in your list");

							if (okcancel == true) {
								$.ajax({
									async: false,
									url: "/wp-content/themes/vds/api_cart_options.php?cartid=" + cart_id + '&action=' + action + '&testingMode=' + testingMode,
									method: "POST",
									data: {
										cartid: cart_id,
										testingMode: testingMode,
										userid: '<?php echo $userid; ?>',
										action: action
									},
									dataType: "json",
									success: function(data) {
										alert("The cart item " + cart_id + " is removed from your list !");
										//window.location.href=window.location.href;								
									},
									error: function(request, status, error) {
										alert("error");
										console.log("There was an error: ", request.responseText);
									}
								});

								getCartListOffline();
							} else {
								alert("Your items are still left on your list");
							}

						});

						// ----- getCartListOffline : It queries based upon the parameters and load the cartTable ----
						function getCartListOffline() {

							var userid = $('#hostid').val();
							//userid=17;
							//user is not existing in system.cart items are assigned to cashier and he settles it
							if ((userid == null) || (userid == '') || (userid == 0)) {
								var userid = '<?php echo $userid; ?>';
							} else {
								$('#hostidcart').val(userid);
							}

							var userfullname = $('#fullname').val();
							$('#fullnamecart').val(userfullname);
							var email = $('#email').val();
							$('#hostemailcart').val(email);
							var mobile = $('#mobile').val();
							$('#hostmobilecart').val(mobile);
							var gender = $('#gender').val();
							$('#hostgendercart').val(gender);
							var address = $('#address').val();
							$('#hostaddresscart').val(address);
							var city = $('#city').val();
							$('#hostcitycart').val(city);
							var state = $('#state').val();
							$('#hoststatecart').val(state);
							var pincode = $('#pincode').val();
							$('#hostpincodecart').val(pincode);
							var gotra = $('#gotra').val();
							$('#hostgotracart').val(gotra);
							var nakshatra = $('#nakshatra').val();
							$('#hostnakshatracart').val(nakshatra);
							var rashi = $('#rashi').val();
							$('#hostrashicart').val(rashi);

							$('#cartList').dataTable().fnClearTable();
							$('#cartList').dataTable().fnDraw();
							$('#cartList').dataTable().fnDestroy();
							$("#cartList").empty();

							var cartTable;

							// Access the array elements of the user roles						
							var passedArray = <?php echo json_encode($user_info->roles); ?>;
							if (passedArray == null) {
								var passedArray = <?php echo json_encode("No Roles"); ?>;
							}

							var purpose = <?php echo json_encode($purpose); ?>;
							//var sub_purpose = <?php echo json_encode($sub_purpose); ?>; 					
							var purpose = $('#purpose').val();
							if (purpose === undefined || purpose == null || purpose.length <= 0) {
								// No need to add single quotes as IN used in query								
							} else {
								var purpose = "'" + purpose + "'";
							}

							var sub_purpose = $('#sub_purpose_type').val();
							if (sub_purpose === undefined || sub_purpose == null || sub_purpose.length <= 0) {
								// No need to add single quotes as IN used in query								
							} else {
								var sub_purpose = "'" + sub_purpose + "'";
							}

							var ashramSelected = $('#ashram').val();

							var statesResponsible = <?php echo json_encode($additionalStates); ?>;
							var stateSelected = $('#eventState').val();
							if (stateSelected) {
								//alert ("state Selected is :" + stateSelected);
								statesResponsible = '';
							} else {
								//alert ("states:" + statesResponsible);
							}

							//the json array is created to send all the search parameters					    
							var searchParams = {};
							searchParams.state = $('#eventState').val();
							searchParams.fiscalyear = $('#q_fiscalyear').val();
							searchParams.eventID = $('#q_eventID').val();
							//searchParams.status = $('#q_status').val();						 
							searchParams.status = "0";
							searchParams.datefrom = $('#datefrom').val();
							searchParams.dateto = $('#dateto').val();
							searchParams.purpose = purpose;
							searchParams.sub_purpose = sub_purpose;
							searchParams.ashram = ashramSelected;
							searchParams.eventtype = 'samoohik';
							var testingMode = '<?php echo $testingmode; ?>'; //1 for testing mode 0 for live
							searchParams.testingMode = testingMode;
							var str = JSON.stringify(searchParams);
							var myState = 'Karnataka';

							$("#cartList").append('<tfoot> <th colspan="10" style="text-align:right">Total:</th> <th></th> </tfoot>');
							cartTable = $('#cartList').DataTable({
								"lengthMenu": [
									['10', '25', '50', '100', '500', '1000'],
									['10 rows', '25 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
								],
								"dom": 'Blfrtip',
								"buttons": ['excel'],
								"processing": true,
								"serverSide": false,
								"ajax": "/wp-content/themes/vds/assets/vkutable/cart_data_processing.php?id=" + userid + '&state=' + myState + '&employees=' + str,
								"order": [0, 'asc'],
								"select": 'single',
								"columns": [{
										"data": 0,
										"width": "5px"
									},
									{
										"data": 0,
										"width": "30px",
										"render": function(data, type, row, meta) {
											//javascript function is called to get the relevant actions as per role
											var event_status = row[11];
											var event_id = row[3];
											var transid = row[3];
											var actions = getActions(event_status, transid, event_id, passedArray);
											return actions;
										}
									},
									{
										"data": 0,
										"width": "20px",
										"render": function(data, type, row, meta) {
											if (type === 'display') {
												if (userid == 0) {
													data = '<a class="btn btn-info" target="_blank" href="/event?transid=' + data + '">' + '<i style="font-size:12px" class="fa fa-tasks"></i>Register </a>'
												} else {
													data = '<button type="button" name="update" id="' + data + '" class="btn btn-warning btn-xs update">Remove</button>';
												}
											}
											return data;
										}
									},
									{
										"title": "Name",
										"width": "150px",
										"data": 2
									},
									{
										"title": "User ID",
										"width": "30px",
										"data": 1
									},
									{
										"title": "Event ID",
										"width": "30px",
										"data": 3
									},
									{
										"title": "Event Name",
										"width": "180px",
										"data": 7
									},
									{
										"title": "Event Date",
										"width": "50px",
										"render": function(data, type, row, meta) {
											if (type === 'display') {
												if (row[11] == '') {
													data = row[5];
												} else {
													data = row[11];
												}
											}
											return data;
										}
									},
									{
										"title": "Sankalpa",
										"width": "40px",
										"data": 4
									},
									{
										"title": "Attending",
										"width": "20px",
										"data": 10
									},
									{
										"title": "Amount",
										"width": "40px",
										"data": 8
									},
								],
								"footerCallback": function(row, data, start, end, display) {
									var api = this.api();

									// Remove the formatting to get integer data for summation
									var intVal = function(i) {
										return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
									};

									// Total over all pages
									total = api
										.column(10)
										.data()
										.reduce(function(a, b) {
											return intVal(a) + intVal(b);
										}, 0);

									// Total over this page
									pageTotal = api
										.column(10, {
											page: 'current'
										})
										.data()
										.reduce(function(a, b) {
											return intVal(a) + intVal(b);
										}, 0);

									// Update footer
									$(api.column(10).footer()).html('Rs.' + pageTotal + '<br>(Rs.' + total + ' total)');
									$('#Amount').val(total);
									$('#Amount').change();
								}
							}); //datatable close			

						}


						// Events are loaded with default setting in the datatable
						getEventListOffline();
						//getCartListOffline();

						// Events are loaded based upon the search parameters
						$('#btnSearchEvents').on('click', function() {
							getEventListOffline();
						}); //close of click action	


						// When cart button is clicked items are loaded to cart  				
						$("#eventsList").on('click', '.update', function() {
							var eventTableID = $(this).attr("id");
							var action = 'getEmployee';
							var userid = $('#hostid').val();
							var userfullname = $('#fullname').val();

							if ((userid == null) || (userid == '') || (userid == 0)) {
								if ((userfullname == null) || (userfullname == '') || (userfullname == 0)) {
									alert("Please select the User to whom Sankalpa has to be registered. \n\n If the ID is not available then create a new user with the email id !");
									document.getElementById("email").focus();
									return;
								} else {
									var userid = '<?php echo $userid; ?>';
								}
								//user is not existing in system.items are assigned to cashier and he settles it.
							}

							var currentRow = $(this).closest("tr");

							var eventsID = currentRow.find("td:eq(13)").text(); // get current row 1st TD value
							var eventname = currentRow.find("td:eq(3)").text(); // get current row 2nd TD
							var eventcategory = currentRow.find("td:eq(4)").text(); // get current row 2nd TD
							var eventdate = currentRow.find("td:eq(6)").text(); // get current row 2nd TD
							var amount = currentRow.find("td:eq(8)").text(); // get current row 3rd TD
							var data = eventsID + "\n Event Name:" + eventname + "\n Event Date:" + eventdate + "\n Amount:" + amount;

							$("#custom_event_id").val(eventsID);
							$("#custom_event_name").val(eventname);
							$("#custom_event_category").val(eventcategory);
							$("#custom_event_date").val(eventdate);
							$('#DescModal').modal("show");

						});

						// ----- getEventListOffline : It queries based upon the parameters and load the datatable ----
						function getEventListOffline() {
							$('#eventsList').dataTable().fnClearTable();
							$('#eventsList').dataTable().fnDraw();
							$('#eventsList').dataTable().fnDestroy();
							$("#eventsList").empty();
							var table;

							// Access the array elements of the user roles						
							var passedArray = <?php echo json_encode($user_info->roles); ?>;
							if (passedArray == null) {
								var passedArray = <?php echo json_encode("No Roles"); ?>;
							}

							var purpose = <?php echo json_encode($purpose); ?>;
							//var sub_purpose = <?php echo json_encode($sub_purpose); ?>; 					
							var purpose = $('#purpose').val();
							if (purpose === undefined || purpose == null || purpose.length <= 0) {
								// No need to add single quotes as IN used in query								
							} else {
								//var purpose = "'" + purpose + "'";									
							}

							var sub_purpose = $('#sub_purpose_type').val();
							if (sub_purpose === undefined || sub_purpose == null || sub_purpose.length <= 0) {
								// No need to add single quotes as IN used in query								
							} else {
								//var sub_purpose = "'" + sub_purpose + "'";	
							}

							var ashramSelected = $('#ashram').val();

							var statesResponsible = <?php echo json_encode($additionalStates); ?>;
							var stateSelected = $('#eventState').val();
							if (stateSelected) {
								//alert ("state Selected is :" + stateSelected);
								statesResponsible = '';
							} else {
								//alert ("states:" + statesResponsible);
							}

							//the json array is created to send all the search parameters					    
							var searchParams = {};
							searchParams.state = $('#eventState').val();
							searchParams.fiscalyear = $('#q_fiscalyear').val();
							searchParams.eventID = $('#q_eventID').val();
							//searchParams.status = $('#q_status').val();						 
							searchParams.status = "8";
							searchParams.datefrom = $('#datefrom').val();
							searchParams.dateto = $('#dateto').val();
							//searchParams.statesResponsible = statesResponsible;
							searchParams.purpose = purpose;
							searchParams.sub_purpose = sub_purpose;
							searchParams.ashram = ashramSelected;
							//searchParams.eventtype = 'samoohik';																
							var str = JSON.stringify(searchParams);
							var myState = 'Karnataka';
							var id = '132810';
							var userid = '<?php echo $userid; ?>';

							table = $('#eventsList').DataTable({
								"lengthMenu": [
									['10', '25', '50', '100', '500', '1000'],
									['10 rows', '25 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
								],
								"dom": 'Blfrtip',
								"buttons": ['excel'],
								"processing": true,
								"serverSide": true,
								"ajax": "/wp-content/themes/vds/assets/vkutable/events_data_processing.php?id=" + id + '&state=' + myState + '&employees=' + str,
								"order": [
									[0, 'asc'],
									[6, 'asc']
								],
								"select": 'single',
								"columns": [{
										"className": 'details-control',
										"orderable": false,
										"data": 1,
										"defaultContent": '',
										"render": function() {
											return '<i class="fa fa-empire" aria-hidden="true"></i>';
											//fa-user-circle-o fa fa-plus-square fa-newspaper-o fa-address-card-o fa-info-circle
										},
										width: "15px"
									},
									{
										"data": 0,
										"render": function(data, type, row, meta) {
											if (type === 'display') {
												if (userid == 0) {
													data = '<a class="btn btn-info" target="_blank" href="/event?transid=' + data + '">' + '<i style="font-size:12px" class="fa fa-tasks"></i>Register </a>'
												} else {
													data = '<button type="button" name="update" id="' + data + '" class="btn btn-warning btn-xs update"><i class="fa fa-cart-plus"></i>Add</button>';
												}
											}
											return data;
										}
									},
									{
										"title": "Action",
										"data": 0,
										"render": function(data, type, row, meta) {
											//javascript function is called to get the relevant actions as per role

											if (type === 'display') {
												if (userid == 0) {
													var event_status = row[11];
													var event_id = row[1];
													var data = getActions(event_status, data, event_id, passedArray);

												} else {
													data = '<a target="_blank" href="/sevadetails/?transid=' + data + '" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i>View</a>';
												}
											}
											return data;
										}
									},
									{
										"title": "Name",
										"data": 2
									},
									{
										"title": "Category",
										"data": 4
									},
									{
										"title": "Sub-Category",
										"data": 5
									},
									{
										"title": "Date",
										"data": 6
									},
									{
										"title": "Time",
										"data": 7
									},
									{
										"title": "Amount",
										"data": 21
									},
									{
										"title": "Venue",
										"data": 8
									},
									{
										"title": "City",
										"data": 9
									},
									{
										"title": "State",
										"data": 10
									},
									{
										"title": "With",
										"data": 15
									},
									{
										"title": "ID",
										"data": 1,
										"visible": true,
									}
								]
							}); //datatable close

							//---- THIS HAS TO HERE INSIDE ELSE THE BODY DOES NOT GET GENERATED START ----
							$('#eventsList tbody').on('click', 'td.details-control', function() {
								var tr = $(this).closest('tr');
								var tdi = tr.find("i.fa");
								var row = table.row(tr);

								if (row.child.isShown()) {
									// This row is already open - close it
									row.child.hide();
									tr.removeClass('shown');
									tdi.first().removeClass('fa-minus-square');
									tdi.first().addClass('fa-plus-square');
								} else {
									// Open this row														
									row.child(eventformat(row.data())).show();
									tr.addClass('shown');
									tdi.first().removeClass('fa-plus-square');
									tdi.first().addClass('fa-minus-square');
								}
							});
							//---- THIS HAS TO HERE INSIDE ELSE THE BODY DOES NOT GET GENERATED END ----	

						} // close of function getEventListOffline	


						$('#email').change(function() {

						});


						// Parse an event date string ("YYYY-MM-DD" or similar) into a local Date at midnight.
						function vdsParseEventDate(vdsStr) {
							if (!vdsStr) { return null; }
							var vdsParts = String(vdsStr).replace(/\//g, "-").split("-");
							if (vdsParts.length === 3 && vdsParts[0].length === 4) {
								return new Date(parseInt(vdsParts[0], 10), parseInt(vdsParts[1], 10) - 1, parseInt(vdsParts[2], 10));
							}
							var vdsD = new Date(vdsStr);
							if (isNaN(vdsD.getTime())) { return null; }
							vdsD.setHours(0, 0, 0, 0);
							return vdsD;
						}

						$("#DescModal").on('show.bs.modal', function(e) {

							var event_id = $('#custom_event_id').val();
							var eventname = $('#custom_event_name').val();
							var custom_event_purpose = $('#custom_event_category').val();
							$('#sankalpaOptions').val("");
							$('#sankalpaDesc').val("");
							$('#sankalpaPass').val("");
							$('#sankalpaAmount').val("");
							$('#sankalpaOptions').prop("hidden", false);
							$('#sankalpaOptionsLbl').prop("hidden", false);
							$("#custom_event_date").datepicker("option", "disabled", true);
							$("#custom_event_date").prop("hidden", true);
							$("#custom_event_date_lbl").prop("hidden", true);
							$("#customSankalpaText").prop("hidden", true);
							$("#standardSankalpaText").prop("hidden", true);
							$("#custom_event_date").val(""); //custom date to be cleared else it has current date on second time add of same event

							$.ajax({
								type: "GET",
								url: "/wp-content/themes/vds/api_get_customSankalpas.php?eventid=" + event_id,
								dataType: 'json',
								success: function(data) {
									$('#sankalpaOptions').empty();
									$.each(data, function(i, value) {
										//id value is zero in case of standardSankalpa
										$("#sankalpaOptions").append("<option value='" + value.id + "'>" + value.sankalpa_description + "</option>");
										$('#sankalpaDesc').val(value.sankalpa_description);
										$('#sankalpaPass').val(value.sankalpa_pass);
										$('#sankalpaAmount').val(value.sankalpa_amount);

										if (value.repeat_frequency == "daily") {
											if (value.event_start_date != null) {
												// Recurring "daily" events: keep the selectable dates inside the event window
												// (never before today, never after the event end date).
												var vdsToday = new Date(); vdsToday.setHours(0, 0, 0, 0);
												var vdsStart = vdsParseEventDate(value.event_start_date);
												var vdsEnd = vdsParseEventDate(value.event_end_date);
												var vdsMin = vdsToday;
												if (vdsStart && vdsStart.getTime() > vdsToday.getTime()) { vdsMin = vdsStart; }
												var vdsDefault = vdsMin;
												if (vdsEnd && vdsDefault.getTime() > vdsEnd.getTime()) { vdsDefault = vdsEnd; }
												$("#custom_event_date").datepicker("option", "minDate", vdsMin);
												$("#custom_event_date").datepicker("option", "maxDate", vdsEnd ? vdsEnd : null);
												$("#custom_event_date_lbl").prop("hidden", false);
												$("#custom_event_date").prop("hidden", false);
												$("#custom_event_date").datepicker("option", "disabled", false);
												$("#custom_event_date").val($.datepicker.formatDate("mm/dd/yy", vdsDefault));
											}

										}
									});
									if (custom_event_purpose == "Donation") {
										$("#sankalpaOptions").append("<option value='Other Amount'>Other Amount</option>");
									}

									$('#sankalpaOptions').change();

								}
							});

							$('#modal_table').dataTable().fnClearTable();
							$('#modal_table').dataTable().fnDraw();
							$('#modal_table').dataTable().fnDestroy();
							$("#modal_table").empty();
						});


						// Custom Sankalpa is changed at the popup box
						$('#sankalpaOptions').on('change', function() {
							var id = $('#sankalpaOptions').val();
							var additionalsankalpa = 1;
							var powerRole = <?php echo json_encode($generateRazorpayReceipt); ?>;

							if (id == "Other Amount") {
								$('#sankalpaAmount').val('');
								$('#sankalpaDesc').val('Other Amount');
								$('#sankalpaPass').val('0');
								$("#customSankalpaText").prop("hidden", false);
								$('#sankalpaAmount').prop("readonly", false);
								$('#sankalpaAmount').focus();
							} else if (id == 0) {
								$("#customSankalpaText").prop("hidden", true);
								$("#standardSankalpaText").prop("hidden", false);
								$('#sankalpaOptions').prop("hidden", true);
								$('#sankalpaOptionsLbl').prop("hidden", true);
								$('#sankalpaOptions').val("");
								if (powerRole == 1) {
									$('#sankalpaAmount').prop("readonly", false);
								} else {
									$('#sankalpaAmount').prop("readonly", true);
								}
							} else {
								$("#customSankalpaText").prop("hidden", false);
								$('#sankalpaOptions').val(id);
								$('#sankalpaDesc').val("");
								$('#sankalpaPass').val("");
								$('#sankalpaAmount').val("");
								if (powerRole == 1) {
									$('#sankalpaAmount').prop("readonly", false);
								} else {
									$('#sankalpaAmount').prop("readonly", true);
								}

								$.ajax({
									type: "GET",
									url: "/wp-content/themes/vds/api_get_sankalpa_amount.php?id=" + id + '&additionalsankalpa=' + additionalsankalpa,
									dataType: 'json',
									success: function(data) {

										$.each(data, function(i, value) {

											if (additionalsankalpa == 1) {
												$amount = value.sankalpa_amount;
												$sankalpaPass = value.sankalpa_pass;
												$('#sankalpaDesc').val(value.sankalpa_description);
												$('#sankalpaPass').val(value.sankalpa_pass);
												$('#sankalpaAmount').val(value.sankalpa_amount);
											} else {
												$amount = value.amount;
												$sankalpaPass = value.passfor;
												$('#sankalpaPass').val(value.passfor);
												$('#sankalpaAmount').val(value.amount);
											}
											$('#sankalpaAmount').change();
										});
									}
								});
							}
						});

						$('#save').click(function() {
							$('#DescModal').modal('hide');
							var eventname = $('#custom_event_name').val();
							var eventID = $('#custom_event_id').val();
							//Replace the E again with space
							let eventTableID = eventID.replace("E", "");
							var action = 'getEmployee';

							//Assign the selected users ID i.e Hostid to the cart's userid.
							var userid = $('#hostid').val();
							//user is not existing in system.cart items are assigned to cashier and he settles it
							if ((userid == null) || (userid == '') || (userid == 0)) {
								var userid = '<?php echo $userid; ?>';
							}
							var userfullname = $('#fullname').val();
							var sankalpaOptionID = $('#sankalpaOptions').val();
							var sankalpaDesc = $('#sankalpaDesc').val();
							var sankalpaAmount = $('#sankalpaAmount').val();

							var cartParams = {};
							cartParams.eventTableID = eventTableID;
							cartParams.action = action;
							cartParams.userid = userid;
							cartParams.username = userfullname;

							cartParams.additionalSankalpa = '1';
							cartParams.sankalpaOptionID = sankalpaOptionID;
							cartParams.sankalpaDesc = sankalpaDesc;
							cartParams.amount = sankalpaAmount;
							cartParams.eventDate = $('#custom_event_date').val();
							cartParams.attending = $('#attending').val();

							var testingMode = '<?php echo $testingmode; ?>'; //1 for testing mode 0 for live
							cartParams.testingMode = testingMode;

							var cartParamsJSON = JSON.stringify(cartParams);
							//alert(cartParamsJSON);

							$.ajax({
								async: false,
								url: "/wp-content/themes/vds/api_cart_options.php",
								method: "POST",
								data: {
									eventTableID: eventTableID,
									action: action,
									userid: userid,
									cartParams: cartParamsJSON
								},
								dataType: "json",
								success: function(data) {
									if (data[0].id == eventTableID) {
										alert("The Event " + eventname + " [ ID : E" + eventTableID + " ] with Rs." + sankalpaAmount + " is updated with one more sankalpa in your list!");
									} else {
										alert("The Event " + eventname + " [ ID : E" + eventTableID + " ] with Rs." + sankalpaAmount + " is successfully added to your cart!");
									}

									$("span#cart_count").html(data[0].totalCartItems);
									$("span#li_cart_count").html(data[0].totalCartItems);
								},
								error: function(request, status, error) {
									console.log("There was an error: ", request.responseText);
									alert("An error occurred while processing your request. Please try again later.");
								}
							});

							getCartListOffline();

						});

						if ($('#Amount').val() > 2100) {
							$('#identityProof').show();
						}

						$('#Amount').on('change', function() {
							if (this.value > 2100) {
								$('#identityProof').show();
							} else {
								$('#identityProof').hide();
							}
							if (this.value > 700000) {
								alert("Amount is more than 700000 hence you are no authorized to provide receipts/Invoices. Please check with the Head of Finance !")
								$('#contribute').prop("disabled", true);
								$('#contribute').prop("hidden", true);
							}
						});

						//******************** Dynamically adding card fields *************
						var i = 0;
						$('#add').click(function() {
							i++;
							$('#dynamic_field').append('<tr id="row' + i + '" class="dynamic-added"><td>Card No.<input type="text" name="dbankCard[]" placeholder="Last 4 digits of card" class="form-control name_list" required /></td><td>Bank Name<input type="text" name="dbankName[]" placeholder="Enter your Bank Name" class="form-control name_list" required /></td><td>Amount<input type="text" name="dbankAmount[]" placeholder="Enter the Amount" class="form-control name_list" required /></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');

						});

						$(document).on('click', '.btn_remove', function() {
							var button_id = $(this).attr("id");
							$('#row' + button_id + '').remove();
						});
						//******************** Dynamically adding card fields ends ***********

						$("#chqdated").datepicker({
							changeMonth: true,
							changeYear: true,
							dateFormat: 'dd M yy',
							showButtonPanel: true
						});

						$("#razorpay_transaction_date").datepicker({
							changeMonth: true,
							changeYear: true,
							dateFormat: 'dd M yy',
							showButtonPanel: true
						});

						$('#searchResults').hide();
						$('.usersearch').change(function() {
							console.log($('#search-email').val());
							var whatProcess = "get_registered_users";
							var data = {
								'token': 'k9CQ9x!7NQv66PJ',
								'process': 'get_registered_users',
								'name': $('#search-name').val(),
								'mobile': $('#search-mobile').val(),
								'email': $('#search-email').val()
							};

							$.post('/wp-content/themes/vds/api/user_search.php', data, function(response) {
								var userList = JSON.parse(response.trim());
								$('#userList').empty();
								$.each(userList, function(index, user) {
									$('#userList').append(`'<tr onclick='selectUser(${JSON.stringify(user)})' role="button">'+
											'<th scope="row" class="align-middle">${user.full_name}</th>'						
											'<td class="align-middle">${user.email}</td>'
											'<td class="align-middle">${user.user_id ? user.user_id : ""}</td>'
											'<td class="align-middle">${user.mobilenumber} | ${user.gender} | ${user.address} | ${user.city} | ${user.state} | ${user.pincode} | ${user.gotra} | ${user.nakshatra} | ${user.rashi}</td>'								
										'</tr>' `);
								});

								$('#searchResults').show();
							});
						});

						window.selectUser = function(user) {
							$('#hostid').val(user.user_id);
							$('#email').val(user.email);
							$('#fullname').val(user.full_name);
							$('#mobile').val(user.mobilenumber);
							$('#address').val(user.address);
							$('#city').val(user.city);
							$('#state').val(user.state);
							$('#pincode').val(user.pincode);
							$('#gender').val(user.gender);
							$('#gotra').val(user.gotra);
							$('#nakshatra').val(user.nakshatra);
							$('#rashi').val(user.rashi);
							//$('#hostid').change();
							Swal.fire({
								title: 'User Selected',
								text: `You have selected ${user.full_name} with email ${user.email}. Modify the details if you want to change and proceed with the registration.`,
								icon: 'success',
								confirmButtonText: 'OK'
							});
							$('#searchResults').hide();
						}

						//e.preventDefault();

					}); // ******close of the main document.ready block	*******	
					//************************************ DOCUMENT.READY BLOCK ENDS **************************************************


					/* Formatting function for row details - modify as you need */
					function eventformat(d) {
						// `d` is the original data object for the row
						//var eventsTableID = $(this).attr("id");
						var eventsTableID = d[1];
						if (d[18] == 1) {
							var amount = "More Options";
						} else {
							var amount = d[21];
						}

						var coordinatorName, coordinatorMobile;
						var organiserName, organiserMobile;
						var eventDescription, specialNotes;

						$.ajax({
							async: false,
							type: "GET",
							url: "/wp-content/themes/vds/api_get_event_details.php?event_id=" + eventsTableID,
							dataType: 'json',
							success: function(data) {
								coordinatorName = data[0].coordinatorName;
								coordinatorMobile = data[0].coordinatorMobile;
								organiserName = data[0].organiserName;
								organiserMobile = data[0].organiserMobile;
								eventDescription = data[0].eventDescription;
								specialNotes = data[0].specialNotes;
							}
						});

						return '<table align="left" cellpadding="0" cellspacing="0" border="1" style="padding-left:10px;">' +
							'<tr>' +
							'<td style="width:10%"><b>Description :</b></td>' +
							'<td style="width:90%" align="left" >' + eventDescription + '</td>' +
							'</tr>' +
							'<tr align="left" >' +
							'<td text-align="left"><b>Amount:</b></td>' +
							'<td text-align="left" >' + amount + '</td>' +
							'</tr>' +
							'<tr align="left">' +
							'<td align="left"><b>Coordinator :</b></td>' +
							'<td>' + coordinatorName + ' [ ' + coordinatorMobile + ' ] ' + '</td>' +
							'</tr>' +
							'<tr align="left">' +
							'<td align="left"><b>Organiser :</b></td>' +
							'<td>' + organiserName + ' [ ' + organiserMobile + ' ] ' + '</td>' +
							'</tr>' +
							'<tr align="left">' +
							'<td align="left"><b>Special Notes :</b></td>' +
							'<td>' + specialNotes + '</td>' +
							'</tr>' +
							'</table>';
					}

					function confirmSubmit() {
						var fullnamecart = jQuery('#fullnamecart').val();
						var hostaddresscart = jQuery('#hostaddresscart').val();
						var hostcitycart = jQuery('#hostcitycart').val();
						var hoststatecart = jQuery('#hoststatecart').val();

						var hostmobilecart = jQuery('#hostmobilecart').val();
						var hostemailcart = jQuery('#hostemailcart').val();
						var finalAmount = jQuery('#Amount').val();

						var showText = "The Receipts will be generated with following details : \n" +
							"Name : " + fullnamecart + "\n" +
							"Address : " + hostaddresscart + "\n" + hostcitycart + "," + hoststatecart + "\n \n" +
							"The Receipts will be sent to the following : \n" +
							"Mobile : " + hostmobilecart + "\n" +
							"e-Mail : " + hostemailcart + "\n\n";

						if ((fullnamecart != null) && (finalAmount != 0)) {
							var agree = confirm(showText + "Have you collected the payments and are you sure to generate receipts ?");
						} else {
							var agree = confirm("Please select the Events and fill the Donor Details !!! ");
						}
						if (agree)
							return true;
						else
							return false;
					}
					window.confirmSubmit = confirmSubmit;  // make it global so the inline onClick can call it

					function getActions(event_status, id, event_id, passedArray) {

						var sel = "<div class='left' style='z-index: 1000'> " +
							"<div class='btn-group pull-left'> " +
							"<a class='btn deepPink-bgcolor  btn-outline dropdown-toggle' data-toggle='dropdown'> <i style='font-size:14px' class='fa fa-tasks'></i>Actions " +
							"<i class='fa fa-angle-down'></i>" +
							"</a>" +
							"<ul class='dropdown-menu pull' style='z-index: 2000'>";
						switch (event_status) {
							case "Requested":
								break;
							default:
								sel +=
									"<li>" +
									"<a  target='_blank' href='/sevadetails/?transid=" + id + "'>" +
									"<i class='fa fa-cloud'></i> More Details </a>" +
									"</li>" +
									"<li>" +
									"<a  target='_blank' href='/coordinator_reports?q_eventID=" + 'E' + event_id + "'>" +
									"<i class='fa fa-users'></i> Participants List </a>" +
									"</li>";
						}
						sel + "</ul>" +
							"</div>" +
							"</div>" +
							"</div>";
						return sel;
					}

					//******************** Dynamically adding card fields ends ***********

					jQuery(function($) {

						$("#datefrom").datepicker({
							changeMonth: true,
							changeYear: true,
							showButtonPanel: true,
							minDate: 0,
							dateFormat: 'mm/dd/yy'
						}).on("change", function() {
							$("#dateto").datepicker("option", "minDate", this.value);
						});
						$("#dateto").datepicker({
							changeMonth: true,
							changeYear: true,
							showButtonPanel: true,
							dateFormat: 'mm/dd/yy'
						}).on("change", function() {
							$("#datefrom").datepicker("option", "maxDate", this.value);
						});;

						$("#q_event_date").datepicker({
							changeMonth: true,
							changeYear: true,
							showButtonPanel: true,
							dateFormat: 'mm/dd/yy'
						});

						$("#custom_event_date").datepicker({
							changeMonth: true,
							changeYear: true,
							showButtonPanel: true,
							minDate: new Date(),
							dateFormat: 'mm/dd/yy'
						});

					});
				</script>

				<!-- Bootstrap needed for the popup to load. I removed 	-->
				<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
				<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
				<!-- start js include path -->
				
				<script src="/wp-content/themes/vds/assets/asset/plugins/popper/popper.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/plugins/jquery-blockui/jquery.blockui.min.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

				<!-- data tables -->
				<script src="/wp-content/themes/vds/assets/asset/plugins/datatables/jquery.dataTables.min.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/plugins/datatables/plugins/bootstrap/dataTables.bootstrap4.min.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/js/pages/table/table_data.js"></script>

				<!-- Common js-->
				<script src="/wp-content/themes/vds/assets/asset/js/app.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/js/layout.js"></script>
				<script src="/wp-content/themes/vds/assets/asset/js/theme-color.js"></script>
				<!-- Material -->
				<script src="/wp-content/themes/vds/assets/asset/plugins/material/material.min.js"></script>
				<!-- Material Design Lite CSS -->
				<link rel="stylesheet" href="/wp-content/themes/vds/assets/asset/plugins/material/material.min.css">
				<link rel="stylesheet" href="/wp-content/themes/vds/assets/asset/css/material_style.css">
				<!-- Theme Styles -->
				<link href="/wp-content/themes/vds/assets/asset/css/theme/light/theme_style.css" rel="stylesheet" id="rt_style_components" type="text/css" />
				<link href="/wp-content/themes/vds/assets/asset/css/theme/light/style.css" rel="stylesheet" type="text/css" />
				<link href="/wp-content/themes/vds/assets/asset/css/plugins.min.css" rel="stylesheet" type="text/css" />
				<link href="/wp-content/themes/vds/assets/asset/css/responsive.css" rel="stylesheet" type="text/css" />
				<link href="/wp-content/themes/vds/assets/asset/css/theme/light/theme-color.css" rel="stylesheet" type="text/css" />
				<!-- favicon -->
				<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
				<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.all.min.js"></script>

		</body>

		</html>
	<?php
} // close of the function
add_shortcode('vds_manage_cart_offline_single_payments', 'vds_manage_cart_offline_single_payments');
	?>