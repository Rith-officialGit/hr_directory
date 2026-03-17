<?php
// export_advanced.php
require_once 'config.php';

// Get departments for filter
$dept_result = $conn->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
$departments = [];
while($dept = $dept_result->fetch_assoc()) {
    $departments[] = $dept['department'];
}

// Get statistics for preview
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];
$stats['departments'] = $conn->query("SELECT COUNT(DISTINCT department) as count FROM employees WHERE department IS NOT NULL AND department != ''")->fetch_assoc()['count'];
$salary_stats = $conn->query("SELECT SUM(salary) as total, AVG(salary) as avg, MIN(salary) as min, MAX(salary) as max FROM employees WHERE status = 'active'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Export - HR Directory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .export-option {
            transition: all 0.3s ease;
        }
        .export-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        }
        .format-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
                            <i class="fas fa-download mr-2 text-green-500"></i>Advanced Export
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Employees</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-building text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Departments</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['departments']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Salary</p>
                            <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($salary_stats['total'], 2); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Average Salary</p>
                            <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($salary_stats['avg'], 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Excel Export -->
                <div class="export-option bg-white rounded-xl shadow-lg overflow-hidden border-2 border-transparent hover:border-green-500">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-4">
                        <div class="flex items-center justify-between">
                            <i class="fas fa-file-excel text-white text-3xl"></i>
                            <span class="text-white text-sm font-semibold bg-green-700 px-3 py-1 rounded-full">Popular</span>
                        </div>
                        <h3 class="text-white text-xl font-bold mt-2">Excel Export</h3>
                        <p class="text-green-100 text-sm">.xlsx format with formatting</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span>Formatted tables and colors</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span>Auto-filter enabled</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span>Summary statistics</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                <span>Department breakdown</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <a href="export_xlsx_advanced.php" 
                               class="block w-full text-center bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition">
                                <i class="fas fa-download mr-2"></i>Download XLSX
                            </a>
                            <a href="export_excel_advanced.php" 
                               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition">
                                <i class="fas fa-file-excel mr-2"></i>Download XLS (Legacy)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CSV Export -->
                <div class="export-option bg-white rounded-xl shadow-lg overflow-hidden border-2 border-transparent hover:border-blue-500">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                        <div class="flex items-center justify-between">
                            <i class="fas fa-file-csv text-white text-3xl"></i>
                            <span class="text-white text-sm font-semibold bg-blue-700 px-3 py-1 rounded-full">Universal</span>
                        </div>
                        <h3 class="text-white text-xl font-bold mt-2">CSV Export</h3>
                        <p class="text-blue-100 text-sm">Comma-separated values</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                                <span>Compatible with all software</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                                <span>UTF-8 encoding</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                                <span>Small file size</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                                <span>Easy to import anywhere</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <a href="export_csv_advanced.php?format=standard" 
                               class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition">
                                <i class="fas fa-download mr-2"></i>Standard CSV
                            </a>
                            <a href="export_csv_advanced.php?format=excel" 
                               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition">
                                <i class="fas fa-file-csv mr-2"></i>Excel CSV (with BOM)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- PDF & JSON Export -->
                <div class="export-option bg-white rounded-xl shadow-lg overflow-hidden border-2 border-transparent hover:border-purple-500">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4">
                        <div class="flex items-center justify-between">
                            <i class="fas fa-file-pdf text-white text-3xl"></i>
                            <span class="text-white text-sm font-semibold bg-purple-700 px-3 py-1 rounded-full">Advanced</span>
                        </div>
                        <h3 class="text-white text-xl font-bold mt-2">PDF & JSON</h3>
                        <p class="text-purple-100 text-sm">Reports and data formats</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                <span>Printable PDF reports</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                <span>JSON for developers</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                <span>XML format available</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                <span>HTML report</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="export_pdf_advanced.php" 
                               class="text-center bg-purple-500 hover:bg-purple-600 text-white py-2 rounded-lg transition text-sm">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <a href="export_json_advanced.php" 
                               class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition text-sm">
                                <i class="fas fa-code mr-1"></i> JSON
                            </a>
                            <a href="export_xml_advanced.php" 
                               class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition text-sm">
                                <i class="fas fa-file-code mr-1"></i> XML
                            </a>
                            <a href="export_html_advanced.php" 
                               class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition text-sm">
                                <i class="fas fa-file-alt mr-1"></i> HTML
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Options Panel -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-sliders-h text-blue-500 mr-2"></i>Export Options
                </h3>
                
                <form id="exportForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Department Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Departments</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date From</label>
                            <input type="date" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date To</label>
                            <input type="date" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Include Fields -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Fields</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="fields[]" value="personal" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Personal Info</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="fields[]" value="contact" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Contact Details</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="fields[]" value="employment" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Employment</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="fields[]" value="emergency" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Emergency Contact</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Sort Options -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                            <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="first_name">First Name</option>
                                <option value="last_name">Last Name</option>
                                <option value="employee_id">Employee ID</option>
                                <option value="department">Department</option>
                                <option value="salary">Salary</option>
                                <option value="hire_date">Hire Date</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                            <select name="sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="ASC">Ascending</option>
                                <option value="DESC">Descending</option>
                            </select>
                        </div>
                        
                        <!-- Summary Options -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Summary</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="summary[]" value="total" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Total Count</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="summary[]" value="salary" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Salary Summary</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="summary[]" value="department" checked class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm">Dept Breakdown</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Quick Export Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="export_xlsx_advanced.php?type=full" 
                   class="flex items-center justify-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition border-2 border-green-200 hover:border-green-500">
                    <i class="fas fa-file-excel text-green-600 text-2xl mr-3"></i>
                    <div>
                        <div class="font-semibold">Full Report</div>
                        <div class="text-xs text-gray-500">All employees</div>
                    </div>
                </a>
                
                <a href="export_xlsx_advanced.php?type=summary" 
                   class="flex items-center justify-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition border-2 border-blue-200 hover:border-blue-500">
                    <i class="fas fa-chart-bar text-blue-600 text-2xl mr-3"></i>
                    <div>
                        <div class="font-semibold">Summary Only</div>
                        <div class="text-xs text-gray-500">Statistics & charts</div>
                    </div>
                </a>
                
                <a href="export_xlsx_advanced.php?type=directory" 
                   class="flex items-center justify-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition border-2 border-purple-200 hover:border-purple-500">
                    <i class="fas fa-address-book text-purple-600 text-2xl mr-3"></i>
                    <div>
                        <div class="font-semibold">Phone Directory</div>
                        <div class="text-xs text-gray-500">Contact list</div>
                    </div>
                </a>
                
                <a href="export_xlsx_advanced.php?type=payroll" 
                   class="flex items-center justify-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition border-2 border-yellow-200 hover:border-yellow-500">
                    <i class="fas fa-money-bill-wave text-yellow-600 text-2xl mr-3"></i>
                    <div>
                        <div class="font-semibold">Payroll Export</div>
                        <div class="text-xs text-gray-500">Salary details</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update export links with form data
        document.querySelectorAll('[href*="export_"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const form = document.getElementById('exportForm');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData).toString();
                
                if (this.href.includes('?')) {
                    this.href += '&' + params;
                } else {
                    this.href += '?' + params;
                }
            });
        });
    </script>
</body>
</html>