<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
?>

<?php

<!-- ====================================================== -->
<!-- PAGE CONTENT STARTS HERE -->
<!-- ====================================================== -->

// Query 1: Jobs Allocated to Each Staff Member
$jobs_allocated_sql = "
    SELECT s.first_name, s.last_name, COUNT(ws.id) AS job_count
    FROM staff s
    LEFT JOIN work_schedule ws ON s.id = ws.staff_id
    GROUP BY s.id
    ORDER BY job_count DESC
";
$jobs_result = runAndCheckSQL($connect, $jobs_allocated_sql);

// Query 2: Total Radiation Exposure by Staff
$exposure_sql = "
    SELECT s.first_name, s.last_name, SUM(j.radiation_exposure) AS total_exposure
    FROM staff s
    JOIN work_schedule ws ON s.id = ws.staff_id
    JOIN job j ON ws.job_id = j.id
    GROUP BY s.id
    ORDER BY total_exposure DESC
";
$exposure_result = runAndCheckSQL($connect, $exposure_sql);

// Query 3: Staff With No Work Allocated
$no_work_sql = "
    SELECT s.first_name, s.last_name
    FROM staff s
    LEFT JOIN work_schedule ws ON s.id = ws.staff_id
    WHERE ws.id IS NULL
";
$no_work_result = runAndCheckSQL($connect, $no_work_sql);
?>

<!-- Load Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        drawJobsPieChart();
        drawExposureBarChart();
    }

    function drawJobsPieChart() {
        var data = google.visualization.arrayToDataTable([
            ['Staff Member', 'Number of Jobs'],
            <?php
            mysqli_data_seek($jobs_result, 0);
            while($row = mysqli_fetch_assoc($jobs_result)) {
                $name = $row['first_name'] . ' ' . $row['last_name'];
                echo "['$name', {$row['job_count']}],";
            }
            ?>
        ]);

        var options = {
            title: 'Jobs Allocated to Staff',
            pieHole: 0.4
        };

        var chart = new google.visualization.PieChart(document.getElementById('jobs_piechart'));
        chart.draw(data, options);
    }

    function drawExposureBarChart() {
        var data = google.visualization.arrayToDataTable([
            ['Staff Member', 'Radiation Exposure'],
            <?php
            mysqli_data_seek($exposure_result, 0);
            while($row = mysqli_fetch_assoc($exposure_result)) {
                $name = $row['first_name'] . ' ' . $row['last_name'];
                echo "['$name', {$row['total_exposure']}],";
            }
            ?>
        ]);

        var options = {
            chart: {
                title: 'Total Radiation Exposure by Staff',
            },
            bars: 'vertical',
            vAxis: {format: 'decimal'},
            height: 400
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('exposure_barchart'));
        chart.draw(data, options);
    }
</script>

<div class="container mt-5">
    <h2>📊 Staff Job Reports</h2>

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



<!-- ====================================================== -->
<!-- PAGE CONTENT ENDS HERE -->
<!-- ====================================================== -->

<?php include_once("includes/footer.php"); ?>
