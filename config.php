<?php
// =========================================================================
// BULLETPROOF PUBLIC CLOUD SQL CONNECTION
// =========================================================================

// Paste your actual Public IP address numbers from your Cloud SQL dashboard here!
$db_host = '34.133.237.4'; 
$user = 'root';
$password = 'AmanMarshaCrash0ut';
$dbName = 'employee_db';

// Establish connection over standard TCP public routing
$link = mysqli_connect($db_host, $user, $password, $dbName);

// Validate database link connection state integrity
if($link === false){
    die("ERROR: Could not establish a secure link connection to the database instance. " . mysqli_connect_error());
}
?>
