<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
?>

<?php
// Fetch available staff and jobs for filter dropdowns
$staff_sql = "SELECT id, first_name, last_name FROM staff ORDER BY first_name, last_name";
$staff_result = runAndCheckSQL($connect, $staff_sql);

$job_sql = "SELECT id, name FROM job ORDER BY name";
$job_result = runAndCheckSQL($connect, $job_sql);

// Fetch the filter criteria from GET request
$filter_staff = isset($_GET['staff']) ? $_GET['staff'] : '';
$filter_job = isset($_GET['job']) ? $_GET['job'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Query 1: Jobs Allocated to Each Staff Member
$jobs_allocated_sql = "
    SELECT s.first_name, s.last_name, COUNT(ws.id) AS job_count
    FROM staff s
    LEFT JOIN work_schedule ws ON s.id = ws.staff_id
    WHERE ('$filter_staff' = '' OR s.id = '$filter_staff')
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
    WHERE ('$filter_staff' = '' OR s.id = '$filter_staff')
    AND ('$filter_job' = '' OR j.id = '$filter_job')
    AND ('$filter_start_date' = '' OR ws.date >= '$filter_start_date')
    AND ('$filter_end_date' = '' OR ws.date <= '$filter_end_date')
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

<!-- Filters -->
<div class="container mt-5">
    <h2>📊 Staff Job Reports</h2>

    <form method="GET" class="form-inline mt-3">
        <label for="staff">Filter by Staff: </label>
        <select name="staff" id="staff" class="form-control mx-2">
            <option value="">All Staff</option>
            <?php while($row = mysqli_fetch_assoc($staff_result)) { ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $filter_staff) ? 'selected' : ''; ?>>
                    <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="job">Filter by Job: </label>
        <select name="job" id="job" class="form-control mx-2">
            <option value="">All Jobs</option>
            <?php while($row = mysqli_fetch_assoc($job_result)) { ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $filter_job) ? 'selected' : ''; ?>>
                    <?php echo $row['name']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="start_date">Start Date: </label>
        <input type="date" name="start_date" id="start_date" class="form-control mx-2" value="<?php echo $filter_start_date; ?>">

        <label for="end_date">End Date: </label>
        <input type="date" name="end_date" id="end_date" class="form-control mx-2" value="<?php echo $filter_end_date; ?>">

        <button type="submit" class="btn btn-primary mx-2">Apply Filters</button>
    </form>

    <hr>

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

    <!-- Jobs Allocated to Staff Chart and Table -->
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

    <!-- Radiation Exposure Chart and Table -->
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

    <!-- Staff With No Work Allocated -->
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
