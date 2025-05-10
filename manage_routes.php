<?php
session_start();
require_once 'connect.php';

// Security check - only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee' || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if (isset($_POST['add_route'])) {
    $origin = $connection->real_escape_string($_POST['origin']);
    $destination = $connection->real_escape_string($_POST['destination']);
    $distance = $connection->real_escape_string($_POST['distance']);
    $duration = $connection->real_escape_string($_POST['duration']);
    $mode = $connection->real_escape_string($_POST['mode']);
    
    // Check if route already exists
    $check_query = "SELECT * FROM routes WHERE origin = '$origin' AND destination = '$destination'";
    $check_result = $connection->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error_message = "Route from $origin to $destination already exists!";
    } else {
        $query = "INSERT INTO routes (origin, destination, distance, estimated_duration, mode) 
                  VALUES ('$origin', '$destination', '$distance', '$duration', '$mode')";
        
        if ($connection->query($query)) {
            $success_message = "New route added successfully!";
        } else {
            $error_message = "Error adding route: " . $connection->error;
        }
    }
}

// Assign bus to route (create trip)
if (isset($_POST['assign_bus'])) {
    $route_id = $connection->real_escape_string($_POST['route_id']);
    $company_id = $connection->real_escape_string($_POST['company_id']);
    $departure_time = $connection->real_escape_string($_POST['departure_time']);
    $arrival_time = $connection->real_escape_string($_POST['arrival_time']);
    $price = $connection->real_escape_string($_POST['price']);
    $seats = $connection->real_escape_string($_POST['seats']);
    $status = $connection->real_escape_string($_POST['status']);
    
    $query = "INSERT INTO trips (route_id, bus_company_id, departure_time, arrival_time, price, available_seats, status) 
              VALUES ('$route_id', '$company_id', '$departure_time', '$arrival_time', '$price', '$seats', '$status')";
    
    if ($connection->query($query)) {
        $success_message = "Bus assigned to route successfully!";
    } else {
        $error_message = "Error assigning bus: " . $connection->error;
    }
}

// Create a new table for bus-driver assignments if it doesn't already exist
// Since we're instructed not to create new tables in the SQL database, we'll adapt
// our interface to work with the existing tables and add relevant functionality.

// Fetch all bus companies
$companies = [];
$company_query = "SELECT * FROM bus_companies ORDER BY name";
$company_result = $connection->query($company_query);
if ($company_result) {
    while ($row = $company_result->fetch_assoc()) {
        $companies[] = $row;
    }
}

// Fetch all drivers
$drivers = [];
$driver_query = "SELECT e.employee_id, u.username, CONCAT(e.employee_number, ' - ', u.username) as driver_name 
                FROM employees e 
                JOIN users u ON e.user_id = u.user_id 
                WHERE e.role = 'driver'";
$driver_result = $connection->query($driver_query);
if ($driver_result) {
    while ($row = $driver_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Fetch all routes with relevant info
$routes_query = "SELECT r.*, 
                (SELECT COUNT(*) FROM trips t WHERE t.route_id = r.route_id) as trip_count
                FROM routes r
                ORDER BY r.origin, r.destination";
$routes_result = $connection->query($routes_query);
$routes = [];
if ($routes_result) {
    while ($row = $routes_result->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Group routes by terminal
$terminal_routes = [];
foreach ($routes as $route) {
    $terminal = '';
    
    // Determine terminal based on origin
    if (strpos($route['origin'], 'South Bus Terminal') !== false) {
        $terminal = 'South Bus Terminal';
    } elseif (strpos($route['origin'], 'North Bus Terminal') !== false) {
        $terminal = 'North Bus Terminal';
    } else {
        $terminal = 'Other Terminals';
    }
    
    if (!isset($terminal_routes[$terminal])) {
        $terminal_routes[$terminal] = [];
    }
    
    $terminal_routes[$terminal][] = $route;
}

// Get trips for each route
$route_trips = [];
foreach ($routes as $route) {
    $route_id = $route['route_id'];
    $trips_query = "SELECT t.*, bc.name as company_name
                    FROM trips t
                    JOIN bus_companies bc ON t.bus_company_id = bc.company_id
                    WHERE t.route_id = $route_id
                    ORDER BY t.departure_time";
    $trips_result = $connection->query($trips_query);
    
    $route_trips[$route_id] = [];
    if ($trips_result) {
        while ($row = $trips_result->fetch_assoc()) {
            $route_trips[$route_id][] = $row;
        }
    }
}

// Terminal addresses (hardcoded for simplicity)
$terminal_addresses = [
    'South Bus Terminal' => 'N. Bacalso Avenue, Cebu City',
    'North Bus Terminal' => 'Mandaue City, Cebu'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes | TripPilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-color: #d32f2f;
            --light-bg: #f9f9f9;
            --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #475569;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
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
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(211, 47, 47, 0.1);
        }

        .admin-title {
            font-size: 2.3rem;
            margin: 0;
            font-weight: 600;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--primary);
        }

        .terminal-title {
            margin-top: 32px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 600;
        }

        .route-card {
            border-top: 5px solid var(--primary);
        }

        .route-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .route-header h5 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .route-header h5 i {
            color: var(--primary);
        }

        .terminal-address {
            display: inline-block;
            padding: 6px 12px;
            background-color: #e0f2fe;
            color: #0369a1;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .terminal-address i {
            margin-right: 5px;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .route-mode {
            font-size: 0.8rem;
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 8px;
            font-weight: 500;
        }

        .mode-regular {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .mode-express {
            background-color: #dcfce7;
            color: #166534;
        }

        .mode-premium {
            background-color: #fef3c7;
            color: #92400e;
        }

        .route-details {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .route-info {
            display: flex;
            font-size: 0.9rem;
            color: var(--secondary);
            margin-right: 16px;
        }

        .route-info i {
            margin-right: 5px;
        }

        .table th {
            font-weight: 600;
            color: var(--secondary);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .terminal-tag {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .south-terminal {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .north-terminal {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .other-terminal {
            background-color: #f3f4f6;
            color: #374151;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Terminal filter */
        .terminal-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .filter-btn-all {
            background-color: #f3f4f6;
            color: #374151;
        }

        .filter-btn-north {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .filter-btn-south {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Trip card for small screens */
        .trip-card {
            display: none;
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .trip-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .trip-company {
            font-weight: 600;
        }
        
        .trip-time {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .trip-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .trip-label {
            color: var(--secondary);
            font-weight: 500;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .table-responsive {
                display: none;
            }
            
            .trip-card {
                display: block;
            }
            
            .terminal-filter {
                flex-wrap: wrap;
            }
            
            .terminal-tag {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header class="admin-header">
        <h1 class="admin-title">Trip Pilot Manage Route</h1>
        <nav class="admin-nav">
            <a href="admin_dash.php">Dashboard</a>
            <a href="manage_buses.php">Manage Buses</a>
            <a href="manage_routes.php">Manage Routes</a>
            <a href="index.php">Logout</a>
        </nav>
    </header>

    <div class="container py-4">
        <!-- Alerts for success/error messages -->
        <?php if(isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Route Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title"><i class="fas fa-plus-circle"></i> Add New Route</h2>
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="origin" class="form-label">Origin</label>
                                    <select class="form-select" id="origin" name="origin" required>
                                        <option value="">Select Origin Terminal</option>
                                        <option value="Cebu City South Bus Terminal">Cebu City South Bus Terminal</option>
                                        <option value="Cebu City North Bus Terminal">Cebu City North Bus Terminal</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="destination" class="form-label">Destination</label>
                                    <input type="text" class="form-control" id="destination" name="destination" placeholder="e.g., Carcar City" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="distance" class="form-label">Distance (km)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="distance" name="distance" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="duration" class="form-label">Est. Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 2h 30m" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="mode" class="form-label">Mode</label>
                                    <select class="form-select" id="mode" name="mode" required>
                                        <option value="Regular">Regular</option>
                                        <option value="Express">Express</option>
                                        <option value="Premium">Premium</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="add_route" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Route
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter by Terminal -->
        <div class="terminal-filter">
            <button class="filter-btn filter-btn-all active" data-terminal="all">
                <i class="fas fa-globe"></i> All Terminals
            </button>
            <button class="filter-btn filter-btn-south" data-terminal="South Bus Terminal">
                <i class="fas fa-map-marker-alt"></i> South Bus Terminal
            </button>
            <button class="filter-btn filter-btn-north" data-terminal="North Bus Terminal">
                <i class="fas fa-map-marker-alt"></i> North Bus Terminal
            </button>
        </div>

        <!-- Routes Section -->
        <h2 class="section-title"><i class="fas fa-route"></i> Available Routes</h2>
        
        <?php if(empty($terminal_routes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No routes available. Add a new route to get started.
            </div>
        <?php else: ?>
            <?php foreach($terminal_routes as $terminal => $terminal_route_list): ?>
                <div class="terminal-section" data-terminal="<?php echo $terminal; ?>">
                    <h3 class="terminal-title">
                        <i class="fas fa-building"></i> <?php echo $terminal; ?>
                    </h3>
                    
                    <?php if(isset($terminal_addresses[$terminal])): ?>
                        <div class="terminal-address">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $terminal_addresses[$terminal]; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <?php foreach($terminal_route_list as $route): ?>
                            <div class="col-lg-6 mb-4 fade-in">
                                <div class="card route-card">
                                    <div class="card-header route-header">
                                        <h5>
                                            <i class="fas fa-route"></i> <?php echo htmlspecialchars($route['destination']); ?>
                                            <?php
                                                $mode_class = '';
                                                switch($route['mode']) {
                                                    case 'Regular':
                                                        $mode_class = 'mode-regular';
                                                        break;
                                                    case 'Express':
                                                        $mode_class = 'mode-express';
                                                        break;
                                                    case 'Premium':
                                                        $mode_class = 'mode-premium';
                                                        break;
                                                }
                                            ?>
                                            <span class="route-mode <?php echo $mode_class; ?>"><?php echo $route['mode']; ?></span>
                                        </h5>
                                        <!-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignBusModal<?php echo $route['route_id']; ?>">
                                            <i class="fas fa-bus me-2"></i>Assign Bus
                                        </button> -->
                                    </div>
                                    <div class="card-body">
                                        <div class="route-details">
                                            <span class="route-info">
                                                <i class="fas fa-road"></i> <?php echo number_format($route['distance'], 2); ?> km
                                            </span>
                                            <span class="route-info">
                                                <i class="fas fa-clock"></i> <?php echo $route['estimated_duration']; ?>
                                            </span>
                                            <span class="route-info">
                                                <i class="fas fa-bus"></i> <?php echo $route['trip_count']; ?> trips
                                            </span>
                                        </div>
                                        
                                        <?php if(isset($route_trips[$route['route_id']]) && !empty($route_trips[$route['route_id']])): ?>
                                            <h6 class="mt-3 mb-2">Assigned Buses</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Company</th>
                                                            <th>Departure</th>
                                                            <th>Arrival</th>
                                                            <th>Price</th>
                                                            <th>Seats</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach($route_trips[$route['route_id']] as $trip): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($trip['company_name']); ?></td>
                                                                <td><?php echo date("h:i A", strtotime($trip['departure_time'])); ?></td>
                                                                <td><?php echo date("h:i A", strtotime($trip['arrival_time'])); ?></td>
                                                                <td>₱<?php echo number_format($trip['price'], 2); ?></td>
                                                                <td><?php echo $trip['available_seats']; ?></td>
                                                                <td>
                                                                    <?php 
                                                                        $status_text = '';
                                                                        switch($trip['status']) {
                                                                            case 'active':
                                                                                $status_text = '<span class="badge bg-success">Available</span>';
                                                                                break;
                                                                            case 'inactive':
                                                                                $status_text = '<span class="badge bg-warning">Travelling</span>';
                                                                                break;
                                                                            case 'cancelled':
                                                                                $status_text = '<span class="badge bg-danger">Unavailable</span>';
                                                                                break;
                                                                        }
                                                                        echo $status_text;
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Mobile view of trips (visible on small screens) -->
                                            <?php foreach($route_trips[$route['route_id']] as $trip): ?>
                                                <div class="trip-card">
                                                    <div class="trip-card-header">
                                                        <span class="trip-company"><?php echo htmlspecialchars($trip['company_name']); ?></span>
                                                        <?php 
                                                            $status_text = '';
                                                            $status_class = '';
                                                            switch($trip['status']) {
                                                                case 'active':
                                                                    $status_text = 'Available';
                                                                    $status_class = 'bg-success';
                                                                    break;
                                                                case 'inactive':
                                                                    $status_text = 'Travelling';
                                                                    $status_class = 'bg-warning';
                                                                    break;
                                                                case 'cancelled':
                                                                    $status_text = 'Unavailable';
                                                                    $status_class = 'bg-danger';
                                                                    break;
                                                            }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                    </div>
                                                    <div class="trip-time">
                                                        <span class="me-2"><?php echo date("h:i A", strtotime($trip['departure_time'])); ?></span>
                                                        <i class="fas fa-arrow-right mx-2"></i>
                                                        <span class="ms-2"><?php echo date("h:i A", strtotime($trip['arrival_time'])); ?></span>
                                                    </div>
                                                    <div class="trip-detail">
                                                        <span class="trip-label">Price:</span>
                                                        <span>₱<?php echo number_format($trip['price'], 2); ?></span>
                                                    </div>
                                                    <div class="trip-detail">
                                                        <span class="trip-label">Available Seats:</span>
                                                        <span><?php echo $trip['available_seats']; ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-exclamation-triangle me-2"></i> No buses assigned to this route yet.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Assign Bus Modal -->
                                <div class="modal fade" id="assignBusModal<?php echo $route['route_id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Assign Bus to <?php echo htmlspecialchars($route['origin'] . ' to ' . $route['destination']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="POST">
                                                    <input type="hidden" name="route_id" value="<?php echo $route['route_id']; ?>">
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="company_id<?php echo $route['route_id']; ?>" class="form-label">Bus Company</label>
                                                            <select class="form-select" id="company_id<?php echo $route['route_id']; ?>" name="company_id" required>
                                                                <option value="">Select Bus Company</option>
                                                                <?php foreach($companies as $company): ?>
                                                                    <option value="<?php echo $company['company_id']; ?>">
                                                                        <?php echo htmlspecialchars($company['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label for="departure_time<?php echo $route['route_id']; ?>" class="form-label">Departure Time</label>
                                                            <input type="time" class="form-control" id="departure_time<?php echo $route['route_id']; ?>" name="departure_time" required>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label for="arrival_time<?php echo $route['route_id']; ?>" class="form-label">Arrival Time</label>
                                                            <input type="time" class="form-control" id="arrival_time<?php echo $route['route_id']; ?>" name="arrival_time" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="price<?php echo $route['route_id']; ?>" class="form-label">Price (PHP)</label>
                                                            <input type="number" step="0.01" min="0" class="form-control" id="price<?php echo $route['route_id']; ?>" name="price" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="seats<?php echo $route['route_id']; ?>" class="form-label">Available Seats</label>
                                                            <input type="number" min="1" class="form-control" id="seats<?php echo $route['route_id']; ?>" name="seats" required>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="status<?php echo $route['route_id']; ?>" class="form-label">Status</label>
                                                            <select class="form-select" id="status<?php echo $route['route_id']; ?>" name="status" required>
                                                                <option value="active">Available</option>
                                                                <option value="inactive">Travelling</option>
                                                                <option value="cancelled">Unavailable</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <h6>Driver Assignment Instructions</h6>
                                                        <p class="text-muted">
                                                            To assign a driver to this route, you need to create an employee with the "driver" role first in the admin dashboard, then you can select from available drivers below.
                                                        </p>
                                                        
                                                        <?php if(!empty($drivers)): ?>
                                                            <div class="mb-3">
                                                                <label for="driver_id<?php echo $route['route_id']; ?>" class="form-label">Assign Driver (Optional)</label>
                                                                <select class="form-select" id="driver_id<?php echo $route['route_id']; ?>" name="driver_id">
                                                                    <option value="">Select Driver</option>
                                                                    <?php foreach($drivers as $driver): ?>
                                                                        <option value="<?php echo $driver['employee_id']; ?>">
                                                                            <?php echo htmlspecialchars($driver['driver_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning">
                                                                No drivers available. Create a driver employee first.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="assign_bus" class="btn btn-primary">Assign Bus</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Terminal filter
            const filterButtons = document.querySelectorAll('.filter-btn');
            const terminalSections = document.querySelectorAll('.terminal-section');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const terminal = this.getAttribute('data-terminal');
                    
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide terminal sections
                    terminalSections.forEach(section => {
                        const sectionTerminal = section.getAttribute('data-terminal');
                        
                        if (terminal === 'all' || terminal === sectionTerminal) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                });
            });
            
            // Animation for new elements
            const fadeElements = document.querySelectorAll('.fade-in');
            
            fadeElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>