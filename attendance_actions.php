<?php
// attendance_actions.php
require_once 'config.php';

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
$date = date('Y-m-d');
$current_time = date('Y-m-d H:i:s');

if($action == 'clock_in') {
    // Check if already clocked in today
    $check = $conn->query("SELECT id FROM attendance WHERE employee_id = $employee_id AND date = '$date'");
    
    if($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Already clocked in today']);
        exit;
    }
    
    // Get settings
    $settings = $conn->query("SELECT * FROM attendance_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $work_start = strtotime($date . ' ' . $settings['work_start_time']);
    $clock_in_time = strtotime($current_time);
    
    // Determine status
    $late_threshold = $settings['late_threshold_minutes'] * 60;
    $status = ($clock_in_time - $work_start) > $late_threshold ? 'late' : 'present';
    
    // Get IP address
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO attendance (employee_id, date, clock_in, clock_in_ip, status) 
            VALUES ($employee_id, '$date', '$current_time', '$ip', '$status')";
    
    if($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Clocked in successfully', 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
}
elseif($action == 'clock_out') {
    // Get today's attendance
    $attendance = $conn->query("SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$date'")->fetch_assoc();
    
    if(!$attendance) {
        echo json_encode(['success' => false, 'message' => 'No clock in record found']);
        exit;
    }
    
    if($attendance['clock_out']) {
        echo json_encode(['success' => false, 'message' => 'Already clocked out today']);
        exit;
    }
    
    // Calculate total hours
    $clock_in = strtotime($attendance['clock_in']);
    $clock_out = strtotime($current_time);
    $total_seconds = $clock_out - $clock_in;
    $total_hours = round($total_seconds / 3600, 2);
    
    // Get IP address
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "UPDATE attendance SET 
            clock_out = '$current_time',
            clock_out_ip = '$ip',
            total_hours = $total_hours
            WHERE id = " . $attendance['id'];
    
    if($conn->query($sql)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Clocked out successfully',
            'hours' => $total_hours
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>