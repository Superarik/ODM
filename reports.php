<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
?>

<?php

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
        const filters = {
            staff: Array.from(document.getElementById('filter_staff').selectedOptions).map(option => option.value),
            job: document.getElementById('filter_job').value
        };

        fetchFilteredData(filters, drawJobsPieChart, 'jobs_piechart');
        fetchFilteredData(filters, drawExposureBarChart, 'exposure_barchart');
    }

    function fetchFilteredData(filters, callback, chartId) {
        $.post('fetch_filtered_data.php', filters, function(response) {
            const data = JSON.parse(response);
            callback(data, chartId);
        });
    }

    function drawJobsPieChart(data, chartId) {
        const chartData = google.visualization.arrayToDataTable(data);
        const options = { title: 'Jobs Allocated to Staff', pieHole: 0.4 };
        const chart = new google.visualization.PieChart(document.getElementById(chartId));
        chart.draw(chartData, options);
    }

    function drawExposureBarChart(data, chartId) {
        const chartData = google.visualization.arrayToDataTable(data);
        const options = {
            chart: { title: 'Total Radiation Exposure by Staff' },
            bars: 'vertical',
            vAxis: { format: 'decimal' },
            height: 400
        };
        const chart = new google.visualization.ColumnChart(document.getElementById(chartId));
        chart.draw(chartData, options);
    }

    // Redraw charts when filters change
    document.getElementById('filter_staff').addEventListener('change', drawCharts);
    document.getElementById('filter_job').addEventListener('change', drawCharts);
</script>

<div class="container mt-5">
    <h2>📊 Staff Job Reports</h2>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label for="filter_staff">Filter by Staff (Multiple Selection)</label>
            <select id="filter_staff" class="form-control" multiple>
                <?php
                $staff_result = mysqli_query($connect, "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM staff");
                while ($staff = mysqli_fetch_assoc($staff_result)) {
                    echo "<option value='{$staff['id']}'>{$staff['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="filter_job">Filter by Job</label>
            <select id="filter_job" class="form-control">
                <option value="">All Jobs</option>
                <?php
                $job_result = mysqli_query($connect, "SELECT id, name FROM job");
                while ($job = mysqli_fetch_assoc($job_result)) {
                    echo "<option value='{$job['id']}'>{$job['name']}</option>";
                }
                ?>
            </select>
        </div>
    </div>

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

<?php include_once("includes/footer.php"); ?>
