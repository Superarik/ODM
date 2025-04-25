<?php
include_once("includes/_connect.php");

$id = $_POST['id'];


// ========================== Part 5
//
// DELETE a work_schedule entry based on $id from above
//
$sql = "DELETE FROM work_schedule WHERE work_schedule.id = '$id' LIMIT 1;";
// ========================== /Part 5


$form_data = array();
if(runAndCheckSQL($connect, $sql)){
    http_response_code(200);
}else{
    http_response_code(500);
}