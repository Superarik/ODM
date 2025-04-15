<?php
$server ="plesk.remote.ac";
$username = "ws373176.remote.ac_vfzhg5fyxg";
$password = "Lup^&5euXgD";
// ========================== TASK 1
$database = "WS373176_WADDEV";
// ========================== TASK 1
$connect = mysqli_connect($server,$username,$password,$database);


function runAndCheckSQL($connection, $sql){
    $run = mysqli_query($connection, $sql);
    if ($run) {
        if(is_array($run) || is_object($run)){
            return $run;
        }else{
            return true;
        }
    } else {
        die(showError($sql, $connection));
    }
}

function showError($sql, $connection){
    echo "<div class=\"alert alert-danger\"><strong>ERROR!</strong> : " .  $sql . "<br>" . mysqli_error($connection)."</div>";
}
?>