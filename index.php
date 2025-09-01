<?php
include 'includes/db.php';
include 'includes/functions.php';

session_start();

?>

<!doctype html >
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

        <div class="page-wrapper">
			<div class="page-content">
				<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-secondary">Content Published Last Month</p>
										<h4 class="my-1">1</h4>
									</div>
									<div class="widgets-icons bg-light-success text-success ms-auto"><i class='bx bxs-wallet'></i>
									</div>
								</div>
								<div id="chart1"></div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-secondary">Platfroms Connected</p>
										<h4 class="my-1">1</h4>
									</div>
									<div class="widgets-icons bg-light-warning text-warning ms-auto"><i class='bx bxs-group'></i>
									</div>
								</div>
								<div id="chart2"></div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<p class="mb-0 text-secondary">Schdeuled Content</p>
										<h4 class="my-1">0</h4>
									</div>
									<div class="widgets-icons bg-light-danger text-danger ms-auto"><i class='bx bxs-binoculars'></i>
									</div>
								</div>
								<div id="chart3"></div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
				
				<!--end row-->
				<div class="row">
					<div class="col-xl-12 d-flex">
						<div class="card radius-10 w-100">
							<div class="card-body">
								<div class="d-flex align-items-center">
									<div>
										<h5 class="mb-1">Latest Articles</h5>
										<p class="mb-0 font-13 text-secondary"><i class='bx bxs-calendar'></i>in last 30 days</p>
									</div>
									<div class="dropdown ms-auto">
										<a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">	<i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
										</a>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" href="javascript:;">Action</a>
											</li>
											<li><a class="dropdown-item" href="javascript:;">Another action</a>
											</li>
											<li>
												<hr class="dropdown-divider">
											</li>
											<li><a class="dropdown-item" href="javascript:;">Something else here</a>
											</li>
										</ul>
									</div>
								</div>
								<div class="table-responsive mt-4">
									<table class="table align-middle mb-0 table-hover" id="Transaction-History">
										<thead class="table-light">
											<tr>
                                            <th>Title</th>
										<th>Body</th>
										<th>Image</th>
										<th>Status</th>
										<th>Date</th>
										<th>View</th>
											</tr>
										</thead>
                                        <tbody>
								<?php
// Fetch data from the database
global $connection;

// Fetch data from the database
$query = "SELECT `id`, `title`, `body`, `image`, `link`, `date`, `company`, `status` FROM posts LIMIT 10";
$result = mysqli_query($connection, $query); // Assuming $conn is your database connection variable

// Check if the query was successful
if (!$result) {
    // Display the SQL error
    echo "Error executing query: " . mysqli_error($connection);
} else {
    // Check if the query returned any results
    if (mysqli_num_rows($result) > 0) {
        // Loop through each row in the result set
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
			echo "<td><h6 class='mb-0 font-14' style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;'>" . htmlspecialchars($row['title']) . "</h6></td>";
			echo "<td><h6 class='mb-0 font-14' style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;'>" . htmlspecialchars($row['body']) . "</h6></td>";
			
            echo "<td><img src='" . htmlspecialchars($row['image']) . "' alt='Image' style='width:50px;height:50px;'></td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
            echo "<td><a href='" . htmlspecialchars($row['link']) . "' target='_blank'>View</a></td>";
            echo "</tr>";
        }
    } else {
        // Display a message if no results are found
        echo "<tr><td colspan='7'>No articles found.</td></tr>";
    }
}
?>


								</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				
				</div>
				<!--end row-->
		
				<!--end row-->
		
				<!--end row-->
			
			</div>
		</div>

        <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <!--app JS-->
    <script src="assets/js/app.js"></script>
 

    </div>
</body>
</html>
