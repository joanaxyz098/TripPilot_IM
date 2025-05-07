<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Pilot | Your Ultimate Bus Booking Platform</title>

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
        }

        /* Header & Navigation */
        header {
            background-color: rgba(10, 49, 108, 0.9);
            position: fixed;
            width: 100%;
            z-index: 1000;
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

        /* Hero Section */
        .hero {
            height: 100vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/random/1600x900/?bus,travel');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            position: relative;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            color: white;
            text-align: left;
            width: 100%;
        }

        .hero-content h1 {
            font-size: 52px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 20px;
            margin-bottom: 30px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            max-width: 600px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        .primary-btn {
            background-color: var(--secondary);
            color: var(--dark);
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            border: none;
            cursor: pointer;
        }

        .primary-btn i {
            margin-right: 8px;
        }

        .primary-btn:hover {
            background-color: var(--secondary-hover);
            transform: translateY(-2px);
        }

        .secondary-btn {
            background-color: transparent;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            border: 2px solid white;
        }

        .secondary-btn i {
            margin-right: 8px;
        }

        .secondary-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Booking Form */
        .booking-container {
            background: white;
            max-width: 1000px;
            margin: -80px auto 60px;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .booking-form h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--primary);
            font-weight: 600;
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

        /* Features Section */
        .features {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .section-header p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(23, 101, 192, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .feature-icon i {
            font-size: 24px;
            color: var(--primary-light);
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--dark);
            font-weight: 600;
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
        }

        /* About Us Section */
        .about {
            padding: 100px 20px;
            background-color: var(--primary);
            color: white;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 60px;
            align-items: center;
        }

        .about-text h2 {
            font-size: 36px;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .about-text p {
            line-height: 1.8;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
        }

        .about-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .stat {
            text-align: center;
        }

        .stat h3 {
            font-size: 36px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat p {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .about-image {
            position: relative;
            height: 400px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Contact Section */
        .contact {
            padding: 100px 20px;
            background-color: #f8f9fa;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .contact-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(23, 101, 192, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .contact-icon i {
            font-size: 24px;
            color: var(--primary-light);
        }

        .contact-card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .contact-card p, .contact-card a {
            color: var(--gray);
            line-height: 1.6;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-card a:hover {
            color: var(--primary-light);
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 20px 20px;
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

        /* Responsive Styles */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 36px;
            }

            .hero-content p {
                font-size: 16px;
            }

            .booking-container {
                margin-top: -50px;
                padding: 20px;
            }

            .search-btn {
                margin-top: 15px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .about-stats {
                grid-template-columns: 1fr;
            }

            .about-image {
                height: 300px;
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
            <a href="#" class="logo">
                <i class="fas fa-bus"></i>
                <h1>Trip Pilot</h1>
            </a>
            <button class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
            <ul id="nav-menu">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="book.php">Book Now</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-btn">Log Out</a></li>
                <?php else: ?>
                    <li><a href="login.php?redirect=book.php">Book Now</a></li>
                    <li><a href="login.php" class="nav-btn">Log In</a></li>
                <?php endif; ?>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Your Journey,<br>Your Comfort</h1>
            <p>Book your bus tickets online with Trip Pilot - the smart way to travel with real-time tracking and hassle-free experience.</p>
            <div class="hero-buttons">
                <a href="#booking" class="primary-btn"><i class="fas fa-ticket-alt"></i> Book Now</a>
                <a href="#about" class="secondary-btn"><i class="fas fa-info-circle"></i> Learn More</a>
            </div>
        </div>
    </section>


    <!-- Features Section -->
    <section class="features">
        <div class="section-header">
            <h2>Why Choose Trip Pilot?</h2>
            <p>Experience the best bus booking platform with features designed to make your journey comfortable and stress-free.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Real-Time Tracking</h3>
                <p>Track your bus in real-time and get accurate estimated arrival times so you never miss your ride.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Tickets</h3>
                <p>Paperless ticketing system allows you to book and manage your tickets directly from your smartphone.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-couch"></i>
                </div>
                <h3>Seat Selection</h3>
                <p>Choose your preferred seat with our interactive seat map for maximum comfort during your journey.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-percent"></i>
                </div>
                <h3>Exclusive Deals</h3>
                <p>Get access to exclusive discounts, promotions and rewards for loyal customers.</p>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about" id="about">
        <div class="about-container">
            <div class="about-text">
                <h2>About Trip Pilot</h2>
                <p>At Trip Pilot, we are committed to revolutionizing the way bus systems are managed. Our innovative bus terminal application offers an intuitive, all-in-one platform for seamless transit management.</p>
                <p>Whether you're overseeing routes, schedules, or real-time tracking, our solution provides transit operators with powerful tools to enhance efficiency and ensure on-time performance. With user-friendly interfaces and cutting-edge technology, we empower transit agencies to optimize operations and deliver reliable services.</p>
                <div class="about-stats">
                    <div class="stat">
                        <h3>50+</h3>
                        <p>Bus Partners</p>
                    </div>
                    <div class="stat">
                        <h3>200+</h3>
                        <p>Routes</p>
                    </div>
                    <div class="stat">
                        <h3>100K+</h3>
                        <p>Happy Travelers</p>
                    </div>
                    <div class="stat">
                        <h3>24/7</h3>
                        <p>Support</p>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="/api/placeholder/500/400" alt="Modern bus interior">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="contact-container">
            <div class="section-header">
                <h2>Get In Touch</h2>
                <p>We'd love to hear from you! Whether you have questions, feedback, or need support, our team is here to help.</p>
            </div>
            <div class="contact-details">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Office Location</h3>
                    <p>123 Transport Avenue, Tech District, Silicon City</p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Phone Number</h3>
                    <a href="tel:+1234567890">+1 (234) 567-890</a>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Address</h3>
                    <a href="mailto:info@trippilot.com">info@trippilot.com</a>
                </div>
            </div>
        </div>
    </section>

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
                    <li><a href="#home">Home</a></li>
                    <li><a href="#booking">Book Now</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Services</h3>
                <ul>
                    <li><a href="#">Route Planning</a></li>
                    <li><a href="#">Bus Tracking</a></li>
                    <li><a href="#">Group Bookings</a></li>
                    <li><a href="#">Corporate Solutions</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Help & Support</h3>
                <ul>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Help Center</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Trip Pilot. All Rights Reserved.</p>
            <p class="team-names">Sheena Mae Jaquez || Joana Carla Gako || Zendy Mariel Dy || BSCS - 2</p>
        </div>
    </footer>

    