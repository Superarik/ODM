<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");

//handle form submission
$staff_id = (isset($_GET["id"])) ? mysqli_real_escape_string($connect, $_GET["id"]) : false;



// ========================== YOUR SQL HERE
//
// Required SELECTS
// - staff.first_name
// - staff.last_name
//
// Make sure you use $staff_id variable in your WHERE clause to SELECT staff member based on ID
//
$staff_data_sql = "SELECT
                        first_name,
                        last_name
                    FROM staff
                    WHERE id = '$staff_id'";
// ========================== /YOUR SQL HERE


$result = runAndCheckSQL($connect, $staff_data_sql);
if($row = mysqli_fetch_assoc($result)){
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
?>

<h2 class="mx-md mt-5"><b><?php echo $first_name .' '.$last_name;?></b> - Work Schedule</h2>
<input type="hidden" class="staff_id" value="<?php echo $staff_id;?>">

<?php
}

        // ========================== YOUR SQL HERE
        //
        // Required SELECTS
        // - work_schedule.id - alias - work_schedule_id
        // - staff.first_name
        // - staff.last_name
        // - staff.clearance_level - alias - staff_clearance_level
        // - job.name - alias - job_name
        // - job.radiation_exposure
        // - location.name - alias - location_name
        // - location.required_clearance_level
        // - weeks.id - alias - week_id
        // - weeks.week_starts
        // - weeks.week_ends
        // - days.day
        //
        // Make sure to use $staff_id to SELECT the correct staff member based on ID
        //
        $jobs_sql = "";    
        // ========================== /YOUR SQL HERE
        
        $run = runAndCheckSQL($connect, $jobs_sql);
?>

<script>
$(()=>{
    /* ====================================================== //
    // AJAX CALL TO delete_work_schedule.php -->
    // ====================================================== */
    $('body').on('click', '.fa-trash-alt', function() {
        var id = $(this).data('work_schedule_id');
        var row_class = $(this).data('row_class');
        $.post("delete_work_schedule.php", {id:id}, (data)=>{
            $('.'+row_class).hide();
            checkRadiationExposure();
        });
    });
    
    /* ====================================================== //
    // AJAX CALL TO  staff_radiation_exposure.php -->
    // ====================================================== */
    function checkRadiationExposure(){
        staff_id = $('.staff_id').val();
        $('.staff_radiation_exposure_levels').html('<b>Loading ... </b>');
        setTimeout(()=>{
            $.post("staff_radiation_exposure.php", {staff_id:staff_id}, (data)=>{
                $('.staff_radiation_exposure_levels').html(data);
            });    
        },200);
    }
    
    checkRadiationExposure();
})
</script>

<div class="card card-body">
    <table class="table table-striped table-dark">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Day</th>
                <th scope="col">Job</th>
                <th scope="col">Radiation Exposure</th>
                <th scope="col">Location</th>
                <th scope="col">Clearance</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <?php 
        $x = 1;
        while($row=mysqli_fetch_assoc($run)) { 
            $day = $row["day"];
            $week_id = $row["week_id"]; 
            $job_name = $row['job_name'];
            $radiation_exposure = $row["radiation_exposure"];
            $location_name = $row["location_name"];
            $staff_clearance_level = $row["staff_clearance_level"];
            $required_clearance_level = $row["required_clearance_level"];
            $work_schedule_id = $row['work_schedule_id'];
            $row_class = $week_id.'_'.$day.'_'.$x;
        ?>
            <tr class="<?php echo $row_class;?>">
                <td>
                    <?php echo $x;?>
                </td>
                <td>
                    <?php echo $day;?>
                </td>
                <td>
                    <?php echo $job_name;?>
                </td>
                <td>
                    <?php echo showRadiationBadge($radiation_exposure);?>
                </td>
                <td>
                    <?php echo $location_name;?>
                </td>
                <td>
                    <?php 
                                                    
                    $clear = ($staff_clearance_level >= $required_clearance_level);
                    if($clear){
                        echo '<span class="badge badge-pill badge-success">Accepted</span>';
                    }else{
                        echo '<span class="badge badge-pill badge-danger">Denied</span>';
                    }
                ?>
                </td>
                <td>
                    <a href="edit_job_appointment.php?appointment_id=<?php echo $work_schedule_id;?>"><i class="fas fa-edit btn-fa" data-toggle="tooltip" data-placement="top" title="Edit Job Appointment"></i></a>
                    <i class="fas fa-trash-alt btn-fa" data-toggle="tooltip" data-placement="top" title="Delete Job Appointment: <?php echo $work_schedule_id;?>" data-row_class="<?php echo $row_class;?>" data-work_schedule_id="<?php echo $work_schedule_id;?>"></i>
                </td>
            </tr>

            <?php 
        $x++;
        };  ?>
        </tbody>
    </table>
</div>

<h2 class="mx-md mt-5">☢️ Radiation Exposure Levels</h2>

<div class="card">
    <div class="card-body staff_radiation_exposure_levels"><b>Loading ... </b></div>
</div>

<?php include_once("includes/footer.php"); ?>
