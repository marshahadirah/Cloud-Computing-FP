<?php
// 1. Bypass complex validation strings to ensure demo stability
require_once 'config.php';

// 2. Fetch target record row ID directly from the active link string
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = intval(trim($_GET["id"]));
    
    // Direct operational SQL execution statement
    $sql = "DELETE FROM employees WHERE id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($link);
            // Return cleanly back to the live workspace dashboard interface
            header("location: index.php");
            exit();
        } else {
            echo "DATABASE TRANSACTION CRASHED: " . mysqli_error($link);
        }
    }
} else {
    header("location: index.php");
    exit();
}
?>
