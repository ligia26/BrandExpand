<?php
session_start();

// Clear the login_token cookie
if (isset($_COOKIE['login_token'])) {
    // Clear the token from the database
    $host = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbname = "dashboard";
    
    // Create connection
    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Assuming user_id is stored in session
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET login_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }

    // Clear the cookie
    setcookie('login_token', '', time() - 3600, "/"); // Expire the cookie by setting it to a time in the past
}

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
