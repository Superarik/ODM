<?php
include_once("includes/_connect.php");

$staff = $_POST['staff'] ?? '';
$job = $_POST['job'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Query for Jobs Allocated to Each Staff Member
$jobs_allocated_sql = "
    SELECT CONCAT(s.first_name, ' ', s.last_name) AS name, COUNT(ws.id) AS job_count
    FROM staff s
    LEFT JOIN work_schedule ws ON s.id = ws.staff_id
    WHERE ('$staff' = '' OR s.id = '$staff')
    AND ('$job' = '' OR ws.job_id = '$job')
    AND ('$start_date' = '' OR ws.start_date >= '$start_date')
    AND ('$end_date' = '' OR ws.start_date <= '$end_date')
    GROUP BY s.id
    ORDER BY job_count DESC
";
$jobs_result = mysqli_query($connect, $jobs_allocated_sql);
$jobs_data = [["Staff Member", "Number of Jobs"]];
while ($row = mysqli_fetch_assoc($jobs_result)) {
    $jobs_data[] = [$row['name'], (int)$row['job_count']];
}

// Query for Total Radiation Exposure by Staff
$exposure_sql = "
    SELECT CONCAT(s.first_name, ' ', s.last_name) AS name, SUM(j.radiation_exposure) AS total_exposure
    FROM staff s
    JOIN work_schedule ws ON s.id = ws.staff_id
    JOIN job j ON ws.job_id = j.id
    WHERE ('$staff' = '' OR s.id = '$staff')
    AND ('$job' = '' OR j.id = '$job')
    AND ('$start_date' = '' OR ws.start_date >= '$start_date')
    AND ('$end_date' = '' OR ws.start_date <= '$end_date')
    GROUP BY s.id
    ORDER BY total_exposure DESC
";
$exposure_result = mysqli_query($connect, $exposure_sql);
$exposure_data = [["Staff Member", "Radiation Exposure"]];
while ($row = mysqli_fetch_assoc($exposure_result)) {
    $exposure_data[] = [$row['name'], (float)$row['total_exposure']];
}

// Return data as JSON
echo json_encode([
    'jobs_piechart' => $jobs_data,
    'exposure_barchart' => $exposure_data
]);
?>