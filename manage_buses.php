<?php
session_start();
require_once 'connect.php';

// Security check - only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee' || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Add a new bus (trip)
if (isset($_POST['add_bus'])) {
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
        $_SESSION['success_message'] = "New bus added successfully!";
        header("Location: manage_buses.php");
        exit();
    } else {
        $error_message = "Error adding bus: " . $connection->error;
    }
}

// Update bus status
if (isset($_POST['update_status'])) {
    $trip_id = $connection->real_escape_string($_POST['trip_id']);
    $status = $connection->real_escape_string($_POST['status']);
    
    $query = "UPDATE trips SET status = '$status' WHERE trip_id = '$trip_id'";
    
    if ($connection->query($query)) {
        $_SESSION['success_message'] = "Bus status updated successfully!";
        header("Location: manage_buses.php");
        exit();
    } else {
        $error_message = "Error updating status: " . $connection->error;
    }
}

// Delete a bus (trip)
if (isset($_POST['delete_bus'])) {
    $trip_id = $connection->real_escape_string($_POST['trip_id']);
    
    // Check if there are any bookings for this trip
    $check_query = "SELECT COUNT(*) as count FROM bookings WHERE trip_id = '$trip_id'";
    $result = $connection->query($check_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error_message = "Cannot delete bus with existing bookings!";
    } else {
        $query = "DELETE FROM trips WHERE trip_id = '$trip_id'";
        
        if ($connection->query($query)) {
            $_SESSION['success_message'] = "Bus deleted successfully!";
            header("Location: manage_buses.php");
            exit();
        } else {
            $error_message = "Error deleting bus: " . $connection->error;
        }
    }
}

// Fetch all data needed for the page
$companies = [];
$company_query = "SELECT * FROM bus_companies ORDER BY name";
$company_result = $connection->query($company_query);
if ($company_result) {
    while ($row = $company_result->fetch_assoc()) {
        $companies[] = $row;
    }
}

$routes = [];
$route_query = "SELECT * FROM routes ORDER BY origin, destination";
$route_result = $connection->query($route_query);
if ($route_result) {
    while ($row = $route_result->fetch_assoc()) {
        $routes[] = $row;
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

// Fetch buses (trips) with company and route info
$buses_query = "SELECT t.trip_id, t.departure_time, t.arrival_time, t.price, 
                t.available_seats, t.status, bc.name as company_name, bc.company_id,
                r.origin, r.destination, r.route_id, r.mode
                FROM trips t
                JOIN bus_companies bc ON t.bus_company_id = bc.company_id
                JOIN routes r ON t.route_id = r.route_id
                ORDER BY bc.name, r.origin, r.destination";
$buses_result = $connection->query($buses_query);
$buses = [];
if ($buses_result) {
    while ($row = $buses_result->fetch_assoc()) {
        $buses[] = $row;
    }
}

// Create an associative array to group buses by company
$grouped_buses = [];
foreach ($buses as $bus) {
    $company_id = $bus['company_id'];
    if (!isset($grouped_buses[$company_id])) {
        $grouped_buses[$company_id] = [
            'company_name' => $bus['company_name'],
            'buses' => []
        ];
    }
    $grouped_buses[$company_id]['buses'][] = $bus;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses | TripPilot</title>
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-travelling {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-unavailable {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Animation for status changes */
        @keyframes statusChange {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .status-updating {
            animation: statusChange 0.6s ease infinite;
        }

        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
        <header class="admin-header">
        <h1 class="admin-title">Trip Pilot Manage Buses</h1>
        <nav class="admin-nav">
            <a href="admin_dash.php">Dashboard</a>
            <a href="manage_buses.php">Manage Buses</a>
            <a href="manage_routes.php">Manage Routes</a>
            <a href="index.php">Logout</a>
        </nav>
    </header>

    <div class="container py-4">
        <!-- Alerts for success/error messages -->
        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Bus Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title"><i class="fas fa-plus-circle"></i> Add New Bus</h2>
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="company_id" class="form-label">Bus Company</label>
                                    <select class="form-select" id="company_id" name="company_id" required>
                                        <option value="">Select Bus Company</option>
                                        <?php foreach($companies as $company): ?>
                                            <option value="<?php echo $company['company_id']; ?>">
                                                <?php echo htmlspecialchars($company['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="route_id" class="form-label">Route</label>
                                    <select class="form-select" id="route_id" name="route_id" required>
                                        <option value="">Select Route</option>
                                        <?php foreach($routes as $route): ?>
                                            <option value="<?php echo $route['route_id']; ?>">
                                                <?php echo htmlspecialchars($route['origin'] . ' to ' . $route['destination'] . ' (' . $route['mode'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="departure_time" class="form-label">Departure Time</label>
                                    <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="arrival_time" class="form-label">Arrival Time</label>
                                    <input type="time" class="form-control" id="arrival_time" name="arrival_time" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="price" class="form-label">Price (PHP)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="seats" class="form-label">Available Seats</label>
                                    <input type="number" min="1" class="form-control" id="seats" name="seats" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="travelling">Travelling</option>
                                        <option value="unavailable">Unavailable</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="add_bus" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Bus
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bus Companies and Buses Section -->
        <h2 class="section-title"><i class="fas fa-bus"></i> Bus Companies & Their Buses</h2>
        
        <?php if(empty($grouped_buses)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No buses available. Add a new bus to get started.
            </div>
        <?php else: ?>
            <?php foreach($grouped_buses as $company_id => $company_data): ?>
                <div class="card company-card">
                    <div class="card-header company-header">
                        <h5><i class="fas fa-building"></i> <?php echo htmlspecialchars($company_data['company_name']); ?></h5>
                        <span class="badge bg-primary rounded-pill">
                            <?php echo count($company_data['buses']); ?> Buses
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Route</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Price</th>
                                        <th>Available Seats</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($company_data['buses'] as $bus): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bus['origin'] . ' to ' . $bus['destination']); ?></td>
                                            <td><?php echo date("h:i A", strtotime($bus['departure_time'])); ?></td>
                                            <td><?php echo date("h:i A", strtotime($bus['arrival_time'])); ?></td>
                                            <td>₱<?php echo number_format($bus['price'], 2); ?></td>
                                            <td><?php echo $bus['available_seats']; ?></td>
                                            <td>
                                                <?php 
                                                    $status_class = '';
                                                    $status_text = '';
                                                    
                                                    switch($bus['status']) {
                                                        case 'available':
                                                            $status_class = 'status-available';
                                                            $status_text = 'Available';
                                                            break;
                                                        case 'travelling':
                                                            $status_class = 'status-travelling';
                                                            $status_text = 'Travelling';
                                                            break;
                                                        case 'unavailable':
                                                            $status_class = 'status-unavailable';
                                                            $status_text = 'Unavailable';
                                                            break;
                                                    }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>" id="status-badge-<?php echo $bus['trip_id']; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Update Status Button -->
                                                <button type="button" class="action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $bus['trip_id']; ?>">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                
                                                <!-- Delete Button -->
                                                <button type="button" class="action-btn delete-btn" data-bs-toggle="modal" data-bs-target="#deleteBusModal<?php echo $bus['trip_id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Update Status Modal -->
                                                <div class="modal fade" id="updateStatusModal<?php echo $bus['trip_id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Bus Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="statusForm<?php echo $bus['trip_id']; ?>" method="POST">
                                                                    <input type="hidden" name="trip_id" value="<?php echo $bus['trip_id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="status<?php echo $bus['trip_id']; ?>" class="form-label">Status</label>
                                                                        <select class="form-select" id="status<?php echo $bus['trip_id']; ?>" name="status" required>
                                                                            <option value="available" <?php echo ($bus['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                                                            <option value="travelling" <?php echo ($bus['status'] == 'travelling') ? 'selected' : ''; ?>>Travelling</option>
                                                                            <option value="unavailable" <?php echo ($bus['status'] == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="d-flex justify-content-end">
                                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="update_status" class="btn btn-warning" id="updateBtn<?php echo $bus['trip_id']; ?>">
                                                                            <i class="fas fa-sync-alt me-1"></i> Update Status
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Bus Modal -->
                                                <div class="modal fade" id="deleteBusModal<?php echo $bus['trip_id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete this bus from <?php echo htmlspecialchars($bus['origin'] . ' to ' . $bus['destination']); ?>?</p>
                                                                <p class="text-danger"><small>Note: This action cannot be undone, and the bus cannot be deleted if it has existing bookings.</small></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form action="" method="POST">
                                                                    <input type="hidden" name="trip_id" value="<?php echo $bus['trip_id']; ?>">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="delete_bus" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile view of buses (visible on small screens) -->
                        <?php foreach($company_data['buses'] as $bus): ?>
                            <div class="bus-card">
                                <div class="bus-card-header">
                                    <h6><?php echo htmlspecialchars($bus['origin'] . ' to ' . $bus['destination']); ?></h6>
                                    <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch($bus['status']) {
                                            case 'available':
                                                $status_class = 'status-available';
                                                $status_text = 'Available';
                                                break;
                                            case 'travelling':
                                                $status_class = 'status-travelling';
                                                $status_text = 'Travelling';
                                                break;
                                            case 'unavailable':
                                                $status_class = 'status-unavailable';
                                                $status_text = 'Unavailable';
                                                break;
                                        }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>" id="mobile-status-badge-<?php echo $bus['trip_id']; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                                <div class="bus-details">
                                    <div class="bus-detail-item">
                                        <span class="bus-detail-label">Departure:</span>
                                        <span><?php echo date("h:i A", strtotime($bus['departure_time'])); ?></span>
                                    </div>
                                    <div class="bus-detail-item">
                                        <span class="bus-detail-label">Arrival:</span>
                                        <span><?php echo date("h:i A", strtotime($bus['arrival_time'])); ?></span>
                                    </div>
                                    <div class="bus-detail-item">
                                        <span class="bus-detail-label">Price:</span>
                                        <span>₱<?php echo number_format($bus['price'], 2); ?></span>
                                    </div>
                                    <div class="bus-detail-item">
                                        <span class="bus-detail-label">Available Seats:</span>
                                        <span><?php echo $bus['available_seats']; ?></span>
                                    </div>
                                </div>
                                <div class="bus-actions">
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $bus['trip_id']; ?>">
                                        <i class="fas fa-sync-alt me-1"></i> Update Status
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBusModal<?php echo $bus['trip_id']; ?>">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
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
            // Handle status update forms with AJAX
            document.querySelectorAll('[id^="statusForm"]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const tripId = this.querySelector('[name="trip_id"]').value;
                    const status = this.querySelector('[name="status"]').value;
                    const updateBtn = document.getElementById('updateBtn' + tripId);
                    const originalBtnText = updateBtn.innerHTML;
                    
                    // Show loading state
                    updateBtn.disabled = true;
                    updateBtn.innerHTML = '<span class="spinner"></span> Updating...';
                    
                    // Get the modal instance
                    const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal' + tripId));
                    
                    // Prepare form data
                    const formData = new FormData(this);
                    
                    // Send AJAX request
                    fetch('manage_buses.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(() => {
                        // Update the status badge immediately
                        const statusBadge = document.getElementById('status-badge-' + tripId);
                        const mobileStatusBadge = document.getElementById('mobile-status-badge-' + tripId);
                        
                        // Add updating animation
                        statusBadge.classList.add('status-updating');
                        if (mobileStatusBadge) mobileStatusBadge.classList.add('status-updating');
                        
                        // Determine new status text and class
                        let newText, newClass;
                        switch(status) {
                            case 'available':
                                newText = 'Available';
                                newClass = 'status-available';
                                break;
                            case 'travelling':
                                newText = 'Travelling';
                                newClass = 'status-travelling';
                                break;
                            case 'unavailable':
                                newText = 'Unavailable';
                                newClass = 'status-unavailable';
                                break;
                        }
                        
                        // Update the badges after a short delay
                        setTimeout(() => {
                            statusBadge.textContent = newText;
                            statusBadge.className = 'status-badge ' + newClass;
                            statusBadge.classList.remove('status-updating');
                            
                            if (mobileStatusBadge) {
                                mobileStatusBadge.textContent = newText;
                                mobileStatusBadge.className = 'status-badge ' + newClass;
                                mobileStatusBadge.classList.remove('status-updating');
                            }
                            
                            // Close the modal
                            modal.hide();
                            
                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                <i class="fas fa-check-circle me-2"></i> Bus status updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            document.querySelector('.container.py-4').prepend(alertDiv);
                            
                            // Auto-dismiss after 3 seconds
                            setTimeout(() => {
                                const bsAlert = new bootstrap.Alert(alertDiv);
                                bsAlert.close();
                            }, 3000);
                            
                        }, 500);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the status.');
                    })
                    .finally(() => {
                        updateBtn.disabled = false;
                        updateBtn.innerHTML = originalBtnText;
                    });
                });
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>