<?php
// 1. Cloud Architecture Security Check
$secure_token = $_ENV['APP_SECRET_TOKEN'] ?? 'MyLocalDevelopmentToken2026';
$user_token = $_GET['token'] ?? '';

if (empty($user_token) || $user_token !== $secure_token) {
    http_response_code(403); 
    die("Unauthorized access: A valid Cloud API Token is required.");
}

// 2. Include connection
require_once 'config.php';

// 3. Execute Instant Delete via GET
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    
    $sql = "DELETE FROM employees WHERE id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;
        
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($link);
    
    // Redirect instantly back to dashboard
    header("location: index.php?token=" . urlencode($secure_token));
    exit();
} else {
    header("location: index.php?token=" . urlencode($secure_token));
    exit();
}
?>
