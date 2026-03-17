<?php
// delete.php
require_once 'config.php';

// Get employee ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Instead of hard delete, we'll soft delete by updating status to inactive
$sql = "UPDATE employees SET status = 'inactive' WHERE id = $id";

if ($conn->query($sql)) {
    $_SESSION['success'] = "Employee offboarded successfully!";
} else {
    $_SESSION['error'] = "Error offboarding employee: " . $conn->error;
}

header("Location: index.php");
exit();
?>