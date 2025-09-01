<?php
session_start();
include "includes/head.php";
include 'includes/db.php';
include "includes/functions.php"; 

global $connection;

// Debug: Check database connection
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Retrieve session values
$user = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$companyId = getCompanyIdByUserId($user);

// Determine query based on the role
if ($role === 1) {
    $query = "SELECT cp.`id`, cp.`company_id`, cs.`company_name`, cp.`name`, cp.`description`, cp.`tone`, cp.`example`, cp.`created_at`, cp.`updated_at` 
              FROM `companies_personalities` cp
              JOIN `content_setup` cs ON cp.`company_id` = cs.`id`";
} else {
    $query = "SELECT cp.`id`, cp.`company_id`, cs.`company_name`, cp.`name`, cp.`description`, cp.`tone`, cp.`example`, cp.`created_at`, cp.`updated_at` 
              FROM `companies_personalities` cp
              JOIN `content_setup` cs ON cp.`company_id` = cs.`id`
              WHERE cp.`company_id` = $companyId";
}


// Execute query
$result = mysqli_query($connection, $query);

// Check query result
if (!$result) {
    die("Query Failed: " . mysqli_error($connection));
}

// Fetch all rows in an array
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en" class="color-sidebar sidebarcolor2">
<?php include "includes/head.php"; ?>

<body>
    <!-- Wrapper -->
    <div class="wrapper">
        <?php include "includes/side_menu.php"; ?>
        <?php include "includes/header.php"; ?>
        
        <div class="page-wrapper">
            <div class="page-content">
                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">Editable Personalities</div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-4 text-uppercase">Personalities List</h6>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Company ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Tone</th>
                                    <th>Example</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr id="row-<?php echo $row['id']; ?>">
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['company_name']; ?></td>
                                        <td><input type="text" id="name-<?php echo $row['id']; ?>" class="form-control editable" value="<?php echo htmlspecialchars($row['name']); ?>" disabled></td>
                                        <td><input type="text" id="description-<?php echo $row['id']; ?>" class="form-control editable" value="<?php echo htmlspecialchars($row['description']); ?>" disabled></td>
                                        <td><input type="text" id="tone-<?php echo $row['id']; ?>" class="form-control editable" value="<?php echo htmlspecialchars($row['tone']); ?>" disabled></td>
                                        <td><input type="text" id="example-<?php echo $row['id']; ?>" class="form-control editable" value="<?php echo htmlspecialchars($row['example']); ?>" disabled></td>
                                        <td><?php echo $row['created_at']; ?></td>
                                        <td><?php echo $row['updated_at']; ?></td>
                                        <td>
                                            <button id="edit-btn-<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" onclick="editRow(<?php echo $row['id']; ?>)">Edit</button>
                                            <button id="save-btn-<?php echo $row['id']; ?>" class="btn btn-success btn-sm" style="display:none;" onclick="saveRow(<?php echo $row['id']; ?>)">Save</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRow(id) {
            $("#row-" + id + " .editable").prop("disabled", false);
            $("#edit-btn-" + id).hide();
            $("#save-btn-" + id).show();
        }

        function saveRow(id) {
            const data = {
                id: id,
                name: $("#name-" + id).val(),
                description: $("#description-" + id).val(),
                tone: $("#tone-" + id).val(),
                example: $("#example-" + id).val()
            };

            $.post("save_personality.php", data, function(response) {
                alert(response.message);
                if (response.success) {
                    $("#row-" + id + " .editable").prop("disabled", true);
                    $("#save-btn-" + id).hide();
                    $("#edit-btn-" + id).show();
                }
            }, "json");
        }
    </script>
</body>
</html>
