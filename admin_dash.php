<?php
session_start();
require_once 'connect.php'; 

// Security check - only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee' || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all employees for the list
$employees = [];
$result = $connection->query("
    SELECT u.user_id, u.username, u.email, e.role, e.employee_number, e.hire_date 
    FROM users u
    JOIN employees e ON u.user_id = e.user_id
    WHERE u.user_type = 'employee'
    ORDER BY e.hire_date DESC
");
if ($result) {
    $employees = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_employee'])) {
        // Create new employee
        $username = $connection->real_escape_string($_POST['username']);
        $email = $connection->real_escape_string($_POST['email']);
        $first_name = $connection->real_escape_string($_POST['first_name']);
        $last_name = $connection->real_escape_string($_POST['last_name']);
        $role = $connection->real_escape_string($_POST['role']);
        $employee_number = $connection->real_escape_string($_POST['employee_number']);

        // Start transaction
        $connection->begin_transaction();

        try {
            // First insert to get the user_id
            $stmt = $connection->prepare("INSERT INTO users (username, email, user_type) VALUES (?, ?, 'employee')");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $user_id = $connection->insert_id;
            
            // Use the user_id as the temporary password
            $hashed_password = password_hash($user_id, PASSWORD_DEFAULT);
            
            // Update the user record with the hashed password
            $stmt = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();

            // Insert into employees table
            $stmt = $connection->prepare("INSERT INTO employees (user_id, role, employee_number, hire_date) VALUES (?, ?, ?, CURDATE())");
            $stmt->bind_param("iss", $user_id, $role, $employee_number);
            $stmt->execute();

            $connection->commit();
            $success_message = "Employee created successfully! Temporary password: $user_id";
            
            // Refresh employee list after creation
            $result = $connection->query("
                SELECT u.user_id, u.username, u.email, e.role, e.employee_number, e.hire_date 
                FROM users u
                JOIN employees e ON u.user_id = e.user_id
                WHERE u.user_type = 'employee'
                ORDER BY e.hire_date DESC
            ");
            if ($result) {
                $employees = $result->fetch_all(MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            $connection->rollback();
            $error_message = "Error creating employee: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Trip Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-color: #d32f2f;
            --light-bg: #f9f9f9;
            --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            border-left: 5px solid var(--admin-color);
        }
        
        .admin-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav {
            display: flex;
            gap: 1rem;
        }
        
        .admin-nav a {
            color: var(--admin-color);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(211, 47, 47, 0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
        }
        
        .card h2 {
            color: var(--admin-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        button {
            background-color: var(--admin-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #b71c1c;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Trip Pilot Admin Dashboard</h1>
        <nav class="admin-nav">
            <a href="admin_dash.php">Dashboard</a>
            <a href="manage_buses.php">Manage Buses</a>
            <a href="manage_routes.php">Manage Routes</a>
            <a href="index.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="dashboard-cards">
            <div class="card">
                <h2>Create New Employee</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="driver">Driver</option>
                            <option value="conductor">Conductor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="employee_number">Employee ID Number</label>
                        <input type="text" id="employee_number" name="employee_number" required>
                    </div>
                    
                    <button type="submit" name="create_employee">Create Employee</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Quick Stats</h2>
                <p>Total Employees: <?= count($employees) ?></p>
                <p>Admins: <?= array_reduce($employees, fn($carry, $e) => $e['role'] === 'admin' ? $carry + 1 : $carry, 0) ?></p>
                <p>Drivers: <?= array_reduce($employees, fn($carry, $e) => $e['role'] === 'driver' ? $carry + 1 : $carry, 0) ?></p>
                <p>Conductors: <?= array_reduce($employees, fn($carry, $e) => $e['role'] === 'conductor' ? $carry + 1 : $carry, 0) ?></p>
            </div>
        </div>
        
        <div class="card">
            <h2>Employee List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Hire Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['employee_number']) ?></td>
                            <td><?= htmlspecialchars($employee['username']) ?></td>
                            <td><?= htmlspecialchars($employee['email']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($employee['role'])) ?></td>
                            <td><?= date('M d, Y', strtotime($employee['hire_date'])) ?></td>
                            <td>
                                <a href="edit_employee.php?id=<?= $employee['user_id'] ?>" style="color: var(--admin-color);">Edit</a>
                                <a href="reset_password.php?id=<?= $employee['user_id'] ?>" style="margin-left: 1rem;">Reset Password</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>