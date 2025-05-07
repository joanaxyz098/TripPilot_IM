<?php
session_start();
require_once 'connect.php';

// Security check - only employees can access (but not admins)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee' || $_SESSION['role'] === 'admin') {
    header("Location: login.php");
    exit();
}

// Get employee details
$employee_id = $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT e.*, u.email FROM employees e JOIN users u ON e.user_id = u.user_id WHERE e.user_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Get assigned routes/schedules (example query - modify as needed)
// $schedules = [];
// $result = $connection->query("
//     SELECT r.route_name, s.departure_time, s.arrival_time, s.status 
//     FROM schedules s
//     JOIN routes r ON s.route_id = r.route_id
//     WHERE s.employee_id = $employee_id
//     ORDER BY s.departure_time
// ");
// if ($result) {
//     $schedules = $result->fetch_all(MYSQLI_ASSOC);
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Trip Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --employee-color: #1976d2;
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
            border-left: 5px solid var(--employee-color);
        }
        
        .employee-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .employee-nav {
            display: flex;
            gap: 1rem;
        }
        
        .employee-nav a {
            color: var(--employee-color);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .employee-nav a:hover {
            background-color: rgba(25, 118, 210, 0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .profile-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .profile-info h2 {
            color: var(--employee-color);
            margin-bottom: 0.5rem;
        }
        
        .profile-info p {
            margin-bottom: 0.5rem;
            color: #555;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .badge-driver {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .badge-conductor {
            background-color: #e8f5e9;
            color: #2e7d32;
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
        
        .status-pending {
            color: #ff9800;
        }
        
        .status-active {
            color: #4caf50;
        }
        
        .status-completed {
            color: #9e9e9e;
        }
        
        .action-button {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .action-button.primary {
            background-color: var(--employee-color);
            color: white;
        }
        
        .action-button.primary:hover {
            background-color: #1565c0;
        }
    </style>
</head>
<body>
    <header class="employee-header">
        <h1>Trip Pilot Employee Portal</h1>
        <nav class="employee-nav">
            <a href="employee_dash.php">Dashboard</a>
            <a href="my_schedules.php">My Schedules</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <div class="profile-card">
            <div class="profile-avatar">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($employee['first_name'] ?? '') ?> <?= htmlspecialchars($employee['last_name'] ?? '') ?></h2>
                <p>Employee ID: <?= htmlspecialchars($employee['employee_number']) ?></p>
                <p>Email: <?= htmlspecialchars($employee['email']) ?></p>
                <span class="badge badge-<?= $employee['role'] ?>"><?= ucfirst($employee['role']) ?></span>
            </div>
        </div>

        <div class="card">
            <h2>Today's Schedule</h2>
            
        </div>
    </div>
</body>
</html>