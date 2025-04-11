<?php
// dashboard.php
require_once 'connect.php';

// Fetch bus data
$busQuery = "SELECT * FROM tblbus";
$busResult = $connection->query($busQuery);
$buses = [];
$activeBuses = 0;
$inactiveBuses = 0;
$maintenanceBuses = 0;

while($row = $busResult->fetch_assoc()) {
    $buses[] = $row;
    if($row['status'] == 'active') $activeBuses++;
    elseif($row['status'] == 'inactive') $inactiveBuses++;
    elseif($row['status'] == 'maintenance') $maintenanceBuses++;
}

// Fetch terminal data
$terminalQuery = "SELECT * FROM tblterminal";
$terminalResult = $connection->query($terminalQuery);
$terminals = [];
while($row = $terminalResult->fetch_assoc()) {
    $terminals[] = $row;
}

// Fetch passenger data
$passengerQuery = "SELECT COUNT(*) as count FROM tblpassenger";
$passengerResult = $connection->query($passengerQuery);
$passengerCount = $passengerResult->fetch_assoc()['count'];

// Fetch employee data with types
$employeeQuery = "SELECT COUNT(*) as count FROM tblemployee";
$employeeResult = $connection->query($employeeQuery);
$employeeCount = $employeeResult->fetch_assoc()['count'];

// Fetch driver count
$driverQuery = "SELECT COUNT(*) as count FROM tbldriver";
$driverResult = $connection->query($driverQuery);
$driverCount = $driverResult->fetch_assoc()['count'];

// Fetch conductor count
$conductorQuery = "SELECT COUNT(*) as count FROM tblconductor";
$conductorResult = $connection->query($conductorQuery);
$conductorCount = $conductorResult->fetch_assoc()['count'];

// Fetch seat data with better handling for buses without seat records
$seatQuery = "SELECT 
                b.plateNo, 
                b.busID,
                COUNT(s.seatID) as totalSeats,
                SUM(CASE WHEN s.isAvailable = 1 THEN 1 ELSE 0 END) as availableSeats
              FROM tblbus b
              LEFT JOIN tblseat s ON s.busID = b.busID
              GROUP BY b.plateNo, b.busID";
$seatResult = $connection->query($seatQuery);
$seatData = [];
$plateNos = [];
$availableSeats = [];
$occupiedSeats = [];

while($row = $seatResult->fetch_assoc()) {
    $seatData[] = $row;
    $plateNos[] = $row['plateNo'];
    // Handle case where bus might not have seats yet
    $totalSeats = $row['totalSeats'] ?: 0;
    $availableSeats[] = $row['availableSeats'] ?: 0;
    $occupiedSeats[] = $totalSeats - ($row['availableSeats'] ?: 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Pilot | Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a316c;
            --blue: #1765c0;
            --yellow: #e9ad10;
            --light-yellow: #f0b732;
            --light-blue: #4773b5;
            --light-gray: #cad3da;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--navy);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-image: url('header_photo.png');
            background-size: cover;
            background-position: center;
            height: 150px;
            border-bottom: 20px solid var(--navy);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        h1 {
            font-weight: 600;
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--navy);
            font-weight: bold;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .card-title {
            color: var(--navy);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .buses { background-color: rgba(10, 49, 108, 0.1); color: var(--navy); }
        .terminals { background-color: rgba(231, 122, 44, 0.1); color: var(--light-blue); }
        .passengers { background-color: rgba(252, 201, 49, 0.1); color: var(--yellow); }
        .employees { background-color: rgba(23, 101, 192, 0.1); color: var(--blue); }
        
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .graph-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .graph-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .graph-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .graph-title {
            color: var(--navy);
            font-weight: 600;
            font-size: 18px;
        }
        
        .panel {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .panel-title {
            color: var(--navy);
            font-weight: 600;
            font-size: 18px;
        }
        
        .view-all {
            color: var(--blue);
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px 15px;
            background-color: var(--light-gray);
            color: var(--navy);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-gray);
            font-size: 14px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .active { background-color: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .maintenance { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; }
        .inactive { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        
        .bus-list {
            list-style: none;
        }
        
        .bus-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .bus-item:last-child {
            border-bottom: none;
        }
        
        .bus-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--navy);
            font-weight: bold;
        }
        
        .bus-info {
            flex-grow: 1;
        }
        
        .bus-plate {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .bus-destination {
            font-size: 12px;
            color: #666;
        }
        
        canvas {
            width: 100% !important;
            height: auto !important;
        }
        
        footer {
            background-color: var(--navy);
            color: white;
            text-align: left;
            padding: 10px 20px;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 14px;
        }
        
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .main-content, .graph-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1></h1>
                <div class="user-info">
                    <div class="user-avatar">TP</div>
                    <span>PowerPuff (Admin)</span>
                </div>
            </div>
        </header>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="card-icon buses">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 11H6V6h12m-6-1a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2 2 2 0 0 1 2-2m-8 9h2v5H4m5 0h2v5H9m3-5h2v5h-2m3 0h2v5h-2m3-5h2v5h-2m1-13H3c-1.11 0-2 .89-2 2v12a2 2 0 0 0 2 2h1v-5h16v5h1a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z"/>
                    </svg>
                </div>
                <div class="card-title">Active Buses</div>
                <div class="card-value"><?php echo $activeBuses; ?></div>
                <div class="card-change">Out of <?php echo count($buses); ?> total</div>
            </div>
            
            <div class="stat-card">
                <div class="card-icon terminals">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                    </svg>
                </div>
                <div class="card-title">Terminals</div>
                <div class="card-value"><?php echo count($terminals); ?></div>
                <div class="card-change">Nationwide</div>
            </div>
            
            <div class="stat-card">
                <div class="card-icon passengers">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 4a4 4 0 0 1 4 4 4 4 0 0 1-4 4 4 4 0 0 1-4-4 4 4 0 0 1 4-4m0 10c4.42 0 8 1.79 8 4v2H4v-2c0-2.21 3.58-4 8-4z"/>
                    </svg>
                </div>
                <div class="card-title">Passengers</div>
                <div class="card-value"><?php echo $passengerCount; ?></div>
                <div class="card-change">Registered</div>
            </div>
            
            <div class="stat-card">
                <div class="card-icon employees">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4m4 10.54c0 1.06-.28 3.53-2.19 6.29L13 15l.94-1.88c-.62-.07-1.27-.12-1.94-.12-.67 0-1.32.05-1.94.12L11 15l-.81 4.83C8.28 17.07 8 14.6 8 13.54c-2.39.7-4 1.96-4 3.46v4h16v-4c0-1.5-1.6-2.76-4-3.46z"/>
                    </svg>
                </div>
                <div class="card-title">Employees</div>
                <div class="card-value"><?php echo $employeeCount; ?></div>
                <div class="card-change">Drivers: <?php echo $driverCount; ?>, Conductors: <?php echo $conductorCount; ?></div>
            </div>
        </div>
        
        <!-- Graph Section -->
        <div class="graph-section">
            <div class="graph-container">
                <div class="graph-header">
                    <div class="graph-title">Bus Status Distribution</div>
                </div>
                <canvas id="busStatusChart"></canvas>
            </div>
            
            <div class="graph-container">
                <div class="graph-header">
                    <div class="graph-title">Seat Availability</div>
                </div>
                <canvas id="seatAvailabilityChart"></canvas>
            </div>
        </div>
        
        <div class="main-content">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Bus Information</div>
                    <a href="#" class="view-all">View All</a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Bus ID</th>
                            <th>Plate No.</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Destination</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($buses as $bus): ?>
                        <tr>
                            <td><?php echo $bus['busID']; ?></td>
                            <td><?php echo $bus['plateNo']; ?></td>
                            <td><?php echo $bus['busType']; ?></td>
                            <td><?php echo $bus['capacity']; ?></td>
                            <td><?php echo $bus['destination']; ?></td>
                            <td>
                                <span class="status <?php echo $bus['status']; ?>">
                                    <?php echo ucfirst($bus['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Terminal Information</div>
                    <a href="#" class="view-all">View All</a>
                </div>
                
                <ul class="bus-list">
                    <?php foreach($terminals as $terminal): 
                        $initials = '';
                        $words = explode(' ', $terminal['name']);
                        foreach($words as $word) {
                            $initials .= strtoupper(substr($word, 0, 1));
                        }
                    ?>
                    <li class="bus-item">
                        <div class="bus-icon"><?php echo $initials; ?></div>
                        <div class="bus-info">
                            <div class="bus-plate"><?php echo $terminal['name']; ?></div>
                            <div class="bus-destination"><?php echo $terminal['address']; ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <footer>
        Sheena Mae Jaquez || Joana Carla Gako || Zendy Mariel Dy || BSCS - 2
    </footer>

    <script>
        // Bus Status Chart
        const busStatusCtx = document.getElementById('busStatusChart').getContext('2d');
        const busStatusChart = new Chart(busStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Maintenance', 'Inactive'],
                datasets: [{
                    data: [<?php echo $activeBuses; ?>, <?php echo $maintenanceBuses; ?>, <?php echo $inactiveBuses; ?>],
                    backgroundColor: [
                        '#2ecc71', // Green for active
                        '#f1c40f', // Yellow for maintenance
                        '#e74c3c'  // Red for inactive
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} buses`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Seat Availability Chart
        const seatAvailabilityCtx = document.getElementById('seatAvailabilityChart').getContext('2d');
        const seatAvailabilityChart = new Chart(seatAvailabilityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($plateNos); ?>,
                datasets: [
                    {
                        label: 'Available Seats',
                        data: <?php echo json_encode($availableSeats); ?>,
                        backgroundColor: '#2ecc71',
                        borderWidth: 0
                    },
                    {
                        label: 'Occupied Seats',
                        data: <?php echo json_encode($occupiedSeats); ?>,
                        backgroundColor: '#e74c3c',
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>
