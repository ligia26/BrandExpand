<?php


ini_set('display_errors', 0); // Do not display errors on the webpage
ini_set('log_errors', 1);     // Enable error logging
error_reporting(E_ALL);







include 'includes/db.php';
include 'includes/functions.php';
global $connection;

$query = "SELECT `first_phase`, `second_phase`, `title_phase` FROM `promts` WHERE 1 LIMIT 1";
$result = $connection->query($query);
$row = $result->fetch_assoc();

$first_phase = $row['first_phase'] ?? '';
$second_phase = $row['second_phase'] ?? '';
$title_phase = $row['title_phase'] ?? '';



if (isset($_POST['update_phases'])) {
    global $connection;
    $first_phase = $_POST['first_phase'];
    $second_phase = $_POST['second_phase'];
    $title_phase = $_POST['title_phase'];

    $sql = "UPDATE `promts` SET `first_phase` = ?, `second_phase` = ?, `title_phase` = ? WHERE 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sss", $first_phase, $second_phase, $title_phase);
    if ($stmt->execute()) {
        echo "Phases updated successfully!";
    } else {
        echo "Error updating phases: " . $stmt->error;
    }
}

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
					
				</div>
			  <!--end breadcrumb-->

			  <!--start stepper one--> 
			   
       

              <form action="" method="post">
    <div class="input-group">
        <span class="input-group-text">First 
            Phase</span>
        <textarea name="first_phase" class="form-control" aria-label="First Phase"><?php echo htmlspecialchars($first_phase); ?></textarea>
    </div>
    <hr>

    <div class="input-group">
        <span class="input-group-text">Second 
            Phase</span>
        <textarea name="second_phase" class="form-control" aria-label="Second Phase"><?php echo htmlspecialchars($second_phase); ?></textarea>
    </div>
    <hr>
    <div class="input-group">
        <span class="input-group-text">Title 
            Phase</span>
        <textarea name="title_phase" class="form-control" aria-label="Title Phase"><?php echo htmlspecialchars($title_phase); ?></textarea>
    </div>
    <button type="submit" name="update_phases" class="btn btn-primary mt-3">Update Prompts</button>
</form>



                              




              
              <!--start stepper two--> 
      
                <!--end stepper two--> 


              <!--start stepper three--> 
          
                <!--end stepper three--> 

                
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



    $(".time-picker").flatpickr({
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "Y-m-d H:i",
                });
            $('#fancy-file-upload').FancyFileUpload({
                params: {
                    action: 'fileuploader'
                },
                maxfilesize: 1000000
            });
        </script>
        <script>
            $(document).ready(function () {
                $('#image-uploadify').imageuploadify();
            })
        </script>

    </div>
</body>
</html>
