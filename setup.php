<?php


ini_set('display_errors', 0); // Do not display errors on the webpage
ini_set('log_errors', 1);     // Enable error logging
ini_set('error_log', '/var/www/automation.datainnovation.io/html/hi.log');
error_reporting(E_ALL);






include 'includes/db.php';
include 'includes/functions.php';
include 'login_lk.php';
$client_id = '78xtj6e2mru8za';
$redirect_uri = urlencode('https://automation.datainnovation.io/call_back.php');
$state = 'RANDOM_STRING'; // Prevent CSRF attacks
$scope = urlencode('openid profile r_ads_reporting r_organization_social rw_organization_admin w_member_social r_learningdata r_ads w_organization_social rw_ads r_basicprofile r_organization_admin email r_1st_connections_size');
session_start();
$user_id = $_SESSION['user_id'];

function getUploadErrorMessage($code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];
    return $errors[$code] ?? 'Unknown upload error.';
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("File upload error: ");        

    
    global $connection;

    // Collect form data and sanitize
    $company_name = isset($_POST['FisrtName']) ? mysqli_real_escape_string($connection, $_POST['FisrtName']) : '';
    $profession = isset($_POST['LastName']) ? mysqli_real_escape_string($connection, $_POST['LastName']) : '';

    $about = isset($_POST['about']) ? mysqli_real_escape_string($connection, $_POST['about']) : '';

    $countries_of_audience = isset($_POST['InputCountry']) ? mysqli_real_escape_string($connection, $_POST['InputCountry']) : '';
    $audience_language = isset($_POST['InputLanguage']) ? mysqli_real_escape_string($connection, $_POST['InputLanguage']) : '';

    // Handle file upload for logo (if any)
    $logo = '';
    if (isset($_FILES['files']) && $_FILES['files']['error'] === 0) {
        error_log("Received file: " . print_r($_FILES['files'], true));

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                error_log("Failed to create uploads directory: $target_dir");
                echo "Failed to create upload directory.";
                exit();
            }
        }

        // Generate the target file path
        $logo = $target_dir . basename($_FILES['files']['name']);

        // Move the uploaded file to the target location
        if (move_uploaded_file($_FILES['files']['tmp_name'], $logo)) {
            error_log("File uploaded successfully: $logo");
        } else {
            error_log("Failed to move uploaded file to: $logo");
            echo "Error uploading file.";
            exit();
        }
    } else {
        error_log("No file uploaded or file upload error: " . ($_FILES['files']['error'] ?? 'No error code'));
    }
    
    $author = isset($_POST['author']) ? mysqli_real_escape_string($connection, $_POST['author']) : '';
    $audience = isset($_POST['audience']) ? mysqli_real_escape_string($connection, $_POST['audience']) : '';
    $mechanics = isset($_POST['mechanics']) ? mysqli_real_escape_string($connection, $_POST['mechanics']) : '';
    $objective = isset($_POST['objective']) ? mysqli_real_escape_string($connection, $_POST['objective']) : '';
    $content_keywords = isset($_POST['content_keywords']) ? mysqli_real_escape_string($connection, $_POST['content_keywords']) : '';
    $not_allowed_keywords = isset($_POST['not_allowed_keywords']) ? mysqli_real_escape_string($connection, $_POST['not_allowed_keywords']) : '';
    $content_with_images = isset($_POST['content_with_images']) ? 1 : 0;
    $preferred_days = isset($_POST['preferred_days']) ? implode(",", $_POST['preferred_days']) : '';
    $preferred_time = isset($_POST['preferred_time']) ? implode(",", $_POST['preferred_time']) : '';
    $example_content = isset($_POST['example_content']) ? mysqli_real_escape_string($connection, $_POST['example_content']) : '';


  
    
    // Handle checkbox for content with images

    // Handle multi-select fields for days and time
    
    // Example content textarea

    // Debugging code to check variable values
    $variables = [

        '$about' => $about,

        '$company_name' => $company_name,
        '$profession' => $profession,
        '$countries_of_audience' => $countries_of_audience,
        '$audience_language' => $audience_language,
        '$logo' => $logo,
        '$author' => $author,
        '$audience' => $audience,
        '$mechanics' => $mechanics,
        '$objective' => $objective,
        '$content_keywords' => $content_keywords,
        '$not_allowed_keywords' => $not_allowed_keywords,
        '$content_with_images' => $content_with_images,
        '$preferred_days' => $preferred_days,
        '$preferred_time' => $preferred_time,
        '$example_content' => $example_content
    ];
    

    foreach ($variables as $var_name => $value) {
        if (!isset($value)) {
            error_log("Variable $var_name is not set.");
        } else {
            error_log("Variable $var_name = " . var_export($value, true));
        }
    }

    // Insert data into database
    $query = "INSERT INTO content_setup (
        company_name, profession, about, countries_of_audience, audience_language, logo, author, audience, mechanics, objective, content_keywords, not_allowed_keywords, content_with_images, preferred_days, preferred_time, example_content
    ,user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $connection->prepare($query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $connection->error]);
        exit();
    }

    // Bind parameters without ampersands
// Corrected bind_param without $keywords
    $stmt->bind_param(
        'ssssssssssssisssi',
        $company_name, $profession, $about, $countries_of_audience, $audience_language, $logo,
        $author, $audience, $mechanics, $objective,
        $content_keywords, $not_allowed_keywords, $content_with_images, $preferred_days,
        $preferred_time, $example_content, $user_id
    );

    // Set header to return JSON
    header('Content-Type: application/json');
    if ($stmt->execute()) {
        // Success
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Data inserted successfully!']);
    } else {

        // Execution failed
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();

    // End the script to prevent any additional output
    exit();
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
					
				</div>
			  <!--end breadcrumb-->

			  <!--start stepper one--> 
			   
			    <hr>
                <div id="stepper1" class="bs-stepper">
  <div class="card">
    <div class="card-header">
      <div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
        
        <!-- Step 1: Profile Information -->
        <div class="step" data-target="#test-l-1">
          <div class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="test-l-1">
            <div class="bs-stepper-circle">1</div>
            <div class="">
              <h5 class="mb-0 steper-title">Profile Information</h5>
              <p class="mb-0 steper-sub-title">Enter Content Sender Information</p>
            </div>
          </div>
        </div>

        <div class="bs-stepper-line"></div>

        <!-- Step 2: Targeted Audience -->
        <div class="step" data-target="#test-l-2">
          <div class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="test-l-2">
            <div class="bs-stepper-circle">2</div>
            <div class="">
              <h5 class="mb-0 steper-title">Targeted Audience</h5>
              <p class="mb-0 steper-sub-title">Specify your target audience details</p>
            </div>
          </div>
        </div>

        <div class="bs-stepper-line"></div>

        <!-- Step 3: Content Setup -->
        <div class="step" data-target="#test-l-3">
          <div class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="test-l-3">
            <div class="bs-stepper-circle">3</div>
            <div class="">
              <h5 class="mb-0 steper-title">Content Setup</h5>
              <p class="mb-0 steper-sub-title">Setup Your Content Options</p>
            </div>
          </div>
        </div>

        <div class="bs-stepper-line"></div>

        <!-- Step 4: Targeted Platforms -->
    <!--    <div class="step" data-target="#test-l-4">
          <div class="step-trigger" role="tab" id="stepper1trigger4" aria-controls="test-l-4">
            <div class="bs-stepper-circle">4</div>
            <div class="">
              <h5 class="mb-0 steper-title">Targeted Platforms</h5>
              <p class="mb-0 steper-sub-title">Setup connected platforms</p>
            </div>
          </div>
        </div>
-->
      </div>


      <div class="card-body">
					
                    <div class="bs-stepper-content">
                    <form id="myForm" method="POST" enctype="multipart/form-data">

                        <div id="test-l-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
                          <h5 class="mb-1">Your Personal Information</h5>
                          <p class="mb-4">Enter your personal information</p>

                          <div class="row g-3">
                              <div class="col-12 col-lg-6">
                                  <label for="FisrtName" class="form-label">Company Name / Personal Name</label>
                                  <input name="FisrtName" type="text" class="form-control" id="FisrtName" placeholder="Company Name or Personal Name">
                              </div>
                              <div class="col-12 col-lg-6">
                                  <label for="LastName" class="form-label">Field / Profession</label>
                                  <input name="LastName" type="text" class="form-control" id="LastName" placeholder="Field or Profession">
                              </div>

                              <div class="col-12 col-lg-6">
                                  <label for="LastName" class="form-label">About</label>
                                  <input name="LastName" type="text" class="form-control" id="about" placeholder="Provide a brief description of the company or entity in just a few words.">
                              </div>
                              <div class="col-12 col-lg-6">
                                  <label for="InputCountry" class="form-label">Countries of Audience</label>
                                  <select name="InputCountry" class="form-select" id="InputCountry" aria-label="Default select example">
                                      <option selected>---</option>
                                      <option value="1">Spain</option>
                                      <option value="2">Italy</option>
                                      <option value="3">Whole OF Europe</option>
                                      <option value="3">Global</option>

                                    </select>
                              </div>
                              <div class="col-12 col-lg-6">
                                  <label for="PhoneNumber" class="form-label">Audience Langugae</label>
                                  <select name="InputLanguage" class="form-select" id="InputLanguage" aria-label="Default select example">
                                      <option selected>---</option>
                                      <option value="1">English</option>
                                      <option value="2">Spanish</option>
                                      <option value="3">Italian</option>
                                      <option value="3">Dutch</option>
                                      <option value="3">French</option>


                                    </select>								</div>
                          
                                    <hr/>

                              
                                    <div class="col-12 col-lg-6">
                                    <h6 class="form-label" >Logo (if applicable)</h6>
                      <div>
        <label for="fileUpload">Upload Logo:</label>
        <input type="file" id="fileUpload" name="files">
    </div>
                  </div>




                              <div class="col-12 col-lg-6">
                              <button type="button" class="btn btn-primary px-4" onclick="stepper1.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>

                              </div>
                          </div><!---end row-->
                          
                        </div>

                              




                      <div id="test-l-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger2">
            <h5 class="mb-1">Targeted Audience Information</h5>
            <p class="mb-4">Provide details about your target audience.</p>

            <!-- Targeted Audience Form -->
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <label for="author" class="form-label">Who?: Author / Publisher / Company</label>
                <input type="text" class="form-control" id="author" name="author" placeholder="Author / Publisher / Company">
                </div>
              <div class="col-12 col-lg-6">
                <label for="audience" class="form-label">To Whom?: Audience / Niche / B2B or B2C</label>
                <input type="text" class="form-control" id="audience" name="audience" placeholder="Audience / Niche / B2B or B2C">
                </div>
           
              <div class="col-12 col-lg-6">
                <label for="mechanics" class="form-label">How?: Mechanics / CTA / Formats</label>
                <input type="text" class="form-control" id="mechanics" name="mechanics" placeholder="Mechanics / CTA / Formats">
                </div>
           
              <div class="col-12 col-lg-6">
                <label for="objective" class="form-label">Why?: End Goal, Objective - interactions, activations, notoriety</label>
                <input type="text" class="form-control" id="objective" name="objective" placeholder="End Goal, Objective">
                </div>
            </div>

            <br>
            <br>


            <div class="col-12">
              <div class="d-flex align-items-center gap-3">
              <button type="button" class="btn btn-outline-secondary px-4" onclick="stepper1.previous()">
                                                    <i class='bx bx-left-arrow-alt me-2'></i>Previous
                                                </button>
                                                <button type="button" class="btn btn-primary px-4" onclick="stepper1.next()">
                                                    Next<i class='bx bx-right-arrow-alt ms-2'></i>
                                                </button>
              </div>
            </div>
          </div>




                        <div id="test-l-3" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger3">


                        

                          <h5 class="mb-1">Content Type and Options</h5>
                          <p class="mb-4">Enter Your Content Target.</p>














                          <div class="col-12 col-lg-6">
                          <h6 class="mb-1">Enter content topics Keywords</h6>
                          <br>						


                          <input type="text" class="form-control" name="content_keywords" data-role="tagsinput" value="Business,Financial">
                                  
                      </div>	
                      
                      <br>				
                      <br>						
      

                      <div class="col-12 col-lg-6">
                          <h6 class="mb-1">Enter Not Allowed Keywords (Content keyword- compititors name)</h6>
                          <br>						


                          <input type="text" class="form-control" name="not_allowed_keywords" data-role="tagsinput" value="">
                                  
                      </div>	
                      <br>				
                      <br>						
      
                              <div class="col-12 col-lg-6">
                              <h6 class="mb-1">Do you prefer Content with Images ?</h6>
                              <br>						


                              <input class="form-check-input" type="checkbox" value="1" id="flexCheckDefault" name="content_with_images">
                              <label class="form-check-label" for="flexCheckDefault">Content with Images</label>
                              </div>

                              <br>						
                              <br>	
                              
                              


                              <div class="col-12 col-lg-6">
                              <h6 class="mb-1">What Days Should We send you content ?</h6>
                              <br>						


                             
                          <div class="mb-4">
                          <select class="form-select" id="preferred_days" name="preferred_days[]" multiple>

                          <option >Sunday</option>
                                  <option >Monday</option>
                                  <option >Tuesday</option>
                                  <option>Wednesday</option>
                                  <option>Thursday</option>
                                  <option>Friday</option>
                                  <option>Saturday</option>
                                  <option>Sunday</option>
                              
                              </select>
                          </div>

                          </div>

                          <br>




                          
                          <div class="col-12 col-lg-6">
                              <h6 class="mb-1">What Time Should We send you content ?</h6>
                              <br>						


                              <div class="mb-4">
                              <select class="form-select" id="preferred_time" name="preferred_time[]" multiple>
                              <option >(8 am - 1 pm)</option>
                                  <option >(1 am - 5 pm)</option>
                                  <option >(5 am - 8 pm)</option>
                      
                              
                              </select>
                          </div>




                              </div>

              <br>




                              <div class="col-12 col-lg-6">
                              <h6 class="mb-1">Example Content (optional)</h6>
                              <br>						


                              <textarea class="form-control" id="input11" name="example_content" placeholder="Copy and paste here an example of the content you like to have ..." rows="5"></textarea>

                              <br>						
                              <br>		


                      
                              <div class="col-12">
                                  <div class="d-flex align-items-center gap-3">
                                  <button type="button" class="btn btn-outline-secondary px-4" onclick="stepper1.previous()">
                                                    <i class='bx bx-left-arrow-alt me-2'></i>Previous
                                                </button>
                                                <button type="button" id="submitButton" class="btn btn-success px-4">Submit</button>

                                  </div>
                              </div>
                          </div><!---end row-->
                          
                        </div>



        


                        <div id="test-l-4" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger4">
                          <h5 class="mb-1">Platform To Send Content</h5>
                          <p class="mb-4">Choose Your Content Platform</p>

                          <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3">



                          <div class="col">
                      <div class="card radius-10">
                          <div class="card-body">
                              <div class="text-center">
                                  <div class="widgets-icons rounded-circle mx-auto bg-light-info text-info mb-3"><i class='bx bxl-linkedin-square'></i>
                                  </div>
                                  <p class="mb-0 text-secondary">Linkedin</p>
                                  <a href="<?php echo "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}"; ?>">Login</a>
                                  </div>
                          </div>
                      </div>
                  </div>
                  <div class="col">
                      <div class="card radius-10">
                          <div class="card-body">
                              <div class="text-center">
                                  <div class="widgets-icons rounded-circle mx-auto bg-light-primary text-primary mb-3"><i class='bx bxl-facebook-square'></i>
                                  </div>
                                  <p class="mb-0 text-secondary">Facebook</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col">
                      <div class="card radius-10">
                          <div class="card-body">
                              <div class="text-center">
                                  <div class="widgets-icons rounded-circle mx-auto bg-light-danger text-danger mb-3"><i class='bx bxl-twitter'></i>
                                  </div>
                                  <p class="mb-0 text-secondary">Twitter</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  
                  <div class="col">
                      <div class="card radius-10">
                          <div class="card-body">
                              <div class="text-center">
                                  <div class="widgets-icons rounded-circle mx-auto bg-light-danger text-primary mb-3"><i class='bx bxl-wordpress'></i>
                                  </div>
                                  <p class="mb-0 text-secondary">Wordpress</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-12">
                                  <div class="d-flex align-items-center gap-3">
                                  <button type="button" class="btn btn-primary px-4" onclick="stepper1.previous()">
                                                    <i class='bx bx-left-arrow-alt me-2'></i>Previous
                                                </button>
                                                <!-- Submit Button -->
                                                <button type="button" id="submitButton" class="btn btn-success px-4">Submit</button>
                                  </div>
                              </div>
                  
                  
                  
              </div>
                          
                        </div>

                  
                      </form>
                    </div>
                  
                  </div>
                 </div>
               </div>
              <!--end stepper one--> 

              
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
        var submitButton = document.getElementById('submitButton');
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default form submission
                console.log('Submit button clicked');

                var form = document.getElementById('myForm');
                var formData = new FormData(form);

                // Log formData entries
                for (var pair of formData.entries()) {
                    console.log(pair[0]+ ', ' + pair[1]);
                }

               fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.text(); // Get the raw response text
            })
            .then(function(text) {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    alert('An unexpected error occurred.');
                }
            })
.catch(function(error) {
    console.error('Error:', error);
});

            });
        } else {
            console.error('Submit button not found.');
        }
    });
</script>


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
