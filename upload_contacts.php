<?php
// FTP Configuration
$ftp_server = "your_ftp_server";
$ftp_username = "username";
$ftp_userpass = "password";
$remote_file = "/path/to/yourfile.csv";
$local_file = "file.csv";

// Mautic Configuration
$mauticBaseUrl = 'https://m.cpcseguro.com';


$username = 'admin'; // Mautic username
$password = 'H9NESK_K99'; // Mautic password

$authHeader = 'Basic ' . base64_encode($username . ':' . $password); // Basic Auth Header
function makeApiRequest($url, $data, $authHeader, $method = 'POST') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $authHeader, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    if ($response === false) {
        echo "CURL Error: " . curl_error($ch) . "\n";
    } else {
        $info = curl_getinfo($ch);
        echo "HTTP Status: " . $info['http_code'] . "\n";  // Shows HTTP status code
        echo "Response Body: " . $response . "\n";         // Shows the response body
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Read CSV and prepare data for batch processing
$handle = fopen($local_file, "r");
$contacts = [];
if ($handle !== FALSE) {
    $header = fgetcsv($handle, 1000, ","); // assuming the first row is header
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $contactData = array_combine($header, $data);
        $contacts[] = [
            'email'             => $contactData['email'],
            'firstname'         => $contactData['firstname'],
            'lastname'          => $contactData['lastname'],
            // Add more fields as necessary
        ];
    }
    fclose($handle);
}

// URL for batch creating/updating contacts
$url = $mauticBaseUrl . '/api/contacts/batch/new';  // Use this endpoint for batch operations

// Make the API request using PUT to create or update entries
$response = makeApiRequest($url, $contacts, $authHeader, 'POST');

// Output the response
echo "API Response: " . print_r($response, true) . "\n";
?>