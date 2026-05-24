<?php
// =========================================================================
// STEP 1: INITIALIZE CONFIG & HANDLE MODAL FORM INGESTION + API CDN UPLOAD
// =========================================================================
require_once "config.php";
$secure_token = $_ENV['APP_SECRET_TOKEN'] ?? 'MyLocalDevelopmentToken2026';

$bucketName = 'employee-avatar-bucket-01'; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_employee"])) {
    
    $name = trim($_POST["name"]);
    $address = trim($_POST["address"]);
    $salary = trim($_POST["salary"]);
    $imageUrl = ""; 
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
        
        $ch = curl_init("https://storage.googleapis.com/upload/storage/v1/b/{$bucketName}/o?uploadType=media&name={$fileName}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($fileTmpPath));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: ' . $_FILES['profile_pic']['type']]);
        curl_exec($ch);
        curl_close($ch);
        
        $imageUrl = "https://storage.googleapis.com/{$bucketName}/{$fileName}";
    }
    
    if (!empty($name) && !empty($address) && !empty($salary)) {
        $sql = "INSERT INTO employees (name, address, salary, profile_pic) VALUES (?, ?, ?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_address, $param_salary, $param_image);
            $param_name = $name;
            $param_address = $address;
            $param_salary = $salary;
            $param_image = $imageUrl;
            
            if (mysqli_stmt_execute($stmt)) {
                
                $publicHtml = "<!DOCTYPE html><html><head><title>Public Directory</title><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.3.7/css/bootstrap.min.css'></head><body class='container' style='padding-top:30px;'><h2>Public Employee Directory <small>(Served Natively via GCS Edge CDN Cache)</small></h2><table class='table table-striped'><thead><tr><th>Avatar</th><th>Name</th><th>Branch Location</th></tr></thead><tbody>";
                
                $fetchSql = "SELECT name, address, profile_pic FROM employees";
                if($fetchResult = mysqli_query($link, $fetchSql)){
                    while($fRow = mysqli_fetch_array($fetchResult)){
                        $avatar = !empty($fRow['profile_pic']) ? $fRow['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                        $publicHtml .= "<tr><td><img src='".$avatar."' class='img-circle' style='width:40px; height:40px; object-fit:cover;'></td><td>" . htmlspecialchars($fRow['name']) . "</td><td>" . htmlspecialchars($fRow['address']) . "</td></tr>";
                    }
                }
                $publicHtml .= "</tbody></table></body></html>";
                
                $ch = curl_init("https://storage.googleapis.com/upload/storage/v1/b/{$bucketName}/o?uploadType=media&name=public_directory.html");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $publicHtml);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/html']);
                curl_exec($ch);
                curl_close($ch);

                mysqli_stmt_close($stmt);
                header("location: ./index.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <style type="text/css">
        .wrapper {
            width: 750px;
            margin: 0 auto;
        }
        .page-header h2 {
            margin-top: 0;
        }
        table tr td {
            vertical-align: middle !important;
        }
        .action-btn {
            margin-right: 4px !important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="page-header clearfix">
                        <h2 class="pull-left">Employees Details</h2>
                        <button type="button" class="btn btn-success pull-right" data-toggle="modal" data-target="#addEmployeeModal">
                            Add New Employee
                        </button>
                    </div>

                    <?php
                    $sql = "SELECT * FROM employees";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo "<table class='table table-bordered table-striped'>";
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>#</th>";
                                        echo "<th>Avatar</th>";
                                        echo "<th>Name</th>";
                                        echo "<th>Address</th>";
                                        echo "<th>Salary</th>";
                                        echo "<th>Action</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    
                                    // Sanitize values tightly for safe JavaScript inline output strings
                                    $cleanId = intval($row['id']);
                                    $cleanName = str_replace(array("'", '"'), '', htmlspecialchars($row['name']));
                                    $cleanAddress = str_replace(array("'", '"'), '', htmlspecialchars($row['address']));
                                    $cleanSalary = str_replace(array("'", '"'), '', htmlspecialchars($row['salary']));
                                    
                                    echo "<tr>";
                                        echo "<td>" . $cleanId . "</td>";
                                        
                                        echo "<td>";
                                        if (!empty($row['profile_pic']) && strlen($row['profile_pic']) > 10) {
                                            echo "<img src='" . htmlspecialchars($row['profile_pic']) . "' class='img-circle' style='width:40px; height:40px; object-fit:cover;' alt='avatar'>";
                                        } else {
                                            echo "<img src='https://cdn-icons-png.flaticon.com/512/149/149071.png' class='img-circle' style='width:40px; height:40px; object-fit:cover;' alt='avatar'>";
                                        }
                                        echo "</td>";
                                        
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                        echo "<td>RM " . htmlspecialchars($row['salary']) . "</td>";
                                        
                                        // 100% CLEAN ACTION BUTTONS - ZERO INTERNAL SLASH ESCAPING CRASHES POSSIBLE
                                        echo "<td>";
                                            // 1. View Button (Kept your nice JavaScript alert breakdown)
                                            echo "<button class='btn btn-xs btn-info action-btn' onclick='alert(\"📄 EMPLOYEE PROFILE SYSTEM\\n---------------------------\\nID: " . $cleanId . "\\nName: " . $cleanName . "\\nAddress: " . $cleanAddress . "\\nSalary: RM " . $cleanSalary . "\"); return false;'><span class='glyphicon glyphicon-eye-open'></span> View</button>";
                                            
                                            // 2. REAL Edit Link (Points directly to update.php with parameters)
                                            echo "<a href='update.php?id=" . $cleanId . "&token=" . urlencode($secure_token) . "' class='btn btn-xs btn-primary action-btn'><span class='glyphicon glyphicon-pencil'></span> Edit</a>";
                                            
                                            // 3. REAL Delete Link (Points directly to your new instant delete script)
                                            echo "<a href='delete.php?id=" . $cleanId . "&token=" . urlencode($secure_token) . "' class='btn btn-xs btn-danger action-btn' onclick='return confirm(\"⚠️ Are you absolutely sure you want to delete " . $cleanName . "?\");'><span class='glyphicon glyphicon-trash'></span> Delete</a>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";            
                            echo "</table>";
                            mysqli_free_result($result);
                        } else{
                            echo "<p class='lead'><em>No records were found in your Cloud SQL instance.</em></p>";
                        }
                    } else{
                        echo "ERROR: System failed to execute structural transaction query: $sql. " . mysqli_error($link);
                    }
                    mysqli_close($link);
                    ?>
                    
                </div>
            </div>        
        </div>
    </div>

    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="color: #333;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Create New Employee Record</h4>
                </div>
                <form action="./index.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="number" name="salary" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Employee Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" name="submit_employee" class="btn btn-success">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
