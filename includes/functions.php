<?php

include "db.php";

function getCompanyIdByUserId($userId) {

    global $connection;
    // Prepare the SQL query to retrieve the company_id
    $sql = "SELECT id FROM content_setup WHERE user_id = ?";

    try {
        // Prepare the statement
        $stmt = $connection->prepare($sql);

        // Execute the query with the user_id parameter
        $stmt->execute([$userId]);

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the company_id or null if not found
        return $result ? $result['id'] : null;
    } catch (PDOException $e) {
        // Handle any potential exceptions
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}




?>