<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");

//handle form submission
$week_param = (isset($_GET["week"])) ? mysqli_real_escape_string($connect, $_GET["week"]) : false;
$day_param = (isset($_GET["day"])) ? mysqli_real_escape_string($connect, $_GET["day"]) : false;

//submitted form
$work_week_id = (isset($_POST["work_week"])) ? mysqli_real_escape_string($connect, $_POST["work_week"]) : false;
$work_day_id = (isset($_POST["work_day"])) ? mysqli_real_escape_string($connect, $_POST["work_day"]) : false;
$work_job_id = (isset($_POST["work_job"])) ? mysqli_real_escape_string($connect, $_POST["work_job"]) : false;
$work_staff_id = (isset($_POST["work_staff"])) ? mysqli_real_escape_string($connect, $_POST["work_staff"]) : false;
$new_appointment_successful = false;

if($work_week_id && $work_day_id && $work_job_id && $work_staff_id){
    
    
    // ========================== Part 10
    //
    // You need to create an INSERT query here to create a new work_schedule
    // To acquire the location_id you will need to create a nested SELECT query for one of the values
    //
    $add_job_sql = "INSERT INTO work_schedule (week_id, day_id, staff_id, job_id, location_id)
                    VALUES (
                        '$work_week_id',
                        '$work_day_id',
                        '$work_staff_id',
                        '$work_job_id',
                        (SELECT location_id FROM job WHERE id = '$work_job_id')";
    // ========================== /Part 10

    if(runAndCheckSQL($connect, $add_job_sql)){
        $new_appointment_successful = true;
    }
}
    
?>
<h2 class="mx-md mt-5">Appoint Job</h2>
<h5>Give a member of staff a job</h5>

<?php


// Staff selector
$staff_run = selectAllStaff($connect);
$staff_options = "";
while($staff_row=mysqli_fetch_assoc($staff_run)){
    $id = $staff_row['id'];
    $staff_name = $staff_row['first_name'].' '.$staff_row['last_name'];
    $level = $staff_row['clearance_level'];
    $staff_options .= "<option value='$id'>🔒: $level - $staff_name</option>";
}
$staff_option = '<select class="form-control" id="staff" name="work_staff">'.$staff_options.'</select>';


// Job selector
$job_run = selectAllJobsAndLocations($connect);
$job_options = "";
while($job_row=mysqli_fetch_assoc($job_run)){
    $id = $job_row['job_id'];
    $job_name = $job_row['job_name'];
    $radiation = $job_row['radiation_exposure'];
    $location = $job_row['location'];
    $req_level = $job_row['required_clearance_level'];
    $job_options .= "<option value='$id'>☢️: $radiation 🔒: $req_level - $job_name ( $location )</option>";
}
$job_option = '<select class="form-control" id="job" name="work_job">'.$job_options.'</select>';


// Week selector
$week_run = selectAllWeeks($connect);
$week_options = "";
$w = 0;
while($week_row=mysqli_fetch_assoc($week_run)){
    $id = $week_row['id'];
    $week_starts = $week_row['week_starts'];
    $week_ends = $week_row['week_ends'];
    $selected = ($id == $week_param) ? 'selected' : '';
    $disabled = ($w > 0) ? 'disabled' : '';
    $week_options .= "<option value='$id' $selected $disabled>$id: $week_starts - $week_ends</option>";
    $w++;
}
$week_option = '<select class="form-control" id="week" name="work_week">'.$week_options.'</select>';

// Day selector
$day_run = selectAllDays($connect);
$day_options = "";
while($day_row=mysqli_fetch_assoc($day_run)){
    $id = $day_row['id'];
    $day = $day_row['day'];
    $selected = ($id == $day_param) ? 'selected' : '';
    $day_options .= "<option value='$id' $selected>$day</option>";
}
$day_option = '<select class="form-control" id="day" name="work_day">'.$day_options.'</select>';

?>

<form class="mt-5" method="post" action="">
    <div class="form-group">
        <label for="exampleInputEmail1">Memeber of Staff</label>
        <?php echo $staff_option;?>
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">Job</label>
        <?php echo $job_option;?>
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">Week</label>
        <?php echo $week_option;?>
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">Day</label>
        <?php echo $day_option;?>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?php
if($new_appointment_successful){
?>
<div class="alert alert-success mt-5" role="alert">
  New job appointment was a success!
</div>
<?php
}
include_once("includes/footer.php"); 
?>   