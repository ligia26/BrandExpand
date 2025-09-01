<?php
include 'includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $tone = mysqli_real_escape_string($connection, $_POST['tone']);
    $example = mysqli_real_escape_string($connection, $_POST['example']);
    $updated_at = date('Y-m-d H:i:s');

    $query = "UPDATE `companies_personalities` SET 
        `name` = '$name', 
        `description` = '$description', 
        `tone` = '$tone', 
        `example` = '$example', 
        `updated_at` = '$updated_at' 
        WHERE `id` = $id";

    if (mysqli_query($connection, $query)) {
        echo json_encode(['success' => true, 'message' => 'Record updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating record: ' . mysqli_error($connection)]);
    }
}
?>
