<!doctype html>
<html lang="en" class="color-sidebar sidebarcolor2">
<?php

session_start();
include "includes/head.php";
include 'includes/db.php';
global $conn;

// Check if the request is an AJAX call for filtering
$isAjaxRequest = isset($_POST['ajax']) && $_POST['ajax'] == 'true';

if ($isAjaxRequest) {
    // Only return the filtered posts content if it's an AJAX call
    $companyId = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
    $query = "SELECT `id`, `title`, `body`, `image`, `link`, `date`, `company`, `status` FROM posts";
    if ($companyId > 0) {
        $query .= " WHERE company = $companyId";
    }
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="col">';
            echo '    <div class="card radius-15">';
            echo '        <div class="card-body text-center">';
            echo '            <div class="p-4 border radius-15">';
            echo '                <img src="' . htmlspecialchars($row['image']) . '" width="110" height="110" class="rounded-circle shadow" alt="Image">';
            echo '                <h5 class="mb-0 mt-5">' . htmlspecialchars(substr($row['title'], 0, 20)) . '...</h5>';
            echo '                <p class="mb-3">' . htmlspecialchars($row['company']) . '</p>';
            echo '                <p class="mb-3">' . htmlspecialchars(substr($row['body'], 0, 30)) . '...</p>';
            echo '                <div class="list-inline contacts-social mt-3 mb-3">';
            echo '                    <a href="javascript:;" class="list-inline-item bg-facebook text-white border-0"><i class="bx bxl-facebook"></i></a>';
            echo '                    <a href="javascript:;" class="list-inline-item bg-twitter text-white border-0"><i class="bx bxl-twitter"></i></a>';
            echo '                    <a href="javascript:;" class="list-inline-item bg-google text-white border-0"><i class="bx bxl-google"></i></a>';
            echo '                    <a href="javascript:;" class="list-inline-item bg-linkedin text-white border-0"><i class="bx bxl-linkedin"></i></a>';
            echo '                </div>';
            echo '                <div class="d-grid">';
            echo '                    <a href="' . htmlspecialchars($row['link']) . '" target="_blank" class="btn btn-outline-primary radius-15">View Profile</a>';
            echo '                    <a href="edit_post.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mt-2">Edit</a>';
            echo '                    <a href="delete_post.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm mt-2">Delete</a>';
            echo '                </div>';
            echo '            </div>';
            echo '        </div>';
            echo '    </div>';
            echo '</div>';
        }
    } else {
        echo '<div class="col">No posts found for this company.</div>';
    }
    exit; // End script execution if it's an AJAX call
}
?>

<body>
    <!--wrapper-->
    <div class="wrapper">
        <?php include "includes/side_menu.php"; ?>
        <?php include "includes/header.php"; ?>
        <div class="page-wrapper">
            <div class="page-content">
                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">Articles</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Articles</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="d-lg-flex align-items-center mb-4 gap-3">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-5 radius-30" placeholder="Search Order">
                                <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="company-filter" class="form-label">Select Company</label>
                            <select class="form-select" id="company-filter">
                                <option value="">All Companies</option>
                                <?php
                                $companyQuery = "SELECT `id`, `company_name` FROM `content_setup` WHERE 1";
                                $companyResult = mysqli_query($connection, $companyQuery);
                                while ($company = mysqli_fetch_assoc($companyResult)) {
                                    echo '<option value="' . $company['id'] . '">' . htmlspecialchars($company['company_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <h6 class="mb-0 text-uppercase">Posts List</h6>
                        <hr/>
                        <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4" id="posts-container">
                            <!-- Posts will be loaded here -->
                            <?php
                            // Initial load without filter
                            $query = "SELECT `id`, `title`, `body`, `image`, `link`, `date`, `company`, `status` FROM posts";
                            $result = mysqli_query($connection, $query);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<div class="col">';
                                    echo '    <div class="card radius-15">';
                                    echo '        <div class="card-body text-center">';
                                    echo '            <div class="p-4 border radius-15">';
                                    echo '                <img src="' . htmlspecialchars($row['image']) . '" width="110" height="110" class="rounded-circle shadow" alt="Image">';
                                    echo '                <h5 class="mb-0 mt-5">' . htmlspecialchars(substr($row['title'], 0, 20)) . '...</h5>';
                                 //   echo '                <p class="mb-3">' . htmlspecialchars($row['company']) . '</p>';

                                    echo '  <div class="d-flex justify-content-center align-items-center text-danger">
                                    <i class="bx bx-radio-circle-marked bx-burst bx-rotate-90 align-middle font-18 me-1"></i>
                                    <span>' . htmlspecialchars($row['status']) . '</span>
                                </div>';
                        
                        

                                    echo '                <p class="mb-3">' . htmlspecialchars(substr($row['body'], 0, 30)) . '...</p>';
                                    echo '                <div class="list-inline contacts-social mt-3 mb-3">';
                                    echo '                    <a href="' . htmlspecialchars($row['link']) . '"  target="_blank" class="list-inline-item bg-facebook text-white border-0"><i class="bx bxl-wordpress"></i></a>';
                            //        echo '                    <a href="javascript:;" class="list-inline-item bg-twitter text-white border-0"><i class="bx bxl-twitter"></i></a>';
                            //        echo '                    <a href="javascript:;" class="list-inline-item bg-google text-white border-0"><i class="bx bxl-google"></i></a>';
                                    echo '                    <a href="javascript:;" class="list-inline-item bg-linkedin text-white border-0"><i class="bx bxl-linkedin"></i></a>';
                                    echo '                </div>';
                                    echo '                <div class="d-grid">';
                               //     echo '                    <a href="' . htmlspecialchars($row['link']) . '" target="_blank" class="btn btn-outline-primary radius-15">View Profile</a>';
                                    echo '                    <a href="edit_post.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm mt-2">Edit</a>';
                                    echo '                    <a href="delete_post.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm mt-2">Delete</a>';
                                    echo '                </div>';
                                    echo '            </div>';
                                    echo '        </div>';
                                    echo '    </div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="col">No posts found for this company.</div>';
                            }
                            ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        
        <footer class="page-footer">
            <p class="mb-0">Copyright Â© 2024. All right reserved.</p>
        </footer>
    </div>

	<!--end wrapper-->

	<!-- search modal -->



	<!--start switcher-->

	<!--end switcher-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
    <script>
    $(document).ready(function() {
        // Listen for changes in the company filter dropdown
        $('#company-filter').change(function() {
            const companyId = $(this).val();
            $.ajax({
                url: 'posts.php', // Same page
                type: 'POST',
                data: { company_id: companyId, ajax: 'true' },
                success: function(response) {
                    $('#posts-container').html(response); // Update posts area with filtered content
                },
                error: function(jqXHR, textStatus, errorThrown) {
    console.log("AJAX error: ", textStatus, errorThrown);
    alert('Error fetching posts. Please try again.');
}
            });
        });
    });
    </script>

</body>

</html>