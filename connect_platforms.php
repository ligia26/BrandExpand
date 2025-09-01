<?php
ini_set('display_errors', 0); // Do not display errors on the webpage
ini_set('log_errors', 1);     // Enable error logging
error_reporting(E_ALL);

ini_set('error_log', '/var/www/automation.datainnovation.io/html/hi.log');
error_reporting(E_ALL);

include 'includes/db.php';
include 'includes/functions.php';
// include 'login_lk.php'; // Remove if not needed
$client_id = '78xtj6e2mru8za';
$redirect_uri = urlencode('https://automation.datainnovation.io/call_back.php');
$state = 'RANDOM_STRING'; // Prevent CSRF attacks
$scope = urlencode('openid profile r_ads_reporting r_organization_social rw_organization_admin w_member_social r_learningdata r_ads w_organization_social rw_ads r_basicprofile r_organization_admin email r_1st_connections_size');
session_start();

$user = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$companyId = getCompanyIdByUserId($user);

if ($role == 1) {
    $companiesQuery = "SELECT id, company_name FROM content_setup";
    $companiesResult = mysqli_query($connection, $companiesQuery);

    $companies = [];
    if ($companiesResult) {
        $companies = mysqli_fetch_all($companiesResult, MYSQLI_ASSOC);
    }

    // Default to the first company if none is selected
    if (isset($_POST['company_id'])) {
        $company_id = intval($_POST['company_id']);
    } elseif (count($companies) > 0) {
        $company_id = $companies[0]['id'];
    } else {
        $company_id = null; // Handle empty company list
    }
} else {
    // For non-admin, get the company ID associated with the user
    $company_id = getCompanyIdByUserId($user);
}

// Fetch credentials for the selected or associated company
if ($company_id) {
    $query = "SELECT wp_url, wp_user, wp_pass, ma_url, ma_user, ma_pass 
              FROM company_credentials 
              WHERE company_id = $company_id";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $wp_url = $data['wp_url'] ?? '';
        $wp_user = $data['wp_user'] ?? '';
        $wp_pass = $data['wp_pass'] ?? '';
        $ma_url = $data['ma_url'] ?? '';
        $ma_user = $data['ma_user'] ?? '';
        $ma_pass = $data['ma_pass'] ?? '';
    } else {
        $wp_url = $wp_user = $wp_pass = '';
        $ma_url = $ma_user = $ma_pass = '';
    }
} else {
    $wp_url = $wp_user = $wp_pass = '';
    $ma_url = $ma_user = $ma_pass = '';
}

// Handle WordPress form submission
// Handle WordPress form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_wp'])) {
    global $connection;
    if (!$connection) {
        error_log('Failed to connect to database: ' . mysqli_connect_error());
        echo "<script>alert('Database connection failed.');</script>";
        exit();
    }
    
    // Collect WordPress credentials from the form
    $wp_url = $_POST['wp_url'];
    $wp_user = $_POST['wp_user'];
    $wp_pass = $_POST['wp_pass'];

    // Sanitize input
    $wp_url = mysqli_real_escape_string($connection, $wp_url);
    $wp_user = mysqli_real_escape_string($connection, $wp_user);
    $wp_pass = mysqli_real_escape_string($connection, $wp_pass);

    // For now, set company_id to 1
    $company_id = 2;

    // Insert or update the record in the database
    // First, check if a record exists for company_id = 1
    $query = "SELECT id FROM company_credentials WHERE company_id = $company_id";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        error_log('Database query failed: ' . mysqli_error($connection));
    }

    if (mysqli_num_rows($result) > 0) {
        // Record exists, update it
        $query = "UPDATE company_credentials SET wp_url = '$wp_url', wp_user = '$wp_user', wp_pass = '$wp_pass' WHERE company_id = $company_id";
    } else {
        // No record exists, insert a new one
        $query = "INSERT INTO company_credentials (company_id, wp_url, wp_user, wp_pass) VALUES ($company_id, '$wp_url', '$wp_user', '$wp_pass')";
    }
    $result = mysqli_query($connection, $query);

    // Prepare the response
    if (!$result) {
        error_log('Database query failed: ' . mysqli_error($connection));
        echo "<script>alert('An error occurred while saving WordPress credentials.');</script>";
    } else {
        echo "<script>alert('WordPress credentials saved successfully.');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_mautic'])) {
    $ma_url = $_POST['ma_url'];
    $ma_user = $_POST['ma_user'];
    $ma_pass = $_POST['ma_pass'];

    // Sanitize input
    $ma_url = mysqli_real_escape_string($connection, $ma_url);
    $ma_user = mysqli_real_escape_string($connection, $ma_user);
    $ma_pass = mysqli_real_escape_string($connection, $ma_pass);

    // Check if a record exists for company_id = 1
    $query = "SELECT id FROM company_credentials WHERE company_id = $company_id";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0) {
        // Update existing record
        $query = "UPDATE company_credentials SET ma_url = '$ma_url', ma_user = '$ma_user', ma_pass = '$ma_pass' WHERE company_id = $company_id";
    } else {
        // Insert a new record
        $query = "INSERT INTO company_credentials (company_id, ma_url, ma_user, ma_pass) VALUES ($company_id, '$ma_url', '$ma_user', '$ma_pass')";
    }
    $result = mysqli_query($connection, $query);

    if (!$result) {
        error_log('Database query failed: ' . mysqli_error($connection));
        echo "<script>alert('An error occurred while saving Mautic credentials.');</script>";
    } else {
        echo "<script>alert('Mautic credentials saved successfully.');</script>";
    }
}


// Flush the output buffer
ob_end_flush();
?>


<!doctype html>
<html lang="en" class="color-sidebar sidebarcolor2">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "includes/head.php"; ?>
    <title>Dashboard</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
</head>
<body>

    <div class="wrapper">
        <?php include "includes/side_menu.php"; ?>
        <?php include "includes/header.php"; ?>
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />

        <link href="assets/plugins/bs-stepper/css/bs-stepper.css" rel="stylesheet" />
        <link href="assets/plugins/input-tags/css/tagsinput.css" rel="stylesheet" />
        <link href="assets/plugins/fancy-file-uploader/fancy_fileupload.css" rel="stylesheet" />
        <link href="assets/plugins/Drag-And-Drop/dist/imageuploadify.min.css" rel="stylesheet" />


        <div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Content Setup</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Add Setup</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						<div class="btn-group">
							<button type="button" class="btn btn-primary">Settings</button>
							<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
							</button>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
								<a class="dropdown-item" href="javascript:;">Another action</a>
								<a class="dropdown-item" href="javascript:;">Something else here</a>
								<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
							</div>
						</div>
					</div>
				</div>
			  <!--end breadcrumb-->

			  <!--start stepper one--> 
			   
			    <hr>
                <div id="stepper1" class="bs-stepper">
  <div class="card">
    <div class="card-header">
      <div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
        
        <!-- Step 1: Profile Information -->
 
        <!-- Step 2: Targeted Audience -->
  
        <!-- Step 3: Content Setup -->
 

        <div class="bs-stepper-line"></div>

        <!-- Step 4: Targeted Platforms -->
        <div class="step" data-target="#test-l-1">
          <div class="step-trigger" role="tab" id="stepper1trigger4" aria-controls="test-l-4">
            <div class="">
              <h5 class="mb-0 steper-title">Targeted Platforms</h5>
              <p class="mb-0 steper-sub-title">Setup connected platforms</p>
            </div>
          </div>
        </div>

      </div>

      <div class="card-body">
    <?php if ($role == 1): ?>
        <div class="mb-3">
            <label for="companyDropdown" class="form-label">Select Company:</label>
            <form method="POST" action="">
                <select id="companyDropdown" name="company_id" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['id']; ?>" 
                            <?php echo ($company['id'] == $company_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label for="companyName" class="form-label">Company:</label>
            <input type="text" id="companyName" class="form-control" 
                   value="<?php echo htmlspecialchars(getCompanyNameById($company_id)); ?>" readonly>
        </div>
    <?php endif; ?>

    <div class="bs-stepper-content">
        <div id="test-l-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
            <h5 class="mb-1">Platform To Send Content</h5>
            <p class="mb-4">Choose Your Content Platform</p>

            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3">
                <!-- LinkedIn -->
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="widgets-icons rounded-circle mx-auto bg-light-info text-info mb-3">
                                    <i class='bx bxl-linkedin-square'></i>
                                </div>
                                <p class="mb-0 text-secondary">Linkedin</p>
                                <a href="<?php echo "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}"; ?>">Login</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WordPress -->
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="widgets-icons rounded-circle mx-auto bg-light-primary text-primary mb-3">
                                    <i class='bx bxl-wordpress'></i>
                                </div>
                                <p class="mb-0 text-secondary">WordPress</p>
                                <form id="wpForm" method="post" action="connect_platforms.php">
                                    <div class="mb-3">
                                        <label for="wp_url" class="form-label">WordPress URL:</label>
                                        <input type="text" class="form-control" name="wp_url" id="wp_url" 
                                               value="<?php echo htmlspecialchars($wp_url ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="wp_user" class="form-label">WordPress Username:</label>
                                        <input type="text" class="form-control" name="wp_user" id="wp_user" 
                                               value="<?php echo htmlspecialchars($wp_user ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="wp_pass" class="form-label">WordPress Password:</label>
                                        <input type="password" class="form-control" name="wp_pass" id="wp_pass" 
                                               value="<?php echo htmlspecialchars($wp_pass ?? ''); ?>" required>
                                    </div>
                                    <input type="submit" class="btn btn-primary" name="submit_wp" value="Save WordPress Credentials">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mautic -->
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="widgets-icons rounded-circle mx-auto bg-light-primary text-primary mb-3">
                                    <img src="/assets/images/mautic.png" alt="Mautic Icon" class="img-fluid">
                                </div>
                                <p class="mb-0 text-secondary">Mautic</p>
                                <form id="mauticForm" method="post" action="connect_platforms.php">
                                    <div class="mb-3">
                                        <label for="ma_url" class="form-label">Mautic URL:</label>
                                        <input type="text" class="form-control" name="ma_url" id="ma_url" 
                                               value="<?php echo htmlspecialchars($ma_url ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ma_user" class="form-label">Mautic Username:</label>
                                        <input type="text" class="form-control" name="ma_user" id="ma_user" 
                                               value="<?php echo htmlspecialchars($ma_user ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ma_pass" class="form-label">Mautic Password:</label>
                                        <input type="password" class="form-control" name="ma_pass" id="ma_pass" 
                                               value="<?php echo htmlspecialchars($ma_pass ?? ''); ?>" required>
                                    </div>
                                    <input type="submit" class="btn btn-primary" name="submit_mautic" value="Save Mautic Credentials">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- Plugins -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<!-- BS Stepper -->
<script src="assets/plugins/bs-stepper/js/bs-stepper.min.js"></script>
<!-- Other plugins and your custom scripts -->
<script src="assets/plugins/input-tags/js/tagsinput.js"></script>
    
    
    
    
        <script src="assets/plugins/input-tags/js/tagsinput.js"></script>

 	<script src="assets/plugins/fancy-file-uploader/jquery.ui.widget.js"></script>
	<script src="assets/plugins/fancy-file-uploader/jquery.fileupload.js"></script>
	<script src="assets/plugins/fancy-file-uploader/jquery.iframe-transport.js"></script>
	<script src="assets/plugins/fancy-file-uploader/jquery.fancy-fileupload.js"></script>
	<script src="assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    var stepper1; // Declare stepper1 in the global scope
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Stepper
        var stepper1Node = document.querySelector('#stepper1');
        if (stepper1Node) {
            stepper1 = new Stepper(stepper1Node, {
                linear: false,
                animation: true
            });
        } else {
            console.error('Element with id "stepper1" not found.');
        }

        // Attach event listener to the submit button
  
    });
</script>


    </div>
</body>
</html>
