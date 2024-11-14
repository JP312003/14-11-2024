<?php
// Include the functions.php file to use the logoutUser function
include('../config/functions.php');

// Check if the logout button is clicked and call logoutUser
if (isset($_POST['logout'])) {
    logoutUser();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if user_id is not set
    header("Location: login.php");
    exit;
}

$conn = dbConnect();

// SQL query to fetch user information (name and role)
$user_id = $_SESSION['user_id']; 
$sql = "SELECT name, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // Bind the user_id as an integer
$stmt->execute();
$result_user = $stmt->get_result();

// Get the department_id of the chairperson
$sql_dept = "SELECT department_id FROM users WHERE user_id = ?";
$stmt_dept = $conn->prepare($sql_dept);
$stmt_dept->bind_param("i", $user_id);
$stmt_dept->execute();
$result_dept = $stmt_dept->get_result();
$department = $result_dept->fetch_assoc();
$department_id = $department['department_id'];

// SQL query to fetch curriculums and subjects for the chairperson's department
$sql_curriculum = "SELECT c.curriculum_name, s.subject_code, s.subject_name, s.units, s.year_level, s.semester
                   FROM curriculums c
                   JOIN programs p ON p.program_id = c.program_id
                   JOIN departments d ON d.department_id = p.department_id
                   JOIN subjects s ON s.curriculum_id = c.curriculum_id
                   WHERE d.department_id = ?
                   ORDER BY c.curriculum_name, s.year_level, s.semester, s.subject_code";
$stmt_curriculum = $conn->prepare($sql_curriculum);
$stmt_curriculum->bind_param("i", $department_id);
$stmt_curriculum->execute();
$result_curriculum = $stmt_curriculum->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Scheduling System</title>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../statics/css/styles.css">
    <link rel="stylesheet" href="../statics/css/table.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.18/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>

</head>
<body>
    <div class="top-nav">
        <div class="logo">
            <img src="../statics/img/psu-logo.png" class="psu-logo" height="70px">
        </div>
        <div class="name">
            <h1>AUTOMATED SCHEDULING SYSTEM</h1>
            <h2>PANGASINAN STATE UNIVERSITY - Lingayen Campus</h2>
        </div>
    </div>
    <div class="container">
        <div class="left-nav">
            <div class="profile">
                <img src="../statics/img/psu-background.jpg" class="profile-image">
                <?php
                    // Check if user data was fetched
                    if ($result_user && $result_user->num_rows > 0) {
                        $user_data = $result_user->fetch_assoc();
                        echo "<h3>" . $user_data['name'] . "</h3>";
                        echo "<h5>" . $user_data['role'] . "</h5>";
                    } else {
                        echo "<h3>Unknown User</h3>";
                        echo "<h5>No role assigned</h5>";
                    }
                ?>
            </div>
            <div class="navs">
                <a href="#dashboard">
                    <div class="dashboard-nav nav">
                        <i class='bx bxs-dashboard icon'></i>
                        <h6>Dashboard</h6>
                    </div>
                </a>
                <a href="#schedules">
                    <div class="schedules-nav nav">
                        <i class='bx bxs-time icon' ></i>
                        <h6>Schedules</h6>
                    </div>
                </a>
                <a href="#curriculums">
                    <div class="curriculum-nav nav">
                        <i class='bx bxs-square-rounded icon'></i>
                        <h6>Curriculums</h6>
                    </div>
                </a>
            </div>
            <div class="logout">
                 <form action="" method="post">
                    <button type="submit" name="logout" class="input-logout">
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="container-container">
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="dashboard">
                <h1>Dashboard</h1><hr>
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="schedules">
                <h1>Schedules</h1>  
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
<div class="details" id="curriculums">
                <section class="table__header">
                    <h1>Curriculums</h1>
                    <div class="input-group">
                        <input type="search" placeholder="Search Data...">
                        <img src="../statics/img/search.png" alt="">
                    </div>
                    <!-- Export buttons -->
                    <div class="export__file">
                        <label for="export-file" class="export__file-btn" title="Export File"></label>
                        <input type="checkbox" id="export-file">
                        <div class="export__file-options">
                            <label>Export As &nbsp; &#10140;</label>
                            <label id="toPDF-curriculums">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-curriculums">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                <table>
                        <thead>
                            <tr>
                                <th>Subject Code <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Subject Name <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Units <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Year Level <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Semester <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $previousCurriculum = ''; // To track curriculum changes
                                if ($result_curriculum->num_rows > 0) {
                                    while ($row = $result_curriculum->fetch_assoc()) {
                                        // New heading row for each curriculum
                                        if ($previousCurriculum != $row['curriculum_name']) {
                                            if ($previousCurriculum != '') {
                                                echo '</tbody>'; // Close previous tbody if not the first
                                            }
                                            echo '<tbody>'; // Open new tbody for the new curriculum
                                            echo '<tr><th colspan="5" class="curriculum-heading">' . htmlspecialchars($row['curriculum_name']) . '</th></tr>';
                                            $previousCurriculum = $row['curriculum_name'];
                                        }

                                        // Subject details row
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['subject_code']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['subject_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['units']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['year_level']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['semester']) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody>'; // Close final tbody
                                } else {
                                    echo '<tr><td colspan="5">No curriculums found</td></tr>'; // Optional message when no results are found
                                }
                            ?>
                        </tbody>
                    </table>
                </section>   
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->       
        </div>
    </div>
    <script src="../statics/js/student.js"></script>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>