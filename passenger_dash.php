<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$passenger_query = "SELECT * FROM passengers WHERE user_id = $user_id";
$passenger_result = $connection->query($passenger_query);
$passenger = $passenger_result->fetch_assoc();

// Get active trips
$trip_query = "SELECT t.*, r.origin, r.destination, r.mode, r.estimated_duration, bc.name as company_name 
               FROM trips t 
               JOIN routes r ON t.route_id = r.route_id 
               JOIN bus_companies bc ON t.bus_company_id = bc.company_id 
               WHERE t.status = 'active'
               ORDER BY t.departure_time";
$trips_result = $connection->query($trip_query);

// Get passenger bookings
$booking_query = "SELECT b.*, t.departure_time, t.arrival_time, t.price, r.origin, r.destination, bc.name as company_name 
                  FROM bookings b
                  JOIN trips t ON b.trip_id = t.trip_id
                  JOIN routes r ON t.route_id = r.route_id
                  JOIN bus_companies bc ON t.bus_company_id = bc.company_id
                  WHERE b.passenger_id = {$passenger['passenger_id']}
                  ORDER BY b.booking_date DESC";
$bookings_result = $connection->query($booking_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passenger Dashboard | Trip Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same CSS as before, keep all your styles */
        body { background-color: #f1f5f9; font-family: 'Poppins', sans-serif; }
        .container { max-width: 1200px; margin: auto; padding: 1rem; }
        .navbar { background: #0a316c; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 0; }
        .navbar-content { display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { text-decoration: none; font-weight: 600; color: #f1f5f9; font-size: 1.25rem; display: flex; gap: 0.5rem; align-items: center; }
        .navbar-links a { margin-left: 1rem; text-decoration: none; color: #f1f5f9; font-weight: 500; }
        .navbar-links a:hover { color:rgb(156, 188, 255); }

        .section-title { margin: 2rem 0 1rem; font-size: 1.5rem; font-weight: 600; color: #1e293b; }

        .trip-card, .booking-card {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .trip-card h3, .booking-card h3 { color: #1e293b; margin-bottom: 0.5rem; }
        .trip-info p, .booking-info p { color: #475569; margin: 0.25rem 0; }

        .trip-time .time, .booking-time .time { font-weight: 600; color: #2563eb; }
        .trip-price .amount, .booking-price .amount { font-size: 1.25rem; font-weight: 700; color: #1e293b; }

        .book-btn {
            background: #10b981;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            text-align: center;
        }

        .book-btn:hover { background-color: #0d9488; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="navbar-brand"><i class="fas fa-bus"></i> Trip Pilot</a>
                <div class="navbar-links">
                    <a href="passenger_dash.php">Dashboard</a>
                    <a href="passenger_dash.php">My Bookings</a>
                    <a href="index.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
    <!-- Passenger Bookings Section -->
    <h2 class="section-title">Your Bookings</h2>
    <?php if ($bookings_result->num_rows > 0): ?>
        <?php while ($booking = $bookings_result->fetch_assoc()): ?>
            <div class="booking-card">
                <div class="booking-info">
                    <h3><?= $booking['origin']; ?> to <?= $booking['destination']; ?></h3>
                    <p><i class="fas fa-bus"></i> <?= $booking['company_name']; ?></p>
                    <p><i class="fas fa-receipt"></i> Booking Ref: <?= $booking['booking_id']; ?></p>
                    <p><i class="fas fa-calendar"></i> Booked on: <?= date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                </div>
                <div class="booking-time">
                    <p class="time"><?= date('h:i A', strtotime($booking['departure_time'])); ?> - <?= date('h:i A', strtotime($booking['arrival_time'])); ?></p>
                </div>
                <div class="booking-price">
                    <p class="amount">₱<?= number_format($booking['price'], 2); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>

    <!-- Available Trips Section -->
    <h2 class="section-title">Available Trips</h2>
    <?php if ($trips_result->num_rows > 0): ?>
        <?php while ($trip = $trips_result->fetch_assoc()): ?>
            <div class="trip-card">
                <div class="trip-info">
                    <h3><?= $trip['origin']; ?> to <?= $trip['destination']; ?></h3>
                    <p><i class="fas fa-bus"></i> <?= $trip['company_name']; ?></p>
                    <p><i class="fas fa-tag"></i> <?= $trip['mode']; ?> Service</p>
                </div>
                <div class="trip-time">
                    <div class="time"><?= date('h:i A', strtotime($trip['departure_time'])); ?> - <?= date('h:i A', strtotime($trip['arrival_time'])); ?></div>
                    <p><i class="fas fa-clock"></i> <?= $trip['estimated_duration']; ?></p>
                </div>
                <div class="trip-price">
                    <div class="amount">₱<?= number_format($trip['price'], 2); ?></div>
                    <p><?= $trip['available_seats']; ?> seats available</p>
                </div>
                <div class="trip-actions">
                    <a href="book.php?trip_id=<?= $trip['trip_id']; ?>" class="book-btn"><i class="fas fa-ticket-alt"></i> Book Now</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No available trips at the moment.</p>
    <?php endif; ?>
</div>
</body>
</html>
