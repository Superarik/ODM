<?php
session_start();
if( isset($_SESSION["auth"]))
{   
    if($_SESSION["auth"]!="admin" )
    {header("Location:login.php");
    die("You no log in.");
    }    
}
else
{
    header("Location:login.php");
    die("You no log in.");    
}