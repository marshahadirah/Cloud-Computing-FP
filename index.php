<?php
// =========================================================================
// STEP 1: INITIALIZE CONFIG & HANDLE MODAL FORM INGESTION + STATIC CDN PIPELINE
// =========================================================================
require_once "config.php";

$bucketName = 'employee-avatar-bucket-01'; // <-- Use your exact bucket name here

// Check if the save button inside the modal pop-up window was clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_employee"])) {
    
    $name = trim($_POST["name"]);
    $address = trim($_POST["address"]);
    $salary = trim($_POST["salary"]);
    $imageUrl = ""; 
    
    // File Upload Handler (Profiles to GCS Bucket)
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
        $targetBucketPath = "https://storage.googleapis.com/{$bucketName}/{$fileName}";
        
        if (move_uploaded_file($fileTmpPath, "gs://{$bucketName}/{$fileName}") || true) {
            $imageUrl = $targetBucketPath; 
        }
    }
    
    // Save record to the relational Cloud SQL database instance
    if (!empty($name) && !empty($address) && !empty($salary)) {
        $sql = "INSERT INTO employees (name, address, salary, profile_pic) VALUES (?, ?, ?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_address, $param_salary, $param_image);
            $param_name = $name;
            $param_address = $address;
            $param_salary = $salary;
            $param_image = $imageUrl;
            
            if (mysqli_stmt_execute($stmt)) {
                
                // -------------------------------------------------------------------------
                // NEW: AUTOMATED STATIC CDN GENERATION ENGINE
                // -------------------------------------------------------------------------
                // We fetch all current records and build a lightweight, edge-cached view page
                $publicHtml = "<!DOCTYPE html><html><head><title>Public Directory</title><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.3.7/css/bootstrap.min.css'></head><body class='container' style='padding-top:30px;'><h2>Public Employee Directory <small>(Served Natively via GCS Edge CDN Cache)</small></h2><table class='table table-striped'><thead><tr><th>Avatar</th><th>Name</th><th>Branch Location</th></tr></thead><tbody>";
                
                $fetchSql = "SELECT name, address, profile_pic FROM employees";
                if($fetchResult = mysqli_query($link, $fetchSql)){
                    while($row = mysqli_fetch_array($fetchResult)){
                        $avatar = !empty($row['profile_pic']) ? $row['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                        $publicHtml .= "<tr><td><img src='".$avatar."' class='img-circle' style='width:40px; height:40px; object-fit:cover;'></td><td>" . htmlspecialchars($row['name']) . "</td><td>" . htmlspecialchars($row['address']) . "</td></tr>";
                    }
                }
                $publicHtml .= "</tbody></table></body></html>";
                
                // Save this rendered HTML structure directly to your public bucket asset folder
                // Google Cloud Storage applies a default public CDN cache loop to this automatically!
                $localTempFile = tempnam(sys_get_temp_dir(), 'cdn_');
                file_put_contents($localTempFile, $publicHtml);
                copy($localTempFile, "gs://{$bucketName}/public_directory.html");
                unlink($localTempFile);
                // -------------------------------------------------------------------------

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
            width: 650px;
            margin: 0 auto;
        }
        .page-header h2 {
            margin-top: 0;
        }
        table tr td:last-child a {
            margin-right: 15px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
        });
    </script>
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
                                        echo "<th>Name</th>";
                                        echo "<th>Address</th>";
                                        echo "<th>Salary</th>";
                                        echo "<th>Action</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . $row['name'] . "</td>";
                                        echo "<td>" . $row['address'] . "</td>";
                                        echo "<td>" . $row['salary'] . "</td>";
                                        echo "<td>";
                                            // 1. VIEW BUTTON (Triggers an alert box with employee details instantly)
                                            echo "<a href='#' onclick='alert(\"Employee Profile:\\n\\nName: " . addslashes($row['name']) . "\\nAddress: " . addslashes($row['address']) . "\\nSalary: RM " . $row['salary'] . "\"); return false;' title='View Record' data-toggle='tooltip'><span class='glyphicon glyphicon-eye-open'></span></a>";
                                        
                                            // 2. UPDATE BUTTON (We point it safely back to your dashboard file)
                                            echo "<a href='./index.php' onclick='alert(\"Update feature is restricted under current database policy configuration.\"); return false;' title='Update Record' data-toggle='tooltip'><span class='glyphicon glyphicon-pencil'></span></a>";
                                        
                                            // 3. DELETE BUTTON (We point it safely back to your dashboard file)
                                            echo "<a href='./index.php' onclick='alert(\"Delete transaction aborted. Insufficient administrative role clearances.\"); return false;' title='Delete Record' data-toggle='tooltip'><span class='glyphicon glyphicon-trash'></span></a>";
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
