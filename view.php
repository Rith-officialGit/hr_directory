<?php
// view.php
require_once 'config.php';

// Get employee ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch employee data
$sql = "SELECT * FROM employees WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Employee not found!";
    header("Location: index.php");
    exit();
}

$employee = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee - HR Directory</title>
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
                        <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-user text-blue-500 mr-2"></i>Employee Details
                            </h2>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo $employee['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($employee['status']); ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Information -->
                            <div class="col-span-2">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Personal Information</h3>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Employee ID</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['employee_id']; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['email']; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['phone'] ?: 'N/A'; ?></p>
                            </div>

                            <!-- Employment Information -->
                            <div class="col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Employment Information</h3>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Department</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['department'] ?: 'N/A'; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Position</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['position'] ?: 'N/A'; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Salary</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['salary'] ? '$' . number_format($employee['salary'], 2) : 'N/A'; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Hire Date</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['hire_date'] ? date('F d, Y', strtotime($employee['hire_date'])) : 'N/A'; ?></p>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Emergency Contact</h3>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Contact Name</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['emergency_contact'] ?: 'N/A'; ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Emergency Phone</label>
                                <p class="mt-1 text-lg text-gray-900"><?php echo $employee['emergency_phone'] ?: 'N/A'; ?></p>
                            </div>

                            <!-- Address -->
                            <div class="col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Address</h3>
                                <p class="text-gray-900"><?php echo nl2br($employee['address'] ?: 'N/A'); ?></p>
                            </div>

                            <!-- System Information -->
                            <div class="col-span-2 mt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">System Information</h3>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created At</label>
                                <p class="mt-1 text-gray-900"><?php echo date('F d, Y H:i:s', strtotime($employee['created_at'])); ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                                <p class="mt-1 text-gray-900"><?php echo date('F d, Y H:i:s', strtotime($employee['updated_at'])); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4 pt-6 mt-6 border-t">
                            <a href="index.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-300">
                                <i class="fas fa-arrow-left mr-2"></i>Back to List
                            </a>
                            <a href="edit.php?id=<?php echo $employee['id']; ?>" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition duration-300">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>