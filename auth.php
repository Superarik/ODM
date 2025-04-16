<?php
session_start();
if(!isset($_POST["username"]) OR !isset($_POST["password"]))
{
    header("Location:login.php?e=noinput");
    die("No inputs detected");    
}
include_once("includes/_connect.php");


//clean data
$username = mysqli_real_escape_string($connect,$_POST["username"]);
$password = mysqli_real_escape_string($connect,$_POST["password"]);


// ========================== TASK 2
//
// Required SELECTS
// - staff.*
//
// You need to use $username and $password from above to find a matching staff member
//
$sql = "SELECT * FROM staff WHERE username = :username AND password = :password;";
// ========================== /TASK 2


$result = runAndCheckSQL($connect, $sql);
$count = mysqli_num_rows($result);

if ($count =="1")
{
    $_SESSION["auth"]= "admin";
    header("Location:index.php");
    die("");    
}
else
{
    header("Location:login.php?e=invalid");
    die("Invalid Username/password");
}

?>
