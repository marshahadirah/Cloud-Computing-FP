<?php
// 1. Inject HTTP Cache Control Headers to instruct Google CDN networks to cache this query result asset
header("Cache-Control: public, max-age=300, s-maxage=300"); // Caches table data globally at edge nodes for 5 minutes
header("X-Cache-Service: Cloud-Run-Edge-CDN-Gateway");

require_once "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Public Employee Directory (Cached Endpoint)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body style="background-color: #f8f9fa; padding: 40px 0;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h2>Public Corporate Directory <small>(Served via Global Cloud CDN Cache)</small></h2>
                </div>
                
                <?php
                $sql = "SELECT name, address, salary, profile_pic FROM employees";
                if($result = mysqli_query($link, $sql)){
                    if(mysqli_num_rows($result) > 0){
                        echo "<table class='table table-striped table-hover'>";
                            echo "<thead><tr><th>Avatar</th><th>Name</th><th>Office Branch Location</th></tr></thead>";
                            echo "<tbody>";
                            while($row = mysqli_fetch_array($result)){
                                echo "<tr>";
                                    // Render image if present, else show a placeholder avatar
                                    $avatar = !empty($row['profile_pic']) ? $row['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                                    echo "<td><img src='".$avatar."' class='img-circle' style='width:40px; height:40px; object-fit:cover;'></td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                        echo "</table>";
                        mysqli_free_result($result);
                    } else {
                        echo "<p class='lead'>No public directories listed currently.</p>";
                    }
                }
                mysqli_close($link);
                ?>
            </div>
        </div>
    </div>
</body>
</html>
