<?php
// attendance.php
require_once 'config.php';

// Get current employee if logged in (you'll need to add login system)
// For now, we'll use a session or default to first employee
session_start();
$current_employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 1;

// Get today's attendance
$today = date('Y-m-d');
$attendance_query = "SELECT a.*, e.first_name, e.last_name 
                     FROM attendance a 
                     JOIN employees e ON a.employee_id = e.id 
                     WHERE a.date = '$today' 
                     ORDER BY a.clock_in DESC";
$attendance_result = $conn->query($attendance_query);

// Get today's status for current employee
$my_attendance = $conn->query("SELECT * FROM attendance WHERE employee_id = $current_employee_id AND date = '$today'")->fetch_assoc();

// Get statistics
$stats = [];

// Total present today
$present_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'present'")->fetch_assoc()['count'];

// Total employees
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];

// On time vs late
$on_time = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'present' AND clock_in <= CONCAT('$today', ' 09:00:00')")->fetch_assoc()['count'];
$late = $present_today - $on_time;

// This week summary
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$week_stats = $conn->query("SELECT 
    COUNT(DISTINCT employee_id) as total_employees,
    COUNT(*) as total_attendance,
    SUM(total_hours) as total_hours
    FROM attendance 
    WHERE date BETWEEN '$week_start' AND '$week_end'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - HR Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .status-badge {
            transition: all 0.3s ease;
        }
        .status-badge:hover {
            transform: scale(1.05);
        }
        .clock-btn {
            transition: all 0.3s ease;
        }
        .clock-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2);
        }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        .clocked-in {
            animation: pulse-green 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-clock mr-2 text-green-500"></i>Attendance System
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i>Dashboard
                        </a>
                        <a href="attendance_reports.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-chart-bar mr-1"></i>Reports
                        </a>
                        <a href="leave_requests.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-umbrella-beach mr-1"></i>Leave
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Quick Clock In/Out Section -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Welcome back!</h2>
                        <p class="text-blue-100"><?php echo date('l, F d, Y'); ?></p>
                        <p class="text-blue-100 mt-2">Current time: <span id="currentTime" class="font-bold"></span></p>
                    </div>
                    <div class="flex space-x-4">
                        <?php if(!$my_attendance || !$my_attendance['clock_in']): ?>
                            <button onclick="clockIn()" 
                                    class="clock-btn bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg font-bold text-lg flex items-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                CLOCK IN
                            </button>
                        <?php elseif($my_attendance && !$my_attendance['clock_out']): ?>
                            <div class="text-center">
                                <div class="bg-green-500 rounded-lg p-4 clocked-in">
                                    <p class="text-sm">Clocked in at:</p>
                                    <p class="font-bold"><?php echo date('h:i A', strtotime($my_attendance['clock_in'])); ?></p>
                                </div>
                                <button onclick="clockOut()" 
                                        class="mt-2 clock-btn bg-red-500 hover:bg-red-600 text-white px-8 py-4 rounded-lg font-bold text-lg flex items-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    CLOCK OUT
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-500 rounded-lg p-4">
                                <p class="text-sm">Today's work complete</p>
                                <p class="font-bold">Total: <?php echo $my_attendance['total_hours']; ?> hours</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Present Today</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $present_today; ?></p>
                            <p class="text-xs text-gray-400">out of <?php echo $total_employees; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">On Time</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $on_time; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Late</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $late; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-hourglass-half text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Hours</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($week_stats['total_hours'], 1); ?></p>
                            <p class="text-xs text-gray-400">this week</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-pink-100 rounded-full">
                            <i class="fas fa-percent text-pink-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Attendance Rate</p>
                            <p class="text-2xl font-bold text-gray-800">
                                <?php echo $total_employees > 0 ? round(($present_today / $total_employees) * 100) : 0; ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance List -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6 lg:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <i class="fas fa-list mr-2 text-blue-500"></i>Today's Attendance
                        </h2>
                        <div class="flex space-x-2">
                            <button onclick="exportAttendance()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                <i class="fas fa-download mr-1"></i>Export
                            </button>
                            <button onclick="refreshAttendance()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                                <i class="fas fa-sync-alt mr-1"></i>Refresh
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if($attendance_result->num_rows > 0): ?>
                                    <?php while($row = $attendance_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php 
                                                $dept = $conn->query("SELECT department FROM employees WHERE id = " . $row['employee_id'])->fetch_assoc();
                                                echo $dept['department'] ?: 'N/A';
                                                ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if($row['clock_in']): ?>
                                                    <span class="text-sm <?php echo strtotime($row['clock_in']) > strtotime($today . ' 09:00:00') ? 'text-red-600' : 'text-green-600'; ?>">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['clock_in'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">--:--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if($row['clock_out']): ?>
                                                    <span class="text-sm">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?php echo date('h:i A', strtotime($row['clock_out'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">--:--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if($row['total_hours'] > 0): ?>
                                                    <span class="text-sm font-bold">
                                                        <?php echo $row['total_hours']; ?> hrs
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">0 hrs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php
                                                $status_colors = [
                                                    'present' => 'bg-green-100 text-green-800',
                                                    'late' => 'bg-yellow-100 text-yellow-800',
                                                    'absent' => 'bg-red-100 text-red-800',
                                                    'half-day' => 'bg-orange-100 text-orange-800',
                                                    'leave' => 'bg-blue-100 text-blue-800'
                                                ];
                                                $color = isset($status_colors[$row['status']]) ? $status_colors[$row['status']] : 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-2 py-1 text-xs rounded-full <?php echo $color; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <button onclick="editAttendance(<?php echo $row['id']; ?>)" 
                                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="viewAttendance(<?php echo $row['id']; ?>)" 
                                                        class="text-green-600 hover:text-green-900">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No attendance records for today
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Weekly Attendance Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-chart-line text-blue-500 mr-2"></i>Weekly Attendance
                    </h3>
                    <canvas id="weeklyChart" height="200"></canvas>
                </div>
                
                <!-- Department Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-chart-pie text-green-500 mr-2"></i>Attendance by Department
                    </h3>
                    <canvas id="departmentChart" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Leave Requests -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-umbrella-beach mr-2 text-yellow-500"></i>Recent Leave Requests
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $leave_query = "SELECT l.*, e.first_name, e.last_name 
                                               FROM leave_requests l 
                                               JOIN employees e ON l.employee_id = e.id 
                                               ORDER BY l.created_at DESC LIMIT 5";
                                $leave_result = $conn->query($leave_query);
                                
                                if($leave_result->num_rows > 0):
                                    while($leave = $leave_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php echo $leave['first_name'] . ' ' . $leave['last_name']; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="capitalize"><?php echo $leave['leave_type']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo date('M d', strtotime($leave['start_date'])) . ' - ' . date('M d', strtotime($leave['end_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4"><?php echo $leave['days']; ?> days</td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $status_colors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $color = isset($status_colors[$leave['status']]) ? $status_colors[$leave['status']] : 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $color; ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No leave requests found
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
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Clock In Function
        function clockIn() {
            if(confirm('Are you sure you want to clock in?')) {
                fetch('attendance_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clock_in&employee_id=<?php echo $current_employee_id; ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Clocked in successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Clock Out Function
        function clockOut() {
            if(confirm('Are you sure you want to clock out?')) {
                fetch('attendance_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clock_out&employee_id=<?php echo $current_employee_id; ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Clocked out successfully! Total hours: ' + data.hours);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Weekly Chart
            const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
            new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Present',
                        data: [12, 15, 14, 16, 13, 8, 5],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Department Chart
            const deptCtx = document.getElementById('departmentChart').getContext('2d');
            new Chart(deptCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Engineering', 'Sales', 'Marketing', 'HR', 'IT'],
                    datasets: [{
                        data: [8, 5, 4, 3, 6],
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(34, 197, 94)',
                            'rgb(249, 115, 22)',
                            'rgb(168, 85, 247)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });

        function exportAttendance() {
            window.location.href = 'export_attendance.php';
        }

        function refreshAttendance() {
            location.reload();
        }

        function editAttendance(id) {
            window.location.href = 'edit_attendance.php?id=' + id;
        }

        function viewAttendance(id) {
            window.location.href = 'view_attendance.php?id=' + id;
        }
    </script>
</body>
</html>