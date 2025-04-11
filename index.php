<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Home Page</title>
    <style>
        /* Header Styles */
        header {
            background-image: url('header_photo.png'); /* Ensure the image path is correct */
            background-size: cover;
            background-position: center center;
            height: 180px;
            color: white;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 20px;
            border-bottom: 20px solid #0a316c;
        }

        /* Navigation Links */
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
        }

        nav a:hover {
            background-color: #1765c0;
        }

        /* Footer Styles */
        footer {
            background-color: #0a316c;
            color: white;
            text-align: left;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        
        .content {
	            text-align: center;
	            padding: 20px;
        }


	@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
	 
	        * {
	            font-family: "Poppins", sans-serif;
	            margin: 0;
	            padding: 0;
	            box-sizing: border-box;
	        }
	 
	        .outsideContainer {
	            display: flex;
	            flex-direction: column; /* Stack the containers vertically */
	            justify-content: center;
	            align-items: center;
	            width: 100%; /* Set width based on screen size */
	            padding: 50px 0; /* Add vertical padding */
	        }
	 
	        .container {
	    width: 80%;
	    max-width: 1500px; 
	    justify-content: center;
	    align-items: center;
	    background-color: #1765c0;
	    color: #fff;
	    border-radius: 10px;
	    padding: 30px 40px 15px;
	    border: 2px solid rgba(255, 255, 255, .2);
	    backdrop-filter: blur(20px);
	    box-shadow: 0 0 10px rgba(0, 0, 0, .2);
	    margin: 20px;
	    display: flex; /* Ensure flex display */
	    flex-direction: column; /* Stack elements vertically */
	    align-items: center; /* Center content horizontally */
	    text-align: center; /* Optional, to center text in the container */
	}
	 
	.container .button {
	    width: 50%; /* Button width is now set to 50% of container's width */
	    height: 45px;
	    background: #e9ad10;
	    border: none;
	    outline: none;
	    border-radius: 40px;
	    margin-top: 30px;
	    margin-bottom: 10px;
	    box-shadow: 0 0 10px rgba(0, 0, 0, .1);
	    cursor: pointer;
	    font-size: 16px;
	    color: #333;
	    font-weight: 600;
	}
	 
	 
	        #aboutUs, #contactUs {
	            margin-top: 100px; /* This helps create space when navigating to the section */
	        }
 
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <nav>
            <a href="#login">Login</a>
            <a href="#about">About Us</a>
            <a href="#contact">Contact Us</a>
            <a href="dashboard.php">Dashboard</a>
        </nav>
    </header>

    <!-- Main content section -->
    <div class="content">
            <h1>Welcome to Trip Pilot!</h1>
            <p>Your Seat, Your Journey</p>
    </div>
    
    <div class="outsideContainer">
    <div id="aboutUs" class="container">
    <h2 style="text-align: center;">About Us</h2>
    <p style="text-align: justify;">At TripPilot, we are committed to revolutionizing the way bus systems are managed. Our innovative bus terminal application offers an intuitive, all-in-one platform for seamless transit management. Whether you're overseeing routes, schedules, or real-time tracking, our solution provides transit operators with powerful tools to enhance efficiency, improve passenger experiences, and ensure on-time performance. With user-friendly interfaces and cutting-edge technology, we empower transit agencies to optimize their operations, minimize delays, and maintain smooth and reliable services. We aim to be the trusted partner in making public transportation smarter, faster, and more accessible for everyone.</p>
    </div>
     
            <div id="contactUs" class="container">
    <p style="text-align: justify;">We'd love to hear from you! Whether you have questions, feedback, or need support, our team is here to help. Reach out to us, and we'll ensure that your inquiries are addressed promptly. At Trip Pilot, we are dedicated to providing top-notch customer service and ensuring your experience with our application is as smooth as possible.</p>
    <button class="button">Contact Us</button>
    </div>
</div>

    <!-- Footer -->
    <footer>
        Sheena Mae Jaquez || Joana Carla Gako || Zendy Mariel Dy || BSCS - 2
    </footer>

</body>
</html>
