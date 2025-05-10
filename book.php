<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "trippilot");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with return URL
    header("Location: login.php?redirect=book.php");
    exit();
}

// Check if user is a passenger
if ($_SESSION['user_type'] !== 'passenger') {
    $_SESSION['error'] = "Only passengers can make bookings.";
    header("Location: index.php");
    exit();
}

// Get passenger information
$user_id = $_SESSION['user_id'];
$passenger_query = "SELECT * FROM passengers WHERE user_id = $user_id";
$passenger_result = $conn->query($passenger_query);

if ($passenger_result->num_rows == 0) {
    $_SESSION['error'] = "Passenger profile not found. Please contact support.";
    header("Location: index.php");
    exit();
}

$passenger = $passenger_result->fetch_assoc();
$passenger_id = $passenger['passenger_id'];
$passenger_name = $passenger['first_name'] . ' ' . $passenger['last_name'];

// Process booking form
$booking_success = false;
$booking_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_trip'])) {
    $trip_id = $conn->real_escape_string($_POST['trip_id']);
    $travel_date = $conn->real_escape_string($_POST['travel_date']);
    $num_passengers = $conn->real_escape_string($_POST['num_passengers']);
    
    // Validate trip exists and has enough seats
    $trip_query = "SELECT t.*, r.origin, r.destination, r.mode, bc.name as company_name 
                  FROM trips t 
                  JOIN routes r ON t.route_id = r.route_id 
                  JOIN bus_companies bc ON t.bus_company_id = bc.company_id 
                  WHERE t.trip_id = $trip_id";
    $trip_result = $conn->query($trip_query);
    
    if ($trip_result->num_rows == 0) {
        $booking_error = "Selected trip not found.";
    } else {
        $trip = $trip_result->fetch_assoc();
        
        // Check if enough seats are available
        if ($trip['available_seats'] < $num_passengers) {
            $booking_error = "Not enough seats available for this trip.";
        } else {
            // Calculate total amount
            $total_amount = $trip['price'] * $num_passengers;
            
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Insert booking
                $insert_booking = "INSERT INTO bookings (trip_id, passenger_id, travel_date, num_passengers, total_amount, status) 
                                  VALUES ($trip_id, $passenger_id, '$travel_date', $num_passengers, $total_amount, 'confirmed')";
                
                if (!$conn->query($insert_booking)) {
                    throw new Exception("Error creating booking: " . $conn->error);
                }
                
                $booking_id = $conn->insert_id;
                
                // Update available seats
                $update_seats = "UPDATE trips SET available_seats = available_seats - $num_passengers WHERE trip_id = $trip_id";
                
                if (!$conn->query($update_seats)) {
                    throw new Exception("Error updating seats: " . $conn->error);
                }
                
                // Commit transaction
                $conn->commit();
                $booking_success = true;
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $booking_error = $e->getMessage();
            }
        }
    }
}

// Get all routes for search form
$routes_query = "SELECT * FROM routes ORDER BY origin, destination";
$routes_result = $conn->query($routes_query);

// Get search results
$search_results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_trips'])) {
    $origin = $conn->real_escape_string($_POST['origin']);
    $destination = $conn->real_escape_string($_POST['destination']);
    $search_date = $conn->real_escape_string($_POST['search_date']);
    
    $search_query = "SELECT t.*, r.origin, r.destination, r.mode, r.estimated_duration, 
                    bc.name as company_name 
                    FROM trips t 
                    JOIN routes r ON t.route_id = r.route_id 
                    JOIN bus_companies bc ON t.bus_company_id = bc.company_id 
                    WHERE r.origin LIKE '%$origin%' 
                    AND r.destination LIKE '%$destination%' 
                    AND t.status = 'active' 
                    AND t.available_seats > 0
                    ORDER BY t.departure_time";
    
    $search_results = $conn->query($search_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Trip | Trip Pilot</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        :root {
            --primary: #0a316c;
            --primary-light: #1765c0;
            --secondary: #e9ad10;
            --secondary-hover: #f0b732;
            --dark: #222;
            --light: #f8f9fa;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #f4f7fc;
            color: var(--dark);
            padding-top: 80px;
        }

        /* Header & Navigation */
        header {
            background-color: rgba(10, 49, 108, 0.9);
            position: fixed;
            width: 100%;
            z-index: 1000;
            top: 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
        }

        .logo i {
            font-size: 28px;
            margin-right: 10px;
            color: var(--secondary);
        }

        .logo h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 25px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-btn {
            background-color: var(--secondary);
            color: var(--dark);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }

        .nav-btn:hover {
            background-color: var(--secondary-hover);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .page-header p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
        }

        /* Booking Form */
        .booking-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .booking-form h2 {
            margin-bottom: 25px;
            color: var(--primary);
            font-weight: 600;
            font-size: 24px;
        }

        .form-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--gray);
            font-weight: 500;
        }

        .input-group select,
        .input-group input {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .input-group select:focus,
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(23, 101, 192, 0.1);
        }

        .search-btn {
            background-color: var(--primary);
            color: white;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background-color: var(--primary-light);
        }

        /* Search Results */
        .search-results {
            margin-top: 40px;
        }

        .search-results h2 {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .trip-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .trip-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .trip-info h3 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .trip-info p {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .trip-details {
            text-align: center;
        }

        .trip-details .time {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .trip-details .duration {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .trip-details .duration i {
            margin-right: 5px;
            color: var(--secondary);
        }

        .trip-price {
            text-align: center;
        }

        .trip-price .price {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .trip-price .seats {
            font-size: 14px;
            color: var(--gray);
        }

        .book-btn {
            background-color: var(--secondary);
            color: var(--dark);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            border: none;
            cursor: pointer;
        }

        .book-btn i {
            margin-right: 8px;
        }

        .book-btn:hover {
            background-color: var(--secondary-hover);
            transform: translateY(-2px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-modal {
            color: #999;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #555;
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: var(--primary);
            font-weight: 600;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 20px 20px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .footer-logo i {
            font-size: 24px;
            margin-right: 10px;
            color: var(--secondary);
        }

        .footer-logo h2 {
            font-size: 20px;
            font-weight: 700;
        }

        .footer-about p {
            margin-bottom: 20px;
            line-height: 1.6;
            opacity: 0.8;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--secondary);
            color: var(--dark);
        }

        .footer-links h3 {
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 10px;
        }

        .footer-links ul li a {
            color: white;
            opacity: 0.8;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links ul li a:hover {
            opacity: 1;
            color: var(--secondary);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            opacity: 0.7;
        }

        .team-names {
            margin-top: 10px;
            font-style: italic;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .trip-card {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto;
            }
            
            .trip-action {
                grid-column: 1 / -1;
                display: flex;
                justify-content: center;
                margin-top: 20px;
            }
        }

        @media (max-width: 768px) {
            .trip-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .search-btn {
                margin-top: 15px;
            }
        }

        /* Mobile Navigation */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 992px) {
            .mobile-toggle {
                display: block;
            }

            nav ul {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background-color: var(--primary);
                flex-direction: column;
                align-items: center;
                padding: 20px 0;
                gap: 15px;
                transform: translateY(-150%);
                transition: transform 0.3s ease;
                z-index: 999;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            nav ul.active {
                transform: translateY(0);
            }

            nav ul li {
                margin: 0;
                width: 100%;
                text-align: center;
            }

            nav ul li a {
                display: block;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-bus"></i>
                <h1>Trip Pilot</h1>
            </a>
            <button class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul id="nav-menu">
                    <li><a href="book.php">Book Now</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="passenger_dash.php">Dashboard</a></li>
                        <li><a href="index.php" class="nav-btn">Log Out</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-btn">Log In</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Book Your Bus Ticket</h1>
            <p>Search for available trips, select your preferred route, and book your tickets with ease.</p>
        </div>

        <?php if($booking_success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Booking successful! Your trip has been confirmed.
            </div>
        <?php endif; ?>

        <?php if($booking_error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $booking_error; ?>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <div class="booking-container">
            <div class="booking-form">
                <h2>Find Available Trips</h2>
                <form action="book.php" method="post">
                    <div class="form-group">
                        <div class="input-group">
                            <label for="origin">Origin</label>
                            <select name="origin" id="origin" required>
                                <option value="">Select Origin</option>
                                <?php
                                $origins = [];
                                if ($routes_result->num_rows > 0) {
                                    while($route = $routes_result->fetch_assoc()) {
                                        if (!in_array($route['origin'], $origins)) {
                                            $origins[] = $route['origin'];
                                            echo "<option value='" . $route['origin'] . "'>" . $route['origin'] . "</option>";
                                        }
                                    }
                                }
                                // Reset the result pointer
                                $routes_result->data_seek(0);
                                ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="destination">Destination</label>
                            <select name="destination" id="destination" required>
                                <option value="">Select Destination</option>
                                <?php
                                $destinations = [];
                                if ($routes_result->num_rows > 0) {
                                    while($route = $routes_result->fetch_assoc()) {
                                        if (!in_array($route['destination'], $destinations)) {
                                            $destinations[] = $route['destination'];
                                            echo "<option value='" . $route['destination'] . "'>" . $route['destination'] . "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="search_date">Travel Date</label>
                            <input type="date" name="search_date" id="search_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="search_trips" class="search-btn">
                        <i class="fas fa-search"></i> Search Trips
                    </button>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if(isset($search_results) && $search_results->num_rows > 0): ?>
            <div class="search-results">
                <h2>Available Trips</h2>
                
                <?php while($trip = $search_results->fetch_assoc()): ?>
                    <div class="trip-card">
                        <div class="trip-info">
                            <h3><?php echo $trip['origin']; ?> to <?php echo $trip['destination']; ?></h3>
                            <p><i class="fas fa-bus"></i> <?php echo $trip['company_name']; ?></p>
                            <p><i class="fas fa-tag"></i> <?php echo $trip['mode']; ?> Service</p>
                        </div>
                        <div class="trip-details">
                            <div class="time"><?php echo date('h:i A', strtotime($trip['departure_time'])); ?> - <?php echo date('h:i A', strtotime($trip['arrival_time'])); ?></div>
                            <div class="duration"><i class="fas fa-clock"></i> <?php echo $trip['estimated_duration']; ?></div>
                        </div>
                        <div class="trip-price">
                            <div class="price">₱<?php echo number_format($trip['price'], 2); ?></div>
                            <div class="seats"><?php echo $trip['available_seats']; ?> seats available</div>
                        </div>
                        <div class="trip-action">
                            <button class="book-btn" onclick="openBookingModal(<?php echo $trip['trip_id']; ?>, '<?php echo $trip['origin']; ?>', '<?php echo $trip['destination']; ?>', '<?php echo date('h:i A', strtotime($trip['departure_time'])); ?>', '<?php echo date('h:i A', strtotime($trip['arrival_time'])); ?>', <?php echo $trip['price']; ?>, <?php echo $trip['available_seats']; ?>)">
                                <i class="fas fa-ticket-alt"></i> Book Now
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php elseif(isset($_POST['search_trips'])): ?>
            <div class="search-results">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> No trips found for your selected route and date. Please try different options.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeBookingModal()">&times;</span>
            <div class="modal-header">
                <h2>Confirm Your Booking</h2>
            </div>
            <div id="modalContent">
                <form action="book.php" method="post" id="bookingForm">
                    <input type="hidden" name="trip_id" id="modal_trip_id">
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <label for="passenger_name">Passenger Name</label>
                        <input type="text" value="<?php echo $passenger_name; ?>" readonly>
                    </div>
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <label for="trip_details">Trip Details</label>
                        <input type="text" id="modal_trip_details" readonly>
                    </div>
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <label for="trip_time">Departure - Arrival</label>
                        <input type="text" id="modal_trip_time" readonly>
                    </div>
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <label for="travel_date">Travel Date</label>
                        <input type="date" name="travel_date" id="modal_travel_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <label for="num_passengers">Number of Passengers</label>
                        <input type="number" name="num_passengers" id="modal_num_passengers" min="1" max="10" value="1" required onchange="updateTotalPrice()">
                    </div>
                    
                    <div class="input-group" style="margin-bottom: 25px;">
                        <label for="total_price">Total Price</label>
                        <input type="text" id="modal_total_price" readonly>
                    </div>
                    
                    <button type="submit" name="book_trip" class="search-btn">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <div class="footer-logo">
                    <i class="fas fa-bus"></i>
                    <h2>Trip Pilot</h2>
                </div>
                <p>Your ultimate platform for booking bus tickets, tracking real-time information, and enjoying a seamless travel experience.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="book.php">Book Now</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Help & Support</h3>
                <ul>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Support Center</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Contact Us</h3>
                <ul>
                    <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +1234 567 890</a></li>
                    <li><a href="mailto:info@trippilot.com"><i class="fas fa-envelope"></i> info@trippilot.com</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Cebu City, Philippines</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>© 2025 Trip Pilot. All Rights Reserved.</p>
            <p class="team-names">A project by Zendy Dy, Joana Carla Gako, and Yelzy Dy</p>
        </div>
    </footer>

    <script>
        // Mobile Navigation Toggle
        document.getElementById('mobile-toggle').addEventListener('click', function() {
            document.getElementById('nav-menu').classList.toggle('active');
        });

        // Set minimum date for travel date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            if (document.getElementById('search_date')) {
                document.getElementById('search_date').min = today;
            }
            if (document.getElementById('modal_travel_date')) {
                document.getElementById('modal_travel_date').min = today;
                
                // Set initial value to the search date if it exists
                const searchDate = document.getElementById('search_date');
                if (searchDate && searchDate.value) {
                    document.getElementById('modal_travel_date').value = searchDate.value;
                } else {
                    document.getElementById('modal_travel_date').value = today;
                }
            }
        });

        // Booking Modal Functions
        const modal = document.getElementById('bookingModal');
        let modalTripPrice = 0;

        function openBookingModal(tripId, origin, destination, departureTime, arrivalTime, price, availableSeats) {
            modal.style.display = 'flex';
            document.getElementById('modal_trip_id').value = tripId;
            document.getElementById('modal_trip_details').value = origin + ' to ' + destination;
            document.getElementById('modal_trip_time').value = departureTime + ' - ' + arrivalTime;
            
            // Set max number of passengers based on available seats
            document.getElementById('modal_num_passengers').max = availableSeats;
            
            // Store price for calculations
            modalTripPrice = price;
            
            // Update total price display
            updateTotalPrice();
            
            // Set travel date from search if available
            const searchDate = document.getElementById('search_date');
            if (searchDate && searchDate.value) {
                document.getElementById('modal_travel_date').value = searchDate.value;
            }
        }

        function closeBookingModal() {
            modal.style.display = 'none';
        }

        function updateTotalPrice() {
            const numPassengers = document.getElementById('modal_num_passengers').value;
            const totalPrice = modalTripPrice * numPassengers;
            document.getElementById('modal_total_price').value = '₱' + totalPrice.toFixed(2);
        }

        // Close modal if user clicks outside the modal content
        window.onclick = function(event) {
            if (event.target == modal) {
                closeBookingModal();
            }
        }
    </script>
</body>
</html>