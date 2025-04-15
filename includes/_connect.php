<?php
$server ="WS373176-odm.remote.ac";
$username = "WS373176_admin";
$password = "%txWH9!dLpw";
// ========================== TASK 1
$database = "WS373176_SAD";
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