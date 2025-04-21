<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");
?>

<!-- ====================================================== -->
<!-- PAGE CONTENT STARTS HERE -->
<!-- ====================================================== -->

<h2 class="mt-5">Staff & Work Allocation Reports</h2>

<div class="container mt-4">

    <!-- Report 1: Jobs per Staff Member (uses 3 tables: work_schedule, staff, job) -->
    <h3>Jobs Allocated to Staff</h3>
    <p>This report shows how many jobs each staff member has been assigned. Useful for workload monitoring.</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Number of Jobs</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql1 = "SELECT s.first_name, s.last_name, COUNT(ws.id) AS job_count
                     FROM staff s
                     LEFT JOIN work_schedule ws ON s.id = ws.staff_id
                     LEFT JOIN job j ON ws.job_id = j.id
                     GROUP BY s.id
                     ORDER BY job_count DESC";
            $result1 = runAndCheckSQL($connect, $sql1);
            while($row = mysqli_fetch_assoc($result1)) {
                echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['job_count']}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Report 2: Total Radiation Exposure (uses 3 tables: staff, work_schedule, job) -->
    <h3>Total Radiation Exposure by Staff</h3>
    <p>This report calculates the total radiation exposure each staff member has been assigned to. This helps monitor safety thresholds.</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Total Radiation Exposure</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql2 = "SELECT s.first_name, s.last_name, SUM(j.radiation_exposure) AS total_exposure
                     FROM staff s
                     JOIN work_schedule ws ON s.id = ws.staff_id
                     JOIN job j ON ws.job_id = j.id
                     GROUP BY s.id
                     ORDER BY total_exposure DESC";
            $result2 = runAndCheckSQL($connect, $sql2);
            while($row = mysqli_fetch_assoc($result2)) {
                $exposure = $row['total_exposure'] ?? 0;
                echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$exposure}</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Report 3: Staff with No Work (uses 2 tables: staff, work_schedule) -->
    <h3>Staff Members Without Any Job Allocated</h3>
    <p>This report shows which staff have not been assigned any work yet. Useful for balancing assignments.</p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Staff Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql3 = "SELECT s.first_name, s.last_name
                     FROM staff s
                     LEFT JOIN work_schedule ws ON s.id = ws.staff_id
                     WHERE ws.staff_id IS NULL";
            $result3 = runAndCheckSQL($connect, $sql3);
            while($row = mysqli_fetch_assoc($result3)) {
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
