<?php
// edit.php
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $department = sanitize($_POST['department']);
    $position = sanitize($_POST['position']);
    $salary = sanitize($_POST['salary']);
    $address = sanitize($_POST['address']);
    $emergency_contact = sanitize($_POST['emergency_contact']);
    $emergency_phone = sanitize($_POST['emergency_phone']);

    // Validate inputs
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    // Check if email already exists for another employee
    $check_query = "SELECT id FROM employees WHERE email = '$email' AND id != $id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Email already exists for another employee";
    }

    if (empty($errors)) {
        $sql = "UPDATE employees SET 
                first_name = '$first_name',
                last_name = '$last_name',
                email = '$email',
                phone = '$phone',
                department = '$department',
                position = '$position',
                salary = '$salary',
                address = '$address',
                emergency_contact = '$emergency_contact',
                emergency_phone = '$emergency_phone'
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Employee updated successfully!";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - HR Directory</title>
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
                            <i class="fas fa-edit text-yellow-500 mr-2"></i>Edit Employee: <?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>
                        </h2>

                        <?php displayMessage(); ?>

                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Employee ID (Read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Employee ID
                                    </label>
                                    <input type="text" value="<?php echo $employee['employee_id']; ?>" readonly disabled
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                </div>

                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="first_name" required
                                           value="<?php echo htmlspecialchars($employee['first_name']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="last_name" required
                                           value="<?php echo htmlspecialchars($employee['last_name']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" required
                                           value="<?php echo htmlspecialchars($employee['email']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone
                                    </label>
                                    <input type="tel" name="phone"
                                           value="<?php echo htmlspecialchars($employee['phone']); ?>"
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
                                        <?php
                                        $departments = ['Engineering', 'Sales', 'Marketing', 'Human Resources', 'Finance', 'Operations', 'IT', 'Customer Support'];
                                        foreach($departments as $dept): ?>
                                            <option value="<?php echo $dept; ?>" <?php echo $employee['department'] == $dept ? 'selected' : ''; ?>>
                                                <?php echo $dept; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Position -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Position
                                    </label>
                                    <input type="text" name="position"
                                           value="<?php echo htmlspecialchars($employee['position']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Salary -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Salary
                                    </label>
                                    <input type="number" step="0.01" name="salary"
                                           value="<?php echo $employee['salary']; ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Emergency Contact Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Emergency Contact
                                    </label>
                                    <input type="text" name="emergency_contact"
                                           value="<?php echo htmlspecialchars($employee['emergency_contact']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Emergency Contact Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Emergency Phone
                                    </label>
                                    <input type="tel" name="emergency_phone"
                                           value="<?php echo htmlspecialchars($employee['emergency_phone']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Address (Full width) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Address
                                    </label>
                                    <textarea name="address" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                                <a href="index.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-300">
                                    Cancel
                                </a>
                                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition duration-300">
                                    <i class="fas fa-save mr-2"></i>Update Employee
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>