<?php
// =========================================================================
// DATABASE CREDENTIAL CONFIGURATION (HARDCODED FOR CLOUD RUN TO CLOUD SQL BRIDGE)
// =========================================================================

$user = 'root'; // Keep as root unless you created a custom DB user

$password = 'amanmarshaCL0UD'; // <-- Change this to your real database master password!

$dbName = 'employee_db'; // <-- Double check if your database name is 'employee_db' or 'demo' in your SQL console

$socketDir = '/cloudsql';

$instanceConnectionName = 'project-09abe099-ed89-4ede-820:us-central1:free-trial-first-project'; 

$socketPath = "$socketDir/$instanceConnectionName";
// 3. Establish the connection using the socket parameter (the 6th slot)
$link = mysqli_connect(
    null,                  // Host must be null when using a Unix socket
    $user,                 // Username
    $password,             // Password
    $dbName,               // Database Name
    null,                  // Port must be null when using a Unix socket
    $socketPath            // The explicit socket path that handles the cloud handshake
);

// Check the connection link status
if($link === false){
    die("ERROR: Could not connect to the cloud database. " . mysqli_connect_error());
}
?>
