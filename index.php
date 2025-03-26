<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
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
        $('.staff_radiation_exposure_levels').html('<b>Loading ... </b>');
        setTimeout(()=>{
            $.post("staff_radiation_exposure.php", (data)=>{
                $('.staff_radiation_exposure_levels').html(data);
            });    
        },200);
    }
    
    checkRadiationExposure();
})
</script>

<!-- ====================================================== -->
<!-- PAGE CONTENT STARTS HERE -->
<!-- ====================================================== -->


<h2 class="mx-md mt-5">Week 1 Work Schedule</h2>

<div class="row mt-2">
    <div class="col"><button type="button" class="btn btn-primary btn-block" data-toggle="collapse" data-target="#Mon" aria-expanded="true" aria-controls="Mon" onclick="$(this).toggleClass('btn-outline-primary');$(this).toggleClass('btn-primary');">Mon</button></div>
    <div class="col"><button type="button" class="btn btn-outline-primary btn-block" data-toggle="collapse" data-target="#Tues" aria-expanded="false" aria-controls="Tues" onclick="$(this).toggleClass('btn-outline-primary');$(this).toggleClass('btn-primary');">Tues</button></div> 
    <div class="col"><button type="button" class="btn btn-outline-primary btn-block" data-toggle="collapse" data-target="#Wed" aria-expanded="false" aria-controls="Wed" onclick="$(this).toggleClass('btn-outline-primary');$(this).toggleClass('btn-primary');">Wed</button></div> 
    <div class="col"><button type="button" class="btn btn-outline-primary btn-block" data-toggle="collapse" data-target="#Thurs" aria-expanded="false" aria-controls="Thurs" onclick="$(this).toggleClass('btn-outline-primary');$(this).toggleClass('btn-primary');">Thurs</button></div> 
    <div class="col"><button type="button" class="btn btn-outline-primary btn-block" data-toggle="collapse" data-target="#Fri" aria-expanded="false" aria-controls="Fri" onclick="$(this).toggleClass('btn-outline-primary');$(this).toggleClass('btn-primary');">Fri</button></div> 
</div>

<div class="collapse multi-collapse mt-2 show" id="Mon">
  <div class="card card-body">
      <h5 class="card-title">Monday</h5> 
      <!-- DISPLAY WORK SCHEDULE FOR MONDAY -->
      <?php showWorkTable(1, 1, $connect); ?>
  </div>
</div>
<div class="collapse multi-collapse mt-2" id="Tues">
  <div class="card card-body">
      <h5 class="card-title">Tuesday</h5>
      <!-- DISPLAY WORK SCHEDULE FOR TUESDAY -->
      <?php showWorkTable(1, 2, $connect); ?>
  </div>
</div>
<div class="collapse multi-collapse mt-2" id="Wed">
  <div class="card card-body">
      <h5 class="card-title">Wednesday</h5>
      <!-- DISPLAY WORK SCHEDULE FOR WEDNESDAY -->
      <?php showWorkTable(1, 3, $connect); ?>
  </div>
</div>
<div class="collapse multi-collapse mt-2" id="Thurs">
  <div class="card card-body">
      <h5 class="card-title">Thursday</h5>
      <!-- DISPLAY WORK SCHEDULE FOR THURSDAY -->
      <?php showWorkTable(1, 4, $connect); ?>
  </div>
</div>
<div class="collapse multi-collapse mt-2" id="Fri">
  <div class="card card-body">
      <h5 class="card-title">Friday</h5>
      <!-- DISPLAY WORK SCHEDULE FOR FRIDAY -->
      <?php showWorkTable(1, 5, $connect); ?>
  </div>
</div>

<h2 class="mx-md mt-5">☢️ Staff Radiation Exposure Levels</h2>

<div class="card">
    <div class="card-body staff_radiation_exposure_levels"><b>Loading ... </b></div>
</div>

<!-- ====================================================== -->
<!-- PAGE CONTENT ENDS HERE -->
<!-- ====================================================== -->

<?php include_once("includes/footer.php"); ?>
