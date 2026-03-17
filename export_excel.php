<?php
// export_excel.php
require_once 'config.php';

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="employee_directory_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
header('Pragma: public');

// Get filter parameters
$search_term = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'name';
$department_filter = isset($_GET['department']) ? sanitize($_GET['department']) : '';

// Build query
$sql = "SELECT * FROM employees WHERE status = 'active'";

if (!empty($search_term)) {
    if ($search_type == 'name') {
        $sql .= " AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%')";
    } else {
        $sql .= " AND department LIKE '%$search_term%'";
    }
}

if (!empty($department_filter)) {
    $sql .= " AND department = '$department_filter'";
}

$sql .= " ORDER BY first_name ASC";
$result = $conn->query($sql);

// Start output buffering and clear any previous output
ob_clean();

// Write HTML table format (works with Excel)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employee Directory</title>
    <style>
        /* Excel-compatible styles */
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
            background-color: #E5E7EB;
            text-align: center;
            padding: 10px;
        }
        .header {
            font-weight: bold;
            background-color: #4F46E5;
            color: #FFFFFF;
            text-align: center;
            padding: 8px;
            border: 1px solid #000000;
        }
        .cell {
            border: 1px solid #000000;
            padding: 5px;
            vertical-align: middle;
        }
        .number {
            border: 1px solid #000000;
            padding: 5px;
            text-align: right;
        }
        .date {
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
        }
        .summary {
            background-color: #F3F4F6;
            font-weight: bold;
            padding: 5px;
            border: 1px solid #000000;
        }
    </style>
</head>
<body>
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <!-- Title Row -->
        <tr>
            <td colspan="12" class="title">
                <strong>EMPLOYEE DIRECTORY - <?php echo date('F d, Y'); ?></strong>
            </td>
        </tr>
        
        <!-- Empty Row -->
        <tr><td colspan="12">&nbsp;</td></tr>
        
        <!-- Header Row -->
        <tr>
            <th class="header">Employee ID</th>
            <th class="header">First Name</th>
            <th class="header">Last Name</th>
            <th class="header">Email</th>
            <th class="header">Phone</th>
            <th class="header">Department</th>
            <th class="header">Position</th>
            <th class="header">Salary</th>
            <th class="header">Hire Date</th>
            <th class="header">Emergency Contact</th>
            <th class="header">Emergency Phone</th>
            <th class="header">Address</th>
        </tr>

        <?php 
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
        ?>
        <tr>
            <td class="cell"><?php echo htmlspecialchars($row['employee_id']); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['first_name']); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['last_name']); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['email']); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['department'] ?: 'N/A'); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['position'] ?: 'N/A'); ?></td>
            <td class="number"><?php echo $row['salary'] ? '$' . number_format($row['salary'], 2) : 'N/A'; ?></td>
            <td class="date"><?php echo $row['hire_date'] ? date('m/d/Y', strtotime($row['hire_date'])) : 'N/A'; ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['emergency_contact'] ?: 'N/A'); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['emergency_phone'] ?: 'N/A'); ?></td>
            <td class="cell"><?php echo htmlspecialchars($row['address'] ?: 'N/A'); ?></td>
        </tr>
        <?php 
            }
            
            // Calculate summary data
            $count = $result->num_rows;
            $salary_query = "SELECT 
                COALESCE(SUM(salary), 0) as total_salary, 
                COALESCE(AVG(salary), 0) as avg_salary,
                COALESCE(MIN(salary), 0) as min_salary,
                COALESCE(MAX(salary), 0) as max_salary
                FROM employees WHERE status = 'active'";
            if (!empty($department_filter)) {
                $salary_query .= " AND department = '$department_filter'";
            }
            $salary_result = $conn->query($salary_query);
            $salary_data = $salary_result->fetch_assoc();
        ?>
        
        <!-- Empty Row -->
        <tr><td colspan="12">&nbsp;</td></tr>
        
        <!-- Summary Section -->
        <tr>
            <td colspan="12" style="background-color: #E5E7EB; font-weight: bold; padding: 8px; text-align: center;">
                SUMMARY STATISTICS
            </td>
        </tr>
        
        <tr>
            <td colspan="5" class="summary">Total Employees:</td>
            <td colspan="7" class="cell"><?php echo $count; ?></td>
        </tr>
        <tr>
            <td colspan="5" class="summary">Total Salary Budget:</td>
            <td colspan="7" class="number">$<?php echo number_format($salary_data['total_salary'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="5" class="summary">Average Salary:</td>
            <td colspan="7" class="number">$<?php echo number_format($salary_data['avg_salary'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="5" class="summary">Minimum Salary:</td>
            <td colspan="7" class="number">$<?php echo number_format($salary_data['min_salary'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="5" class="summary">Maximum Salary:</td>
            <td colspan="7" class="number">$<?php echo number_format($salary_data['max_salary'], 2); ?></td>
        </tr>
        
        <!-- Department Breakdown -->
        <tr><td colspan="12">&nbsp;</td></tr>
        <tr>
            <td colspan="12" style="background-color: #E5E7EB; font-weight: bold; padding: 8px; text-align: center;">
                DEPARTMENT BREAKDOWN
            </td>
        </tr>
        
        <?php
        $dept_query = "SELECT department, COUNT(*) as emp_count, SUM(salary) as dept_salary 
                       FROM employees WHERE status = 'active' GROUP BY department";
        if (!empty($department_filter)) {
            $dept_query = "SELECT department, COUNT(*) as emp_count, SUM(salary) as dept_salary 
                          FROM employees WHERE status = 'active' AND department = '$department_filter' 
                          GROUP BY department";
        }
        $dept_result = $conn->query($dept_query);
        
        if ($dept_result->num_rows > 0) {
            ?>
            <tr>
                <th class="header" colspan="4">Department</th>
                <th class="header" colspan="4">Employee Count</th>
                <th class="header" colspan="4">Total Salary</th>
            </tr>
            <?php
            while($dept = $dept_result->fetch_assoc()) {
                ?>
                <tr>
                    <td colspan="4" class="cell"><?php echo htmlspecialchars($dept['department'] ?: 'Unassigned'); ?></td>
                    <td colspan="4" class="number"><?php echo $dept['emp_count']; ?></td>
                    <td colspan="4" class="number">$<?php echo number_format($dept['dept_salary'] ?: 0, 2); ?></td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="12" class="cell">No department data available</td></tr>';
        }
        ?>
        
        <?php } else { ?>
        <tr>
            <td colspan="12" class="cell" style="text-align: center; padding: 20px;">
                <strong>No employees found matching the criteria</strong>
            </td>
        </tr>
        <?php } ?>
        
        <!-- Export Information -->
        <tr><td colspan="12">&nbsp;</td></tr>
        <tr>
            <td colspan="12" style="background-color: #F3F4F6; padding: 8px; font-size: 11px;">
                <strong>Export Information:</strong><br>
                Export Date: <?php echo date('Y-m-d H:i:s'); ?><br>
                Exported by: HR Department<br>
                Filters Applied: 
                <?php 
                $filters = [];
                if (!empty($search_term)) $filters[] = "Search: $search_term ($search_type)";
                if (!empty($department_filter)) $filters[] = "Department: $department_filter";
                echo !empty($filters) ? implode(' | ', $filters) : 'None';
                ?><br>
                Total Records: <?php echo $result->num_rows; ?>
            </td>
        </tr>
    </table>
</body>
</html>
<?php
exit();
?>