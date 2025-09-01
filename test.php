<?php
// Start the Python script in the background
exec('nohup python3 /var/www/automation.datainnovation.io/html/includes/main.py > /dev/null 2>&1 &');

// Display an alert and redirect using JavaScript
echo '<script type="text/javascript">
    alert("Your request is under processing.");
    window.location.href = "index.php";
</script>';
?>
