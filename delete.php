<?php
// 1. Cloud Architecture Security Check
$secure_token = $_ENV['APP_SECRET_TOKEN'] ?? 'MyLocalDevelopmentToken2026';
$user_token = $_GET['token'] ?? ($_POST['token'] ?? '');

if (empty($user_token) || $user_token !== $secure_token) {
    http_response_code(403); 
    die("Unauthorized access: A valid Cloud API Token is required to delete resources.");
}

// Include database configuration once right at the top
require_once 'config.php';

// 2. Process Delete Action on POST Confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && !empty($_POST["id"])) {
    
    $sql = "DELETE FROM employees WHERE id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = trim($_POST["id"]);
        
        if (mysqli_stmt_execute($stmt)) {
            // Close statement and connection before redirecting
            mysqli_stmt_close($stmt);
            mysqli_close($link);
            
            // Records deleted successfully. Redirect back to landing page
            header("location: index.php?token=" . urlencode($secure_token));
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
    
} else {
    // 3. Check existence of ID parameter on initial GET load
    if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
        mysqli_close($link);
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Employee Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>.wrapper{ width: 600px; margin: 0 auto; }</style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5 mb-3">Delete Record</h2>
                    <form action="delete.php?token=<?php echo urlencode($secure_token); ?>" method="post">
                        <div class="alert alert-danger">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars(trim($_GET["id"])); ?>"/>
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($secure_token); ?>"/>
                            <p>Are you sure you want to delete this employee record?</p>
                            <p>
                                <input type="submit" value="Yes" class="btn btn-danger">
                                <a href="index.php?token=<?php echo urlencode($secure_token); ?>" class="btn btn-secondary">No</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
