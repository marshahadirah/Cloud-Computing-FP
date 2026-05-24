<?php
// =========================================================================
// DATABASE CREDENTIAL CONFIGURATION (ENVIRONMENT-AWARE CLOUD BRIDGE)
// =========================================================================

// 1. Check if running inside Google Cloud Run environment
if (isset($_ENV['CLOUD_RUN_SERVICE']) || isset($_SERVER['CLOUD_RUN_SERVICE'])) {
    
    // CLOUD ENVIRONMENT DIRECTORY
    $user = 'root';
    $password = 'AmanMarshaCrash0ut';
    $dbName = 'employee_db';
    $socketDir = '/cloudsql';
    $instanceConnectionName = 'project-09abe099-ed89-4ede-820:us-central1:free-trial-first-project'; 
    $socketPath = "$socketDir/$instanceConnectionName";

    // Establish the connection using the Unix socket parameters
    $link = mysqli_connect(
        null,                  // Host must be null when using a Unix socket
        $user,                 // Username
        $password,             // Password
        $dbName,               // Database Name
        null,                  // Port must be null when using a Unix socket
        $socketPath            // The explicit socket path that handles the cloud handshake
    );

} else {
    
    // LOCAL DEVELOPMENT ENVIRONMENT DIRECTORY (Fallback for local adjustments/testing)
    $host = '127.0.0.1';
    $user = 'root';
    $password = '';            // Default local database configuration password fallback
    $dbName = 'employee_db';

    $link = mysqli_connect($host, $user, $password, $dbName);
}

// 2. Validate the established database connection link status integrity
if($link === false){
    die("ERROR: Could not establish a secure connection link to the database instance. " . mysqli_connect_error());
}
?>
