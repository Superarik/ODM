<!-- Code to include the neccessary php files -->

<?php
include_once("includes/_connect.php");
include_once("includes/header.php");
include_once("includes/nav.php");
include_once("includes/utils.php");

// SQL statement to fetch all staff names for the filter
$all_staff_sql = "SELECT id, first_name, last_name FROM staff ORDER BY first_name, last_name";
$all_staff_result = runAndCheckSQL($connect, $all_staff_sql);

// List for staff member names to be stored in
$staff_list = [];
if ($all_staff_result && mysqli_num_rows($all_staff_result) > 0) {
    while ($staff_row = mysqli_fetch_assoc($all_staff_result)) {
        $staff_list[] = $staff_row; // Store id and names
    }
}

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

// Query 3: Staff with the Most Jobs in a Specific Location
$jobs_by_location_sql = "
    SELECT 
        location.name AS location_name, 
        staff.first_name, 
        staff.last_name, 
        COUNT(work_schedule.id) AS job_count
    FROM work_schedule
    JOIN job ON work_schedule.job_id = job.id
    JOIN location ON job.location_id = location.id
    JOIN staff ON work_schedule.staff_id = staff.id
    GROUP BY location.id, staff.id
    ORDER BY location_name, staff.first_name, staff.last_name
";
$jobs_by_location_result = runAndCheckSQL($connect, $jobs_by_location_sql);

// Query 4: Staff With No Work Allocated
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
    google.charts.setOnLoadCallback(initialiseCharts);

    // Global variable to store data for all workers for how many jobs they have
    var allJobsData = [
        ['Staff Member', 'Number of Jobs'], // Header row
        <?php
        if ($jobs_result && mysqli_num_rows($jobs_result) > 0) {
            mysqli_data_seek($jobs_result, 0); // Reset the result pointer
            while($row = mysqli_fetch_assoc($jobs_result)) {
                // Use staff_id as a unique identifier if names clash, but display name
                $name = $row['first_name'] . ' ' . $row['last_name'];
                 // Ensure name is properly escaped for JavaScript use
                $js_safe_name = addslashes($name);
                // Adds name and number of jobs to the variable
                echo "['$js_safe_name', {$row['job_count']}],";
            }
        }
        ?>
    ];

    // Global variable to store data for all workers for how much exposure they have
    var allExposureData = [
        ['Staff Member', 'Radiation Exposure'], // Header row
        <?php
         if ($exposure_result && mysqli_num_rows($exposure_result) > 0) {
            mysqli_data_seek($exposure_result, 0); // Reset the result pointer
            while($row = mysqli_fetch_assoc($exposure_result)) {
                $name = $row['first_name'] . ' ' . $row['last_name'];
                $js_safe_name = addslashes($name);
                // Handle potential null value
                $exposure = isset($row['total_exposure']) ? $row['total_exposure'] : 0;
                // Adds name and amount of exposure to the variable
                echo "['$js_safe_name', {$exposure}],";
            }
        }
        ?>
    ];

    // Global variable to store data for where they work
    var jobsByLocationData = [
    ['Location', 'Number of Jobs'], // Header row
    <?php
    if ($jobs_by_location_result && mysqli_num_rows($jobs_by_location_result) > 0) {
        mysqli_data_seek($jobs_by_location_result, 0); // Reset pointer
        while($row = mysqli_fetch_assoc($jobs_by_location_result)) {
            $location = addslashes($row['location_name']);
            // Adds location and job count to the variable
            echo "['$location', {$row['job_count']}],";
        }
    }
    ?>
];

    // Function to get selected employee names from the filter
    function getSelectedEmployeeNames() {
        const selectElement = document.getElementById('employeeFilterSelect');
        const selectedNames = [];
        if (selectElement) {
             for (const option of selectElement.selectedOptions) {
                selectedNames.push(option.value);
             }
        }
        return selectedNames;
    }

    // Function to filter data based on selection
    function filterData(originalData, selectedNames) {
        // If no names are selected, show all data
        if (selectedNames.length === 0) {
            return originalData;
        }

        // Start with the header row
        let filteredData = [originalData[0]];

        // Iterate through the original data (skip header row)
        for (let i = 1; i < originalData.length; i++) {
            const staffName = originalData[i][0]; // Assuming name is the first column
            if (selectedNames.includes(staffName)) {
                filteredData.push(originalData[i]);
            }
        }
        
        return filteredData;
    }

    // Main drawing function
    function drawFilteredCharts() {
        const selectedNames = getSelectedEmployeeNames();

        // Prepares data to be drawn for the first chart
        const filteredJobs = filterData(allJobsData, selectedNames);
         // Pass filtered data when drawing
        drawJobsPieChart(filteredJobs);

        // Prepares data to be drawn for the first chart
        const filteredExposure = filterData(allExposureData, selectedNames);
        // Pass filtered data when drawing
        drawExposureBarChart(filteredExposure);

        // Pass unfiltered data for the third chart
        drawJobsByLocationChart(jobsByLocationData);
    }

    // Initial setup function
    function initialiseCharts() {
        // Draw charts with all data initially
        drawJobsPieChart(allJobsData);
        drawExposureBarChart(allExposureData);
        drawJobsByLocationChart(jobsByLocationData);

        // Add event listener to the filter button
        const filterButton = document.getElementById('applyFilterButton');
        // When the apply filter button is pressed, charts are redrawn and filtered
        if (filterButton) {
            filterButton.addEventListener('click', drawFilteredCharts);
        }
    }


    // Function for drawing the bar chart for radiation exposure by staff
    function drawExposureBarChart(dataArray) {
         // Check if we have data beyond the header row
        if (dataArray.length <= 1) {
             document.getElementById('exposure_barchart').innerHTML = '<p class="text-center text-muted">No data to display for selected staff.</p>';
             return; // Don't draw chart if no data
        }
        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            chart: {
                title: 'Total Radiation Exposure by Staff',
            },
            bars: 'vertical',
            vAxis: {format: 'decimal'},
            height: 400, // Keep specified height
            legend: { position: 'none' } // Hide legend if many bars make it cluttered
        };

        // Create and draw the chart
        var chart = new google.visualization.ColumnChart(document.getElementById('exposure_barchart'));
        chart.draw(data, options);
    }

    // Function to draw the bar chart for jobs by location
    function drawJobsByLocationChart(dataArray) {
        // Check if we have data beyond the header row
        if (dataArray.length <= 1) {
            document.getElementById('jobs_by_location_chart').innerHTML = '<p class="text-center text-muted">No data to display for jobs by location.</p>';
            return; // Don't draw chart if no data
        }
        var data = google.visualization.arrayToDataTable(dataArray);

        // Chart options
        var options = {
            title: 'Jobs by Location and Staff',
            height: 400,
            hAxis: { title: 'Location' },
            vAxis: { title: 'Number of Jobs', format: 'decimal' },
            legend: { position: 'top', maxLines: 3 },
            bar: { groupWidth: '75%' },
            isStacked: true
        };

        // Create and draw the chart
        var chart = new google.visualization.ColumnChart(document.getElementById('jobs_by_location_chart'));
        chart.draw(data, options);
    }

    // Function to draw the pie chart for jobs allocated to staff
    function drawJobsPieChart(dataArray) {
        // Check if we have data beyond the header row
        const filteredData = dataArray.filter((row, index) => {
            return index === 0 || row[1] > 0; // Keep the header row or rows with values > 0
        });
        if (filteredData.length <= 1) {
             document.getElementById('jobs_piechart').innerHTML = '<p class="text-center text-muted">No data to display for selected staff.</p>';
             return; // Don't draw chart if no data
        }

        var data = google.visualization.arrayToDataTable(dataArray);

        // Chart options
        var options = {
            title: 'Jobs Allocated to Staff',
            pieHole: 0.4,
            // Ensure height is reset if previously cleared
            height: 400
        };

        // Create and draw the chart
        var chart = new google.visualization.PieChart(document.getElementById('jobs_piechart'));
        chart.draw(data, options);
    }
</script>

<div class="container mt-5">
    <h2>📊 Staff Job Reports</h2>

    <!-- Box at top of page to allow user to filter staff -->
    <div class="card my-4">
        <div class="card-header">
            Filter by Staff (only affects first two charts)
        </div>
        <div class="card-body">
            <form id="filterForm" onsubmit="event.preventDefault(); drawFilteredCharts();">
                <div class="mb-3">
                    <label for="employeeFilterSelect" class="form-label">Select Employees (Ctrl + Click for multiple):</label>
                    <select multiple class="form-select" id="employeeFilterSelect" name="selected_employees[]" size="8">
                        <?php
                        // Populate the dropdown with staff names
                        foreach ($staff_list as $staff) {
                            $name = htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']);
                            echo "<option value=\"$name\">$name</option>";
                        }
                        ?>
                    </select>
                </div>
                 <button type="button" id="applyFilterButton" class="btn btn-primary">Apply Filter</button>
                 <button type="button" class="btn btn-secondary" onclick="document.getElementById('employeeFilterSelect').selectedIndex = -1; drawFilteredCharts();">Show All</button>
            </form>
        </div>
    </div>


    

    <h4 class="mt-4">1. Jobs Allocated to Each Staff Member</h4>
    <div id="jobs_piechart" style="width: 100%; min-height: 100px;"></div>
    <h5 class="mt-3">Full Data Table (All Staff)</h5>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Jobs Allocated</th>
            </tr>
        </thead>
        <tbody>
            <?php /* PHP loop for jobs table */
            if ($jobs_result && mysqli_num_rows($jobs_result) > 0) {
                 mysqli_data_seek($jobs_result, 0);
                while($row = mysqli_fetch_assoc($jobs_result)) {
                     echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['job_count']}</td></tr>";
                 }
            } else {
                echo "<tr><td colspan='2'>No job data found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h4 class="mt-5">2. Total Radiation Exposure by Staff</h4>
     <div id="exposure_barchart" style="width: 100%; min-height: 100px;"></div>
     <h5 class="mt-3">Full Data Table (All Staff)</h5>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Total Radiation Exposure</th>
            </tr>
        </thead>
        <tbody>
            <?php /* PHP loop for exposure table */
             if ($exposure_result && mysqli_num_rows($exposure_result) > 0) {
                mysqli_data_seek($exposure_result, 0);
                while($row = mysqli_fetch_assoc($exposure_result)) {
                    $exposure_display = isset($row['total_exposure']) ? $row['total_exposure'] : 'N/A';
                     echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$exposure_display}</td></tr>";
                 }
            } else {
                 echo "<tr><td colspan='2'>No exposure data found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h4 class="mt-5">3. Jobs by Location and Staff</h4>
    <div id="jobs_by_location_chart" style="width: 100%; min-height: 100px;"></div>
    <h5 class="mt-3">Full Data Table (Jobs by Location)</h5>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Location</th>
                <th>Staff Name</th>
                <th>Number of Jobs</th>
            </tr>
        </thead>
        <tbody>
            <?php /* PHP loop for location table */
            if ($jobs_by_location_result && mysqli_num_rows($jobs_by_location_result) > 0) {
                mysqli_data_seek($jobs_by_location_result, 0);
                while ($row = mysqli_fetch_assoc($jobs_by_location_result)) {
                    echo "<tr><td>{$row['location_name']}</td><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['job_count']}</td></tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No data found for jobs by location.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <h4 class="mt-5">4. Staff With No Work Allocated</h4>
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Staff Name</th>
            </tr>
        </thead>
        <tbody>
            <?php /* PHP loop for no-work table */
             if ($no_work_result && mysqli_num_rows($no_work_result) > 0) {
                mysqli_data_seek($no_work_result, 0);
                while($row = mysqli_fetch_assoc($no_work_result)) {
                     echo "<tr><td>{$row['first_name']} {$row['last_name']}</td></tr>";
                 }
            } else {
                 echo "<tr><td>All staff have allocated work or no staff found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include_once("includes/footer.php"); ?>
