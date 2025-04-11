<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Pilot | Home</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* Header */
        header {
            background-image: url('header_photo.png');
            background-size: cover;
            background-position: center;
            height: 180px;
            color: white;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 20px;
            border-bottom: 20px solid #0a316c;
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #4773b5;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #1765c0;
        }

        /* Main content */
        .content {
	    text-align: center;
	    padding: 40px 20px 10px; /* Reduced bottom padding from 40px to 10px */
	}

        /* Sections */
        .outsideContainer {
	    display: flex;
	    flex-direction: column;
	    align-items: center;
	    width: 100%;
	    padding: 30px 0; /* Reduced from 50px to 30px */
	}

        .container {
            width: 80%;
            max-width: 1500px;
            background-color: #1765c0;
            color: #fff;
            border-radius: 10px;
            padding: 30px 40px;
            margin: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, .2);
        }

        .container p {
            text-align: justify;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .button {
            width: 50%;
            height: 45px;
            background: #e9ad10;
            border: none;
            border-radius: 40px;
            margin-top: 30px;
            font-size: 16px;
            color: #333;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #f0b732;
        }

        /* Footer */
        footer {
            background-color: #0a316c;
            color: white;
            text-align: left;
            padding: 10px 20px;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 14px;
        }

        /* Section Spacing for anchor navigation */
        #about, #contact {
            scroll-margin-top: 200px;
        }
    </style>
</head>
<body>

    <!-- Header with Navigation -->
    <header>
        <nav>
            <a href="#login">Login</a>
            <a href="#about">About Us</a>
            <a href="#contact">Contact Us</a>
            <a href="dashboard.php">Dashboard</a>
        </nav>
    </header>

    <!-- Main Welcome Content -->
    <div class="content" id="login">
        <h1>Welcome to Trip Pilot!</h1>
        <p>Your Seat, Your Journey</p>
    </div>

    <!-- About & Contact Sections -->
    <div class="outsideContainer">
        
        <div id="about" class="container">
            <h2>About Us</h2>
            <p>
                At TripPilot, we are committed to revolutionizing the way bus systems are managed. Our innovative bus terminal application offers an intuitive, all-in-one platform for seamless transit management. Whether you're overseeing routes, schedules, or real-time tracking, our solution provides transit operators with powerful tools to enhance efficiency and ensure on-time performance. With user-friendly interfaces and cutting-edge technology, we empower transit agencies to optimize operations and deliver reliable services.
            </p>
        </div>

        <div id="contact" class="container">
            <h2>Contact Us</h2>
            <p>
                We'd love to hear from you! Whether you have questions, feedback, or need support, our team is here to help. At TripPilot, we are dedicated to providing top-notch customer service and ensuring your experience with our application is as smooth as possible.
            </p>
            <button class="button">Contact Us</button>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        Sheena Mae Jaquez || Joana Carla Gako || Zendy Mariel Dy || BSCS - 2
    </footer>

</body>
</html>
