<?php
// export_summary.php
require_once 'config.php';

// Get statistics
$stats = [];

// Total employees
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stats['total_employees'] = $result->fetch_assoc()['total'];

// By department
$dept_query = "SELECT department, COUNT(*) as count FROM employees WHERE status = 'active' GROUP BY department";
$dept_result = $conn->query($dept_query);
$stats['by_department'] = [];
while($row = $dept_result->fetch_assoc()) {
    $stats['by_department'][$row['department']] = $row['count'];
}

// Salary stats
$salary_query = "SELECT 
    MIN(salary) as min_salary,
    MAX(salary) as max_salary,
    AVG(salary) as avg_salary,
    SUM(salary) as total_salary
    FROM employees WHERE status = 'active'";
$salary_result = $conn->query($salary_query);
$stats['salary'] = $salary_result->fetch_assoc();

// Recent hires (last 30 days)
$recent_query = "SELECT COUNT(*) as recent FROM employees 
                 WHERE status = 'active' 
                 AND hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$recent_result = $conn->query($recent_query);
$stats['recent_hires'] = $recent_result->fetch_assoc()['recent'];

header('Content-Type: application/json');
echo json_encode($stats, JSON_PRETTY_PRINT);
?>