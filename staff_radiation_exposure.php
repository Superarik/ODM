<?php
include_once("includes/_connect.php");
$staff_id = (isset($_POST["staff_id"])) ? mysqli_real_escape_string($connect, $_POST["staff_id"]) : false;
?>

<table class="table table-striped table-dark">
    <thead class="thead-dark">
        <tr>
            <th scope="col"><i class="fas fa-calendar-alt" data-toggle="tooltip" data-placement="top" title="Staff Schedule"></i></th>
            <th scope="col">Firstname</th>
            <th scope="col">Lastname</th>
            <th scope="col">Radiation Exposure</th>
            <th scope="col"></th>
            <th scope="col">Health</th>
        </tr>
    </thead>
    <tbody>

        <?php
        // ========================== Task 4
        //
        // Required SELECTS
        // - staff.id
        // - staff.first_name
        // - staff.last_name
        // - SUM(job.radiation_exposure) - alias - exposure
        //
        // This query will require you to use GROUP BY staff.id
        //
        
        // $staff_check is a WHERE clause that only gets used if the php variable $staff_id has value
        // $staff_id only has a value when this file is called from staff_schedule.php
        // YOU WILL NEED TO USE $staff_check in your SQL query
        $staff_check = '';
        if($staff_id)$staff_check = "WHERE s.id = '$staff_id'";
        
        $sql = "SELECT
                    staff.id,
                    staff.first_name,
                    staff.last_name,
                    SUM(job.radiation_exposure) AS exposure
                FROM work_schedule
                JOIN staff ON work_schedule.staff_id = staff.id
                JOIN job ON work_schedule.job_id = job.id
                $staff_check
                GROUP BY staff.id
                ORDER BY exposure DESC";    
        // ========================== /Task 4


        $run = runAndCheckSQL($connect,$sql);
        while($row=mysqli_fetch_assoc($run)) { 
        
            $class = "bg-success";
            $level = $row["exposure"];
            $health = "./images/healthy.svg";
            
            $perc = round(($level/350) * 100,2);

            if($perc < 25){
                $class = "bg-info";
                $health = "./images/sick_1.svg";
            }else if($perc < 50){
                $class = "bg-warning";
                $health = "./images/sick_2.svg";
            }else if($perc < 100){
                $class = "bg-danger";
                $health = "./images/vomit.svg";
            }else{
                $class = "bg-danger";
                $health = "./images/skull.svg";   
            }
        
        ?>
        <tr>
            <td>
                <a href="staff_schedule.php?id=<?php echo $row['id'];?>"><i class="fas fa-search btn-fa" data-toggle="tooltip" data-placement="top" title="View Staff Schedule"></i></a>
            </td>
            <td>
                <?php echo $row["first_name"];?>
            </td>
            <td>
                <?php echo $row["last_name"];?>
            </td>
            <td>
                <div class="progress">
                    <div class="progress-bar <?php echo $class; ?>" role="progressbar" style="width: <?php echo $perc;?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $perc;?>%</div>
                </div>
            </td>
            <td>
                <i class="fas fa-radiation text-warning"></i>
                <?php 
                    $level = $row["exposure"];
                    echo $level;
                ?>
            </td>
            <td>
                <img src="<?php echo $health;?>" width="24"/>
            </td>
        </tr>

        <?php };  ?>
    </tbody>
</table>
