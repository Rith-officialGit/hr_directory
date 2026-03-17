<?php
// create.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $employee_id = sanitize($_POST['employee_id']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $department = sanitize($_POST['department']);
    $position = sanitize($_POST['position']);
    $salary = sanitize($_POST['salary']);
    $hire_date = sanitize($_POST['hire_date']);
    $address = sanitize($_POST['address']);
    $emergency_contact = sanitize($_POST['emergency_contact']);
    $emergency_phone = sanitize($_POST['emergency_phone']);

    // Validate inputs
    $errors = [];
    
    if (empty($employee_id)) $errors[] = "Employee ID is required";
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    // Check if employee_id or email already exists
    $check_query = "SELECT id FROM employees WHERE employee_id = '$employee_id' OR email = '$email'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Employee ID or Email already exists";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO employees (employee_id, first_name, last_name, email, phone, department, 
                position, salary, hire_date, address, emergency_contact, emergency_phone) 
                VALUES ('$employee_id', '$first_name', '$last_name', '$email', '$phone', '$department', 
                '$position', '$salary', '$hire_date', '$address', '$emergency_contact', '$emergency_phone')";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Employee added successfully!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>
<!-- After successful save, show export option -->
<?php if(isset($_SESSION['success'])): ?>
<div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center animate-bounce">
                <i class="fas fa-check text-white"></i>
            </div>
            <div>
                <h4 class="font-semibold text-green-800">Employee Added Successfully!</h4>
                <p class="text-sm text-green-600">Would you like to export the updated directory?</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="export_excel.php" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm transition">
                <i class="fas fa-file-excel mr-1"></i>Export Excel
            </a>
            <a href="index.php" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm transition">
                <i class="fas fa-arrow-right mr-1"></i>Continue
            </a>
        </div>
    </div>
</div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee - HR Directory</title>
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
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">
                            <i class="fas fa-user-plus text-green-500 mr-2"></i>Add New Employee
                        </h2>

                        <?php displayMessage(); ?>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Employee ID -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Employee ID <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="employee_id" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="first_name" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="last_name" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone
                                    </label>
                                    <input type="tel" name="phone"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Department -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Department
                                    </label>
                                    <select name="department"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Department</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Sales">Sales</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Human Resources">Human Resources</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Operations">Operations</option>
                                        <option value="IT">IT</option>
                                        <option value="Customer Support">Customer Support</option>
                                    </select>
                                </div>

                                <!-- Position -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Position
                                    </label>
                                    <input type="text" name="position"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Salary -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Salary
                                    </label>
                                    <input type="number" step="0.01" name="salary"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Hire Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Hire Date
                                    </label>
                                    <input type="date" name="hire_date"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                               

                                <!-- Emergency Contact Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Emergency Phone
                                    </label>
                                    <input type="tel" name="emergency_phone"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Address (Full width) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Address
                                    </label>
                                    <textarea name="address" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                                <a href="index.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-300">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">
                                    <i class="fas fa-save mr-2"></i>Save Employee
                                </button>

                                <!-- After successful save, show export option -->
<?php if(isset($_SESSION['success'])): ?>
<div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center animate-bounce">
                <i class="fas fa-check text-white"></i>
            </div>
            <div>
                <h4 class="font-semibold text-green-800">Employee Added Successfully!</h4>
                <p class="text-sm text-green-600">Would you like to export the updated directory?</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="export_excel.php" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm transition">
                <i class="fas fa-file-excel mr-1"></i>Export Excel
            </a>
            <a href="index.php" class="px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm transition">
                <i class="fas fa-arrow-right mr-1"></i>Continue
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>