<?php
session_start();
$connection = new mysqli('localhost', 'root', '', 'trippilot');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$login_error = '';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $connection->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare and execute query
    $stmt = $connection->prepare("SELECT user_id, username, password, user_type FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Get employee role if applicable
            if ($user['user_type'] == 'employee') {
                $stmt = $connection->prepare("SELECT role FROM employees WHERE user_id = ?");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $role_result = $stmt->get_result();
                $role_data = $role_result->fetch_assoc();
                $_SESSION['role'] = $role_data['role'];
                
                // Redirect admin to admin dashboard
                if ($_SESSION['role'] == 'admin') {
                    header("Location: admin_dash.php");
                    exit();
                }
            }
            
            // Check for redirect parameter
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
            
            // Redirect to appropriate page
            if (!empty($redirect)) {
                header("Location: $redirect");
            } else {
                $redirect_page = ($_SESSION['user_type'] == 'passenger') ? 'passenger_dash.php' : 'employee_dash.php';
                header("Location: $redirect_page");
            }
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    } else {
        $login_error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Pilot | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f0f4f8;
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

        /* Login Container */
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #0a316c;
            margin-bottom: 10px;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-form button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #0a316c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-form button:hover {
            background-color: #1765c0;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin: 15px 0;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #0a316c;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <nav>
        </nav>
    </header>

    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back!</h1>
            <p>Please login to continue</p>
        </div>

        <?php if(!empty($login_error)): ?>
            <div class="error-message"><?php echo $login_error; ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
