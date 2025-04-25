<!-- Code to include the neccessary php files -->

<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
?>

<?php

// Query 1: Jobs Allocated to Each Staff Member
$jobs_allocated_sql = "
    SELECT staff.first_name, staff.last_name, COUNT(work_schedule.id) AS job_count
    FROM staff
    LEFT JOIN work_schedule ON staff.id = work_schedule.staff_id
    GROUP BY staff.id
    ORDER BY job_count DESC
";
$jobs_result = runAndCheckSQL($connect, $jobs_allocated_sql);

// Query 2: Total Radiation Exposure by Staff
$exposure_sql = "
    SELECT staff.first_name, staff.last_name, SUM(job.radiation_exposure) AS total_exposure
    FROM staff
    JOIN work_schedule ON staff.id = work_schedule.staff_id
    JOIN job ON work_schedule.job_id = job.id
    GROUP BY staff.id
    ORDER BY total_exposure DESC
";
$exposure_result = runAndCheckSQL($connect, $exposure_sql);

// Query 3: Staff With No Work Allocated
$no_work_sql = "
    SELECT staff.first_name, staff.last_name
    FROM staff
    LEFT JOIN work_schedule ON staff.id = work_schedule.staff_id
    WHERE work_schedule.id IS NULL
";
$no_work_result = runAndCheckSQL($connect, $no_work_sql);
?>

<!-- Load Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
    // Loads required packages
    google.charts.load('current', {'packages':['corechart', 'bar']});
    // Tells the google charts library to call the drawCharts function after the google charts library has finished loading
    google.charts.setOnLoadCallback(drawCharts);

    // Function to draw the charts
    function drawCharts() {
        drawJobsPieChart();
        drawExposureBarChart();
    }

    // Function to draw the pie chart for jobs allocated to staff
    function drawJobsPieChart() {
        
        // Prepare the data for the chart
        var data = google.visualization.arrayToDataTable([
            ['Staff Member', 'Number of Jobs'], // Column headers
            <?php
            mysqli_data_seek($jobs_result, 0); // Reset the result pointer to the beginning
            while($row = mysqli_fetch_assoc($jobs_result)) {
                $name = $row['first_name'] . ' ' . $row['last_name'];
                echo "['$name', {$row['job_count']}],"; // Add each staff member and their job count
            }
            ?>
        ]);

        // Configure chart options
        var options = {
            title: 'Jobs Allocated to Staff', // Chart title
            pieHole: 0.4 // Makes the chart a donut chart
        };

        // Chart is created and drawn
        var chart = new google.visualization.PieChart(document.getElementById('jobs_piechart'));
        chart.draw(data, options);
    }

    // Function for drawing the bar chart for radiation exposure by staff
    function drawExposureBarChart() {
        
        // Prepare the data for the chart
        var data = google.visualization.arrayToDataTable([
            ['Staff Member', 'Radiation Exposure'], // Column headers
            <?php
            mysqli_data_seek($exposure_result, 0); // Reset the result pointer to the beginning
            while($row = mysqli_fetch_assoc($exposure_result)) {
                $name = $row['first_name'] . ' ' . $row['last_name'];
                echo "['$name', {$row['total_exposure']}],"; // Add each staff member and their total radiation exposure
            }
            ?>
        ]);

        // Configure chart options
        var options = {
            chart: {
                title: 'Total Radiation Exposure by Staff', // Chart title
            },
            bars: 'vertical', // Specifies the direction of the bars
            vAxis: {format: 'decimal'}, // Specifies that the y axis should use decimal formatting
            height: 400 // Specifies the height of the chart
        };

        // Chart is created and drawn
        var chart = new google.visualization.ColumnChart(document.getElementById('exposure_barchart'));
        chart.draw(data, options);
    }
</script>

<div class="container mt-5">
    <h2>📊 Staff Job Reports</h2>


    <!-- Code for job allocation chart and table -->
    <h4 class="mt-4">1. Jobs Allocated to Each Staff Member</h4>
    <div id="jobs_piechart" style="width: 100%; height: 400px;"></div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Jobs Allocated</th>
            </tr>
        </thead>
        <tbody>
            <?php
            mysqli_data_seek($jobs_result, 0);
            while($row = mysqli_fetch_assoc($jobs_result)) {
                echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['job_count']}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Code for radiation exposure chart and table -->
    <h4 class="mt-5">2. Total Radiation Exposure by Staff</h4>
    <div id="exposure_barchart" style="width: 100%; height: 400px;"></div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Total Radiation Exposure</th>
            </tr>
        </thead>
        <tbody>
            <?php
            mysqli_data_seek($exposure_result, 0);
            while($row = mysqli_fetch_assoc($exposure_result)) {
                echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['total_exposure']}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Code for no work allocation table -->
    <h4 class="mt-5">3. Staff With No Work Allocated</h4>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Staff Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while($row = mysqli_fetch_assoc($no_work_result)) {
                echo "<tr><td>{$row['first_name']} {$row['last_name']}</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include_once("includes/footer.php"); ?>
