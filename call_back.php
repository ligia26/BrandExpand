<?php
// Error reporting (optional)
ini_set('display_errors', 0); // Do not display errors on the webpage
ini_set('log_errors', 1);     // Enable error logging
ini_set('error_log', '/var/www/automation.datainnovation.io/html/hi.log');
error_reporting(E_ALL);

// Include database connection
include 'includes/db.php';

// Start session if needed
session_start();

if (isset($_GET['code'])) {
    $client_id = '78xtj6e2mru8za';
    $client_secret = 'nVOEuWEOtK45BLig';
    $redirect_uri = 'https://automation.datainnovation.io/call_back.php';
    $code = $_GET['code'];

    $token_url = "https://www.linkedin.com/oauth/v2/accessToken";

    $params = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'client_id' => $client_id,
        'client_secret' => $client_secret
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];
        // For now, set company_id to 1
        $company_id = 1;

        // Sanitize input (security)
        $access_token = mysqli_real_escape_string($conn, $access_token);

        // Check if a record exists for company_id = 1
        $query = "SELECT id FROM company_credentials WHERE company_id = $company_id";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log('Database query failed: ' . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            // Record exists, update it
            $query = "UPDATE company_credentials SET linkedin_token = '$access_token' WHERE company_id = $company_id";
        } else {
            // No record exists, insert a new one
            $query = "INSERT INTO company_credentials (company_id, linkedin_token, wp_url, wp_user, wp_pass) VALUES ($company_id, '$access_token', '', '', '')";
        }
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log('Database query failed: ' . mysqli_error($conn));
            echo "<script>alert('An error occurred while saving LinkedIn token.');</script>";
        } else {
            echo "<script>alert('LinkedIn token saved successfully.');</script>";
        }

        // Redirect back to the main page or wherever you want
        echo "<script>alert('LinkedIn token saved successfully.');</script>";

        header('Location: index.php');
        exit();
    } else {
        error_log('Error fetching access token: ' . $response);
        echo "Error fetching access token.";
    }
} else {
    echo "Authorization code not found.";
}
?>
