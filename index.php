<?php
// index.php
require_once 'config.php';

// Handle search
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

// Get unique departments for filter
$dept_query = "SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department";
$dept_result = $conn->query($dept_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Directory - Employee Management</title>
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
                            <i class="fas fa-users mr-2 text-blue-500"></i>HR Directory
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                            <i class="fas fa-plus mr-1"></i>Add Employee
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                                <div class="flex">
                                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" 
                                           placeholder="Search employees..."
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search By</label>
                                <select name="search_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>Name</option>
                                    <option value="department" <?php echo $search_type == 'department' ? 'selected' : ''; ?>>Department</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Department</label>
                                <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Departments</option>
                                    <?php while($dept = $dept_result->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['department']; ?>" <?php echo $department_filter == $dept['department'] ? 'selected' : ''; ?>>
                                            <?php echo $dept['department']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <?php if(!empty($search_term) || !empty($department_filter)): ?>
                            <div class="flex justify-end">
                                <a href="index.php" class="text-sm text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-times mr-1"></i>Clear Filters
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Employees Table -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Employee List (<?php echo $result->num_rows; ?>)
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo $row['employee_id']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo $row['email']; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <?php echo $row['department'] ?: 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $row['position'] ?: 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo $row['phone'] ?: 'N/A'; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo $row['email']; ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $row['salary'] ? '$' . number_format($row['salary'], 2) : 'N/A'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $row['id']; ?>" 
                                                       onclick="return confirm('Are you sure you want to offboard this employee?')" 
                                                       class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No employees found. <a href="create.php" class="text-blue-500 hover:text-blue-700">Add your first employee</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                                <!-- In your navigation bar, replace with this advanced export button -->
<div class="flex items-center space-x-4">
    <!-- Advanced Export Button -->
    <a href="export_advanced.php" 
       class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-5 py-2 rounded-md text-sm font-medium transition duration-300 flex items-center shadow-lg hover:shadow-xl transform hover:scale-105">
        <i class="fas fa-chart-bar mr-2"></i>
        <span>Advanced Export</span>
        <i class="fas fa-chevron-right ml-2 text-xs"></i>
    </a>
    
    <!-- Add Employee Button -->
    <a href="create.php" 
       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 flex items-center">
        <i class="fas fa-plus mr-2"></i>
        Add Employee
    </a>
</div>
<!-- In your navigation bar, add attendance link -->
<div class="flex items-center space-x-4">
    <a href="attendance.php" 
       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 flex items-center">
        <i class="fas fa-clock mr-2"></i>
        Attendance
    </a>



                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!-- RithDev Credit Popup -->
<div id="rithdevCredit" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Popup Header -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-t-2xl p-6 text-center">
            <div class="w-24 h-24 mx-auto bg-white rounded-full p-1 mb-4">
                <div class="w-full h-full bg-gradient-to-r from-purple-600 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-code text-white text-4xl"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Created by RithDev</h2>
            <p class="text-purple-100">Full Stack Developer & UI/UX Designer</p>
        </div>
        
        <!-- Popup Body -->
        <div class="p-6">
            <div class="flex items-center justify-center space-x-4 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-800">5+</div>
                    <div class="text-sm text-gray-500">Years Experience</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-800">50+</div>
                    <div class="text-sm text-gray-500">Projects Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-800">100%</div>
                    <div class="text-sm text-gray-500">Client Satisfaction</div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="flex justify-center space-x-4 mb-6">
                <a href="https://github.com/Rith-officialGit" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-purple-600 hover:text-white transition-all duration-300">
                    <i class="fab fa-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/nuth-varith-678212376/" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-purple-600 hover:text-white transition-all duration-300">
                    <i class="fab fa-linkedin-in"></i>
    
                <a href="https://www.facebook.com/varithhh/" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-purple-600 hover:text-white transition-all duration-300">
                    <i class="fab fa-facebook"></i>
                </a>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-6">
                <p class="text-gray-600">
                    <i class="fas fa-quote-left text-purple-500 mr-1"></i>
                    Crafting beautiful and functional web experiences with passion and precision.
                    <i class="fas fa-quote-right text-purple-500 ml-1"></i>
                </p>
            </div>
            
            <!-- Tech Stack -->
            <div class="flex flex-wrap justify-center gap-2 mb-6">
                <span class="px-3 py-1 bg-purple-100 text-purple-600 rounded-full text-sm font-medium">PHP</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium">Laravel</span>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-full text-sm font-medium">JavaScript</span>
                <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-sm font-medium">React</span>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-sm font-medium">Vue.js</span>
                <span class="px-3 py-1 bg-pink-100 text-pink-600 rounded-full text-sm font-medium">Tailwind</span>
                <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-sm font-medium">MySQL</span>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button onclick="closeCreditPopup()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-300 font-medium">
                    Close
                </button>
                <a href="#" class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 transition duration-300 font-medium text-center">
                    <i class="fas fa-envelope mr-2"></i>Hire Me
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-3 bg-gray-50 rounded-b-2xl text-center text-sm text-gray-500">
            © 2024 RithDev - All rights reserved
        </div>
    </div>
</div>

<!-- Floating Credit Button (always visible) -->
<div class="fixed bottom-4 right-4 z-40">
    <button onclick="showCreditPopup()" class="group relative">
        <!-- Tooltip -->
        <span class="absolute bottom-full right-0 mb-2 hidden group-hover:block bg-gray-900 text-white text-sm px-3 py-1 rounded-lg whitespace-nowrap">
            Created by RithDev
            <span class="absolute top-full right-4 -mt-1 border-4 border-transparent border-t-gray-900"></span>
        </span>
        
        <!-- Button -->
        <div class="w-12 h-12 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300">
            <i class="fas fa-code text-xl"></i>
        </div>
    </button>
</div>

<script>
// Show popup on page load with delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.getElementById('rithdevCredit').classList.remove('hidden');
        document.getElementById('rithdevCredit').classList.add('flex');
    }, 1000); // Shows after 1 second
});

// Close popup function
function closeCreditPopup() {
    const popup = document.getElementById('rithdevCredit');
    popup.classList.add('opacity-0');
    setTimeout(function() {
        popup.classList.add('hidden');
        popup.classList.remove('flex', 'opacity-0');
    }, 300);
}

// Show popup function (for floating button)
function showCreditPopup() {
    const popup = document.getElementById('rithdevCredit');
    popup.classList.remove('hidden');
    popup.classList.add('flex');
    setTimeout(function() {
        popup.classList.remove('opacity-0');
    }, 10);
}

// Close when clicking outside
document.getElementById('rithdevCredit').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreditPopup();
    }
});

// Close with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreditPopup();
    }
});
</script>

<!-- Add Font Awesome if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">