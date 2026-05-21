<?php
// =========================================================================
// STEP 1: INITIALIZE CONFIG & HANDLE MODAL FORM INGESTION (INSERT)
// =========================================================================
require_once "config.php";

// Check if the save button inside the modal pop-up window was clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_employee"])) {
    
    // Ingest and trim form values
    $name = trim($_POST["name"]);
    $address = trim($_POST["address"]);
    $salary = trim($_POST["salary"]);
    
    // Check that fields aren't empty before pushing to database
    if (!empty($name) && !empty($address) && !empty($salary)) {
        
        $sql = "INSERT INTO employees (name, address, salary) VALUES (?, ?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind input values into the statement
            mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_address, $param_salary);
            
            $param_name = $name;
            $param_address = $address;
            $param_salary = $salary;
            
            // Execute transaction against your Cloud SQL instance
            if (mysqli_stmt_execute($stmt)) {
                // Refresh the index page smoothly to show the brand new row
                header("location: ./index.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
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
                <form action="./index.php" method="POST">
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
