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

// SQL query to fetch department data
$sql = "SELECT * FROM departments";
$result_departments = $conn->query($sql);

// SQL query to fetch curriculum details with program name
$sql_curriculums = "SELECT c.curriculum_id, c.curriculum_name, p.program_name
                    FROM curriculums c
                    JOIN programs p ON c.program_id = p.program_id"; // Join to get program_name
$result_curriculums = $conn->query($sql_curriculums);

// SQL query to fetch instructor data with department name
$sql_instructors = "SELECT i.instructor_id, i.instructor_name, d.department_name
        FROM instructors i
        JOIN departments d ON i.department_id = d.department_id";
$result_instructors = $conn->query($sql_instructors);

// SQL query to fetch section data with program name and department name
$sql_sections = "SELECT s.section_id, s.section_name, p.program_name, d.department_name
        FROM sections s
        JOIN programs p ON s.program_id = p.program_id
        JOIN departments d ON p.department_id = d.department_id"; 
$result_sections = $conn->query($sql_sections);

// SQL query to fetch room data with department name
$sql_rooms = "SELECT r.room_id, r.room_name, d.department_name
              FROM rooms r
              JOIN departments d ON r.department_id = d.department_id"; 
$result_rooms = $conn->query($sql_rooms);

 // Query to fetch users along with department, program, section names
 $sql_users = "SELECT u.user_id, u.username, u.password, d.department_name, p.program_name, s.section_name, u.role
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.department_id
        LEFT JOIN programs p ON u.program_id = p.program_id
        LEFT JOIN sections s ON u.section_id = s.section_id
        ORDER BY u.role, d.department_name, p.program_name, s.section_name";
$result_users = $conn->query($sql_users);


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
                <a href="#departments">
                    <div class="department-nav nav">
                        <i class='bx bxs-time icon' ></i>
                        <h6>Department</h6>
                    </div>
                </a>
                <a href="#curriculums">
                    <div class="curriculum-nav nav">
                        <i class='bx bxs-square-rounded icon'></i>
                        <h6>Curriculum</h6>
                    </div>
                </a>
                <a href="#instructors">
                    <div class="instructors-nav nav">
                        <i class='bx bxs-square-rounded icon'></i>
                        <h6>Instructors</h6>
                    </div>
                </a>
                <a href="#sections">
                    <div class="sections-nav nav">
                        <i class='bx bxs-square-rounded icon'></i>
                        <h6>Section</h6>
                    </div>
                </a>
                <a href="#rooms">
                    <div class="rooms-nav nav">
                        <i class='bx bxs-square-rounded icon'></i>
                        <h6>Rooms</h6>
                    </div>
                </a>
                <a href="#schedules">
                    <div class="schedules-nav nav">
                        <i class='bx bxs-time icon' ></i>
                        <h6>Schedules</h6>
                    </div>
                </a>
                <a href="#users">
                    <div class="users-nav nav">
                        <i class='bx bxs-time icon' ></i>
                        <h6>Users</h6>
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
            <div class="details" id="dashboard">
                <h1>Dashboard</h1><hr>
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="departments">
                <section class="table__header">
                    <h1>Departments</h1>
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
                            <label id="toPDF-departments">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-departments">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                    <table>
                        <thead>
                            <tr>
                                <th> Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th> Department <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                    // Check if any records were returned
                                    if ($result_departments->num_rows > 0) {
                                        // Output data of each row
                                        while($row = $result_departments->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["department_id"] . "</td>";
                                            echo "<td>" . $row["department_name"] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No users found</td></tr>";
                                    }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </section>   
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
                                <th> Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th> Curriculum <span class="icon-arrow">&UpArrow;</span></th>
                                <th> Program <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Check if any records were returned
                                if ($result_curriculums->num_rows > 0) {
                                    // Output data of each row
                                    while ($row = $result_curriculums->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["curriculum_id"] . "</td>";
                                        echo "<td>" . $row["curriculum_name"] . "</td>";
                                        echo "<td>" . $row["program_name"] . "</td>"; // Display program name
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No records found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </section>   
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="instructors">
                <section class="table__header">
                    <h1>Instructors</h1>
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
                            <label id="toPDF-instructors">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-instructors">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                    <table>
                        <thead>
                            <tr>
                                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Instructor Name <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Department <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Check if any records were returned
                                if ($result_instructors->num_rows > 0) {
                                    $previous_department = ''; // Variable to store the department name

                                    // Output data of each row
                                    while ($row = $result_instructors->fetch_assoc()) {
                                        $current_department = $row["department_name"];
                                        
                                        // Check if the department has changed
                                        if ($previous_department != $current_department) {
                                            // Department header for the new department
                                            if ($previous_department != '') {
                                                echo "</tr>"; // Close the previous department row
                                            }
                                            echo "<tr><th colspan='3'>" . $current_department . "</th></tr>";
                                            $previous_department = $current_department; // Update the previous department
                                        }
                                        
                                        // Output the instructor data under the corresponding department
                                        echo "<tr>";
                                        echo "<td>" . $row["instructor_id"] . "</td>";
                                        echo "<td>" . $row["instructor_name"] . "</td>";
                                        echo "<td>" . $row["department_name"] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No instructors found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </section>  
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="sections">
                <section class="table__header">
                    <h1>Sections</h1>
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
                            <label id="toPDF-sections">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-sections">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                    <table>
                        <thead>
                            <tr>
                                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Section Name <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Program <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Check if any records were returned
                                if ($result_sections->num_rows > 0) {
                                    $previous_department = ''; // Variable to store the department name

                                    // Output data of each row
                                    while ($row = $result_sections->fetch_assoc()) {
                                        $current_department = $row["department_name"];
                                        
                                        // Check if the department has changed
                                        if ($previous_department != $current_department) {
                                            // Department header for the new department
                                            if ($previous_department != '') {
                                                echo "</tr>"; // Close the previous department row
                                            }
                                            echo "<tr><th colspan='3'>" . $current_department . "</th></tr>";
                                            $previous_department = $current_department; // Update the previous department
                                        }
                                        
                                        // Output the section data under the corresponding department
                                        echo "<tr>";
                                        echo "<td>" . $row["section_id"] . "</td>";
                                        echo "<td>" . $row["section_name"] . "</td>";
                                        echo "<td>" . $row["program_name"] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No sections found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </section>   
            </div> 
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="rooms">
                <section class="table__header">
                    <h1>Rooms</h1>
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
                            <label id="toPDF-rooms">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-rooms">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                    <table>
                        <thead>
                            <tr>
                                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Room <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Department <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Check if any records were returned
                                if ($result_rooms->num_rows > 0) {
                                    $previous_department = ''; // Variable to store the department name

                                    // Output data of each row
                                    while ($row = $result_rooms->fetch_assoc()) {
                                        $current_department = $row["department_name"];
                                        
                                        // Check if the department has changed
                                        if ($previous_department != $current_department) {
                                            // Department header for the new department
                                            if ($previous_department != '') {
                                                echo "</tr>"; // Close the previous department row
                                            }
                                            echo "<tr><th colspan='3'>" . $current_department . "</th></tr>";
                                            $previous_department = $current_department; // Update the previous department
                                        }
                                        
                                        // Output the room data under the corresponding department
                                        echo "<tr>";
                                        echo "<td>" . $row["room_id"] . "</td>";
                                        echo "<td>" . $row["room_name"] . "</td>";
                                        echo "<td>" . $row["department_name"] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No rooms found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </section>   
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="schedules">
                <h1>Schedules</h1><hr>
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
            <div class="details" id="users">
                <section class="table__header">
                    <h1>Users</h1>
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
                            <label id="toPDF-users">PDF <img src="../statics/img/pdf.png" alt=""></label>
                            <label id="toEXCEL-users">EXCEL <img src="../statics/img/excel.png" alt=""></label>
                        </div>
                    </div>
                </section>
                <section class="table__body">
                    <table>
                        <thead>
                            <tr>
                                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Username <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Password <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Department <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Program <span class="icon-arrow">&UpArrow;</span></th>
                                <th>Section <span class="icon-arrow">&UpArrow;</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // Check if any records were returned
                                if ($result_users->num_rows > 0) {
                                    $previous_role = ''; // Variable to store the role

                                    // Output data of each row
                                    while ($row = $result_users->fetch_assoc()) {
                                        $current_role = $row["role"];

                                        // Check if the role has changed
                                        if ($previous_role != $current_role) {
                                            // Role header for the new role
                                            if ($previous_role != '') {
                                                echo "</tr>"; // Close the previous role row
                                            }
                                            echo "<tr><th colspan='6'>" . strtoupper($current_role) . "</th></tr>";
                                            $previous_role = $current_role; // Update the previous role
                                        }

                                        // Output the user data under the corresponding role
                                        echo "<tr>";
                                        echo "<td>" . $row["user_id"] . "</td>";
                                        echo "<td>" . $row["username"] . "</td>";
                                        echo "<td>" . $row["password"] . "</td>";
                                        echo "<td>" . $row["department_name"] . "</td>";
                                        echo "<td>" . $row["program_name"] . "</td>";
                                        echo "<td>" . $row["section_name"] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>No users found</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </section>   
            </div>
<!-- -------------------------------------------------------------------------------------------------------------------- -->
        </div>
    </div>
    <script src="../statics/js/admin.js"></script>
    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>