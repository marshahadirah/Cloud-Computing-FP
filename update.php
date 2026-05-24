<?php
require_once 'config.php';

$name = $address = $salary = "";
$id = "";

// A. HANDLE FORM SUBMISSION EVENT (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && !empty($_POST["id"])) {
    $id = intval($_POST["id"]);
    $name = trim($_POST["name"]);
    $address = trim($_POST["address"]);
    $salary = intval(trim($_POST["salary"]));
    
    $sql = "UPDATE employees SET name=?, address=?, salary=? WHERE id=?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssii", $param_name, $param_address, $param_salary, $param_id);
        
        $param_name = $name;
        $param_address = $address;
        $param_salary = $salary;
        $param_id = $id;
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($link);
            header("location: index.php");
            exit();
        } else {
            echo "SQL UPDATE EXECUTION CRASHED: " . mysqli_error($link);
        }
    }

// B. HANDLE INITIAL PAGE LOAD VIEW STATE (GET)
} else {
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id = intval(trim($_GET["id"]));
        
        $sql = "SELECT * FROM employees WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id;
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    $name = $row["name"];
                    $address = $row["address"];
                    $salary = $row["salary"];
                }
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        header("location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Employee Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>.wrapper{ width: 600px; margin: 0 auto; }</style>
</head>
<body>
    <div class="wrapper">
        <h2 class="mt-5">Update Record</h2>
        <form action="update.php" method="post">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" required><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <div class="form-group">
                <label>Salary</label>
                <input type="number" name="salary" class="form-control" value="<?php echo htmlspecialchars($salary); ?>" required>
            </div>
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
            <input type="submit" class="btn btn-primary" value="Submit Changes">
            <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
        </form>
    </div>
</body>
</html>
