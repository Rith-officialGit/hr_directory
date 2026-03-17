<?php
// leave_requests.php
require_once 'config.php';

// Handle leave request submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = intval($_POST['employee_id']);
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = sanitize($_POST['reason']);
    
    // Calculate days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $end->diff($start)->days + 1;
    
    $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days, reason) 
            VALUES ($employee_id, '$leave_type', '$start_date', '$end_date', $days, '$reason')";
    
    if($conn->query($sql)) {
        $_SESSION['success'] = "Leave request submitted successfully";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    header("Location: leave_requests.php");
    exit();
}

// Get employees for dropdown
$employees = $conn->query("SELECT id, first_name, last_name FROM employees WHERE status = 'active' ORDER BY first_name");

// Get pending requests
$pending = $conn->query("SELECT l.*, e.first_name, e.last_name, e.department 
                         FROM leave_requests l 
                         JOIN employees e ON l.employee_id = e.id 
                         WHERE l.status = 'pending' 
                         ORDER BY l.created_at DESC");

// Get approved/rejected requests
$history = $conn->query("SELECT l.*, e.first_name, e.last_name, e.department 
                         FROM leave_requests l 
                         JOIN employees e ON l.employee_id = e.id 
                         WHERE l.status != 'pending' 
                         ORDER BY l.created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - HR Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-umbrella-beach mr-2 text-yellow-500"></i>Leave Management
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="attendance.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-clock mr-1"></i>Attendance
                        </a>
                        <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php displayMessage(); ?>

            <!-- Leave Request Form -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-plus-circle text-green-500 mr-2"></i>New Leave Request
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                                <select name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Select Employee</option>
                                    <?php while($emp = $employees->fetch_assoc()): ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                                <select name="leave_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="annual">Annual Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="personal">Personal Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" name="end_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                                <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                                <i class="fas fa-paper-plane mr-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>Pending Requests
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($pending->num_rows > 0): ?>
                                    <?php while($row = $pending->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="font-medium"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $row['department']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 capitalize"><?php echo $row['leave_type']; ?></td>
                                        <td class="px-6 py-4">
                                            <?php echo date('M d', strtotime($row['start_date'])) . ' - ' . date('M d', strtotime($row['end_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4"><?php echo $row['days']; ?> days</td>
                                        <td class="px-6 py-4"><?php echo $row['reason'] ?: '-'; ?></td>
                                        <td class="px-6 py-4">
                                            <button onclick="approveLeave(<?php echo $row['id']; ?>)" 
                                                    class="text-green-600 hover:text-green-900 mr-3">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectLeave(<?php echo $row['id']; ?>)" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No pending leave requests
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leave History -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-history text-blue-500 mr-2"></i>Recent Leave History
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($history->num_rows > 0): ?>
                                    <?php while($row = $history->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 capitalize"><?php echo $row['leave_type']; ?></td>
                                        <td class="px-6 py-4">
                                            <?php echo date('M d', strtotime($row['start_date'])) . ' - ' . date('M d', strtotime($row['end_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4"><?php echo $row['days']; ?> days</td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $colors = [
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800'
                                            ];
                                            $color = isset($colors[$row['status']]) ? $colors[$row['status']] : 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $color; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No leave history found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function approveLeave(id) {
            if(confirm('Approve this leave request?')) {
                window.location.href = 'leave_actions.php?action=approve&id=' + id;
            }
        }
        
        function rejectLeave(id) {
            if(confirm('Reject this leave request?')) {
                window.location.href = 'leave_actions.php?action=reject&id=' + id;
            }
        }
    </script>
</body>
</html>