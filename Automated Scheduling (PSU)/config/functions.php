<?php
// Start the session at the beginning of the script
session_start();

// Function to connect to the database
function dbConnect() {
    $conn = mysqli_connect("localhost", "root", "", "dbscheduling", 3306);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Function to authenticate user
function authenticateUser($username, $password) {
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    mysqli_close($conn);

    return $user;
}

// Function to handle login and redirect based on user role
function handleLogin() {
    if (isset($_POST['login'])) {
        $user = $_POST['username'];
        $pass = $_POST['password'];

        $authenticatedUser = authenticateUser($user, $pass);

        if ($authenticatedUser) {
            // Store user data in the session
            $_SESSION['user_id'] = $authenticatedUser['user_id'];
            $_SESSION['role'] = $authenticatedUser['role'];

            // Redirect based on user role
            switch ($authenticatedUser['role']) {
                case "admin":
                    header("Location: admin.php");
                    exit();
                case "chairperson":
                    header("Location: chairperson.php");
                    exit();
                case "teacher":
                    header("Location: teacher.php");
                    exit();
                case "student":
                    header("Location: student.php");
                    exit();
                default:
                    echo "Unauthorized role";
                    exit();
            }
        } else {
            echo "Username or password is incorrect";
        }
    }
}




// Logout function
function logoutUser() {
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("location: ../templates/login.php");
    exit();
}

// Example: Calling logoutUser() function when needed
// logoutUser(); // Uncomment this line to logout when needed
?>
