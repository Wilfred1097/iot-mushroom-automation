<?php
// Define the command to run the Python script
$command = escapeshellcmd('python try.py');

// Run the command and capture the output
$output = shell_exec($command);

// Output the result
echo "<pre>$output</pre>";
?>
