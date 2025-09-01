<?php
$client_id = '78xtj6e2mru8za';
$redirect_uri = urlencode('https://automation.datainnovation.io/call_back.php');
$state = 'RANDOM_STRING'; // Prevent CSRF attacks
$scope = urlencode('r_basicprofile w_member_social');

$auth_url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}";

?>
