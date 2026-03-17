<?php
// export_xlsx_advanced.php
require_once 'config.php';

// Get export parameters
$type = isset($_GET['type']) ? $_GET['type'] : 'full';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'first_name';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$fields = isset($_GET['fields']) ? $_GET['fields'] : ['personal', 'contact', 'employment', 'emergency'];
$include_summary = isset($_GET['summary']) ? $_GET['summary'] : ['total', 'salary', 'department'];

// Build query
$sql = "SELECT * FROM employees WHERE status = 'active'";

if (!empty($department)) {
    $sql .= " AND department = '$department'";
}

if (!empty($date_from)) {
    $sql .= " AND hire_date >= '$date_from'";
}

if (!empty($date_to)) {
    $sql .= " AND hire_date <= '$date_to'";
}

$sql .= " ORDER BY $sort_by $sort_order";
$result = $conn->query($sql);

// Set headers
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="employee_export_' . $type . '_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Employee Export</title>
    <style>
        /* Professional Excel styles */
        .report-header { 
            background: #1E40AF; 
            color: white; 
            font-size: 18px; 
            font-weight: bold; 
            text-align: center; 
            padding: 15px;
        }
        .section-header {
            background: #2563EB;
            color: white;
            font-weight: bold;
            padding: 10px;
            font-size: 14px;
        }
        .column-header {
            background: #3B82F6;
            color: white;
            font-weight: bold;
            padding: 8px;
            text-align: center;
        }
        .data-cell {
            padding: 6px;
            border: 1px solid #CBD5E1;
        }
        .summary-cell {
            background: #F1F5F9;
            font-weight: bold;
            padding: 8px;
        }
        .total-row {
            background: #E2E8F0;
            font-weight: bold;
        }
        .currency {
            text-align: right;
        }
        .date {
            text-align: center;
        }
    </style>
</head>
<body>

<?php if($type == 'full'): ?>
    <!-- Full Employee Report -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
        <!-- Company Header -->
        <tr>
            <td colspan="12" class="report-header">
                <h1>HR DIRECTORY - COMPLETE EMPLOYEE REPORT</h1>
                <div style="font-size: 12px; margin-top: 5px;">Generated: <?php echo date('F d, Y H:i:s'); ?></div>
            </td>
        </tr>
        
        <!-- Filters Applied -->
        <?php if(!empty($department) || !empty($date_from) || !empty($date_to)): ?>
        <tr>
            <td colspan="12" style="background: #F3F4F6; padding: 8px;">
                <strong>Filters Applied:</strong> 
                <?php 
                $filters = [];
                if(!empty($department)) $filters[] = "Department: $department";
                if(!empty($date_from)) $filters[] = "From: $date_from";
                if(!empty($date_to)) $filters[] = "To: $date_to";
                echo implode(' | ', $filters);
                ?>
            </td>
        </tr>
        <?php endif; ?>
        
        <!-- Employee Data Table -->
        <tr>
            <td colspan="12" class="section-header">EMPLOYEE DETAILS</td>
        </tr>
        
        <tr>
            <?php if(in_array('personal', $fields)): ?>
                <th class="column-header">ID</th>
                <th class="column-header">First Name</th>
                <th class="column-header">Last Name</th>
            <?php endif; ?>
            
            <?php if(in_array('contact', $fields)): ?>
                <th class="column-header">Email</th>
                <th class="column-header">Phone</th>
            <?php endif; ?>
            
            <?php if(in_array('employment', $fields)): ?>
                <th class="column-header">Department</th>
                <th class="column-header">Position</th>
                <th class="column-header">Salary</th>
                <th class="column-header">Hire Date</th>
            <?php endif; ?>
            
            <?php if(in_array('emergency', $fields)): ?>
                <th class="column-header">Emergency Contact</th>
                <th class="column-header">Emergency Phone</th>
                <th class="column-header">Address</th>
            <?php endif; ?>
        </tr>
        
        <?php 
        $total_salary = 0;
        $count = 0;
        $dept_salaries = [];
        
        if ($result->num_rows > 0):
            while($row = $result->fetch_assoc()): 
                $count++;
                $total_salary += $row['salary'];
                
                // Track department salaries
                $dept = $row['department'] ?: 'Unassigned';
                if(!isset($dept_salaries[$dept])) {
                    $dept_salaries[$dept] = ['count' => 0, 'salary' => 0];
                }
                $dept_salaries[$dept]['count']++;
                $dept_salaries[$dept]['salary'] += $row['salary'];
        ?>
        <tr>
            <?php if(in_array('personal', $fields)): ?>
                <td class="data-cell"><?php echo $row['employee_id']; ?></td>
                <td class="data-cell"><?php echo $row['first_name']; ?></td>
                <td class="data-cell"><?php echo $row['last_name']; ?></td>
            <?php endif; ?>
            
            <?php if(in_array('contact', $fields)): ?>
                <td class="data-cell"><?php echo $row['email']; ?></td>
                <td class="data-cell"><?php echo $row['phone'] ?: 'N/A'; ?></td>
            <?php endif; ?>
            
            <?php if(in_array('employment', $fields)): ?>
                <td class="data-cell"><?php echo $row['department'] ?: 'N/A'; ?></td>
                <td class="data-cell"><?php echo $row['position'] ?: 'N/A'; ?></td>
                <td class="data-cell currency">$<?php echo number_format($row['salary'], 2); ?></td>
                <td class="data-cell date"><?php echo $row['hire_date'] ? date('m/d/Y', strtotime($row['hire_date'])) : 'N/A'; ?></td>
            <?php endif; ?>
            
            <?php if(in_array('emergency', $fields)): ?>
                <td class="data-cell"><?php echo $row['emergency_contact'] ?: 'N/A'; ?></td>
                <td class="data-cell"><?php echo $row['emergency_phone'] ?: 'N/A'; ?></td>
                <td class="data-cell"><?php echo $row['address'] ?: 'N/A'; ?></td>
            <?php endif; ?>
        </tr>
        <?php 
            endwhile; 
        else:
        ?>
        <tr>
            <td colspan="12" class="data-cell" style="text-align: center;">No employees found</td>
        </tr>
        <?php endif; ?>
        
        <!-- Summary Section -->
        <?php if(!empty($include_summary)): ?>
        <tr><td colspan="12">&nbsp;</td></tr>
        
        <tr>
            <td colspan="12" class="section-header">EXPORT SUMMARY</td>
        </tr>
        
        <?php if(in_array('total', $include_summary)): ?>
        <tr class="total-row">
            <td colspan="6" class="summary-cell">Total Employees:</td>
            <td colspan="6" class="summary-cell"><?php echo $count; ?></td>
        </tr>
        <?php endif; ?>
        
        <?php if(in_array('salary', $include_summary)): ?>
        <tr>
            <td colspan="6" class="summary-cell">Total Salary Budget:</td>
            <td colspan="6" class="summary-cell currency">$<?php echo number_format($total_salary, 2); ?></td>
        </tr>
        <tr>
            <td colspan="6" class="summary-cell">Average Salary:</td>
            <td colspan="6" class="summary-cell currency">$<?php echo number_format($count > 0 ? $total_salary / $count : 0, 2); ?></td>
        </tr>
        <tr>
            <td colspan="6" class="summary-cell">Minimum Salary:</td>
            <td colspan="6" class="summary-cell currency">$<?php echo number_format($result->num_rows > 0 ? min(array_column($result->fetch_all(MYSQLI_ASSOC), 'salary')) : 0, 2); ?></td>
        </tr>
        <tr>
            <td colspan="6" class="summary-cell">Maximum Salary:</td>
            <td colspan="6" class="summary-cell currency">$<?php echo number_format($result->num_rows > 0 ? max(array_column($result->fetch_all(MYSQLI_ASSOC), 'salary')) : 0, 2); ?></td>
        </tr>
        <?php endif; ?>
        
        <?php if(in_array('department', $include_summary) && !empty($dept_salaries)): ?>
        <tr><td colspan="12">&nbsp;</td></tr>
        <tr>
            <td colspan="12" class="section-header">DEPARTMENT BREAKDOWN</td>
        </tr>
        <tr>
            <th class="column-header" colspan="4">Department</th>
            <th class="column-header" colspan="4">Employee Count</th>
            <th class="column-header" colspan="4">Total Salary</th>
        </tr>
        <?php foreach($dept_salaries as $dept_name => $dept_data): ?>
        <tr>
            <td colspan="4" class="data-cell"><?php echo $dept_name; ?></td>
            <td colspan="4" class="data-cell"><?php echo $dept_data['count']; ?></td>
            <td colspan="4" class="data-cell currency">$<?php echo number_format($dept_data['salary'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <?php endif; ?>
        
        <!-- Footer -->
        <tr><td colspan="12">&nbsp;</td></tr>
        <tr>
            <td colspan="12" style="background: #F8FAFC; padding: 10px; font-size: 11px;">
                <strong>Report Information:</strong><br>
                Export Type: <?php echo ucfirst($type); ?> Report<br>
                Generated By: HR Department<br>
                Total Records: <?php echo $count; ?><br>
                Sort Order: <?php echo $sort_by . ' ' . $sort_order; ?>
            </td>
        </tr>
    </table>

<?php elseif($type == 'summary'): ?>
    <!-- Summary Only Report -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td colspan="4" class="report-header">HR SUMMARY REPORT</td>
        </tr>
        
        <tr>
            <td colspan="4" style="padding: 10px; text-align: center; background: #F1F5F9;">
                Generated: <?php echo date('F d, Y H:i:s'); ?>
            </td>
        </tr>
        
        <tr>
            <th class="column-header" colspan="2">Metric</th>
            <th class="column-header" colspan="2">Value</th>
        </tr>
        
        <tr>
            <td colspan="2" class="summary-cell">Total Employees</td>
            <td colspan="2" class="data-cell"><?php echo $stats['total']; ?></td>
        </tr>
        <tr>
            <td colspan="2" class="summary-cell">Total Departments</td>
            <td colspan="2" class="data-cell"><?php echo $stats['departments']; ?></td>
        </tr>
        <tr>
            <td colspan="2" class="summary-cell">Total Salary Budget</td>
            <td colspan="2" class="data-cell currency">$<?php echo number_format($salary_stats['total'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" class="summary-cell">Average Salary</td>
            <td colspan="2" class="data-cell currency">$<?php echo number_format($salary_stats['avg'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" class="summary-cell">Minimum Salary</td>
            <td colspan="2" class="data-cell currency">$<?php echo number_format($salary_stats['min'], 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" class="summary-cell">Maximum Salary</td>
            <td colspan="2" class="data-cell currency">$<?php echo number_format($salary_stats['max'], 2); ?></td>
        </tr>
    </table>

<?php elseif($type == 'directory'): ?>
    <!-- Phone Directory -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td colspan="4" class="report-header">EMPLOYEE PHONE DIRECTORY</td>
        </tr>
        
        <tr>
            <th class="column-header">Name</th>
            <th class="column-header">Department</th>
            <th class="column-header">Email</th>
            <th class="column-header">Phone</th>
        </tr>
        
        <?php 
        $result = $conn->query("SELECT first_name, last_name, department, email, phone FROM employees WHERE status = 'active' ORDER BY first_name");
        while($row = $result->fetch_assoc()): 
        ?>
        <tr>
            <td class="data-cell"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
            <td class="data-cell"><?php echo $row['department'] ?: 'N/A'; ?></td>
            <td class="data-cell"><?php echo $row['email']; ?></td>
            <td class="data-cell"><?php echo $row['phone'] ?: 'N/A'; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php elseif($type == 'payroll'): ?>
    <!-- Payroll Export -->
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td colspan="6" class="report-header">PAYROLL EXPORT</td>
        </tr>
        
        <tr>
            <th class="column-header">Employee ID</th>
            <th class="column-header">Name</th>
            <th class="column-header">Department</th>
            <th class="column-header">Position</th>
            <th class="column-header">Monthly Salary</th>
            <th class="column-header">Annual Salary</th>
        </tr>
        
        <?php 
        $total_monthly = 0;
        $total_annual = 0;
        $result = $conn->query("SELECT employee_id, first_name, last_name, department, position, salary FROM employees WHERE status = 'active' ORDER BY department, first_name");
        while($row = $result->fetch_assoc()): 
            $annual = $row['salary'] * 12;
            $total_monthly += $row['salary'];
            $total_annual += $annual;
        ?>
        <tr>
            <td class="data-cell"><?php echo $row['employee_id']; ?></td>
            <td class="data-cell"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
            <td class="data-cell"><?php echo $row['department'] ?: 'N/A'; ?></td>
            <td class="data-cell"><?php echo $row['position'] ?: 'N/A'; ?></td>
            <td class="data-cell currency">$<?php echo number_format($row['salary'], 2); ?></td>
            <td class="data-cell currency">$<?php echo number_format($annual, 2); ?></td>
        </tr>
        <?php endwhile; ?>
        
        <tr class="total-row">
            <td colspan="4" class="summary-cell">TOTALS</td>
            <td class="summary-cell currency">$<?php echo number_format($total_monthly, 2); ?></td>
            <td class="summary-cell currency">$<?php echo number_format($total_annual, 2); ?></td>
        </tr>
    </table>
<?php endif; ?>

</body>
</html>
<?php exit; ?>