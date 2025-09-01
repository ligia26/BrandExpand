<?php
// Define the command to run the Python script in the background
$command = escapeshellcmd('python3 main1.py > /dev/null 2>&1 &'); // Redirect output and run in background

// Execute the command
shell_exec($command);

// You can optionally display a message
echo "Python script is running in the background.";
?>
