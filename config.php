<?php
// 1. Pull the variables securely from Cloud Run's Environment Settings
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS'); // Your master password
$dbName = getenv('DB_NAME') ?: 'employee_db';

// 2. THE CRITICAL CLOUD SOCKET PATH FIX
// This tells PHP exactly where Cloud Run mounts your database link inside the serverless container!
$socketDir = getenv('DB_SOCKET_DIR') ?: '/cloudsql';
$instanceConnectionName = 'project-09abe099-ed89-4ede-820:us-central1:free-trial-first-project'; // Double check if yours has numbers on the SQL dashboard

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
