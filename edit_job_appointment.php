<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");

//handle form submission
$appointment_id = (isset($_GET["appointment_id"])) ? mysqli_real_escape_string($connect, $_GET["appointment_id"]) : false;

//submitted form
$work_week = (isset($_POST["work_week"])) ? mysqli_real_escape_string($connect, $_POST["work_week"]) : false;
$work_day = (isset($_POST["work_day"])) ? mysqli_real_escape_string($connect, $_POST["work_day"]) : false;
$work_job = (isset($_POST["work_job"])) ? mysqli_real_escape_string($connect, $_POST["work_job"]) : false;
$work_staff = (isset($_POST["work_staff"])) ? mysqli_real_escape_string($connect, $_POST["work_staff"]) : false;
$edit_appointment_successful = false;

if($work_week && $work_day && $work_job && $work_staff){
    
    // ========================== Part 11
    //
    // You need to create an UPDATE query here to update this job
    // To acquire the location_id you will need to create a nested SELECT query for one of the values
    // 
    // Make sure you use the $appointment_id from line 8 in your WHERE clause.
    //
    $add_job_sql = "";
    // ========================== /Part 11    

    if(runAndCheckSQL($connect, $add_job_sql)){
        $edit_appointment_successful = true;
    }
}
    
?>
<h2 class="mx-md mt-5">Edit Job Appointment</h2>
<h5>Change details of an appointed job</h5>

<?php
$appointed_result = selectASingleWorkSchedule($connect, $appointment_id);
$appointed_row = mysqli_fetch_assoc($appointed_result);
$appointed_week = $appointed_row['week_id'];
$appointed_day = $appointed_row['day_id'];
$appointed_job = $appointed_row['job_id'];
$appointed_staff = $appointed_row['staff_id'];


// STAFF selector
$staff_run = selectAllStaff($connect);
$staff_options = "";
while($staff_row=mysqli_fetch_assoc($staff_run)){
    $staff_id = $staff_row['id'];
    $staff_name = $staff_row['first_name'].' '.$staff_row['last_name'];
    $level = $staff_row['clearance_level'];
    $staff_selected = ($appointed_staff == $staff_id) ? 'selected' : '';
    $staff_options .= "<option value='$staff_id' $staff_selected>🔒: $level - $staff_name</option>";
}
$staff_option = '<select class="form-control" id="staff" name="work_staff">'.$staff_options.'</select>';

// JOB selector
$job_run = selectAllJobsAndLocations($connect);
$job_options = "";
while($job_row=mysqli_fetch_assoc($job_run)){
    $job_id = $job_row['job_id'];
    $job_name = $job_row['job_name'];
    $radiation = $job_row['radiation_exposure'];
    $location = $job_row['location'];
    $req_level = $job_row['required_clearance_level'];
    $job_selected = ($appointed_job == $job_id) ? 'selected' : '';
    $job_options .= "<option value='$job_id' $job_selected>☢️: $radiation 🔒: $req_level - $job_name ( $location )</option>";
}
$job_option = '<select class="form-control" id="job" name="work_job">'.$job_options.'</select>';


// WEEK selector
$week_run = selectAllWeeks($connect);
$week_options = "";
while($week_row=mysqli_fetch_assoc($week_run)){
    $week_id = $week_row['id'];
    $week_starts = $week_row['week_starts'];
    $week_ends = $week_row['week_ends'];
    $week_selected = ($week_id == $appointed_week) ? 'selected' : '';
    $week_options .= "<option value='$week_id' $week_selected>$week_id: $week_starts - $week_ends</option>";
}
$week_option = '<select class="form-control" id="week" name="work_week">'.$week_options.'</select>';

// DAY selector
$day_run = selectAllDays($connect);
$day_options = "";
while($day_row=mysqli_fetch_assoc($day_run)){
    $day_id = $day_row['id'];
    $day = $day_row['day'];
    $day_selected = ($day_id == $appointed_day) ? 'selected' : '';
    $day_options .= "<option value='$day_id' $day_selected>$day</option>";
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
    <button type="submit" class="btn btn-primary" onclick="$('.alert-success').hide();">Submit</button>
</form>

<?php
if($edit_appointment_successful){
?>
<div class="alert alert-success mt-5" role="alert">
    Job appointment was a successfully editted!
</div>
<?php
}
include_once("includes/footer.php"); 
?>
