<?php
// config.php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'employee_directory');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Function to generate next employee ID
function generateEmployeeID($conn) {
    // Get the last employee ID
    $sql = "SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['employee_id'];
        
        // Extract the number part (assuming format like EMP001 or just 001)
        $number = intval(preg_replace('/[^0-9]/', '', $last_id));
        $new_number = $number + 1;
        
        // Format with leading zeros (3 digits)
        $formatted_number = str_pad($new_number, 3, '0', STR_PAD_LEFT);
        
        // You can add a prefix if wanted (e.g., EMP, HR, etc.)
        // return 'EMP' . $formatted_number;
        return $formatted_number;
    } else {
        // First employee - start with 001
        return '001';
    }
}

// Function to sanitize input
function sanitize($input) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}

// Function to display success/error messages
function displayMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . 
             $_SESSION['success'] . 
             '</div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . 
             $_SESSION['error'] . 
             '</div>';
        unset($_SESSION['error']);
    }
}
?>