<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $connection->real_escape_string($_POST['username']);
    $email = $connection->real_escape_string($_POST['email']);
    $password = $connection->real_escape_string($_POST['password']);
    $confirm_password = $connection->real_escape_string($_POST['confirm_password']);
    $first_name = $connection->real_escape_string($_POST['first_name']);
    $last_name = $connection->real_escape_string($_POST['last_name']);
    $phone = $connection->real_escape_string($_POST['phone']);
    $address = $connection->real_escape_string($_POST['address']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $stmt = $connection->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $connection->begin_transaction();
            try {
                $stmt = $connection->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, 'passenger')");
                $stmt->bind_param("sss", $username, $hashed_password, $email);
                $stmt->execute();
                $user_id = $connection->insert_id;

                $stmt = $connection->prepare("INSERT INTO passengers (user_id, first_name, last_name, phone, address) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $first_name, $last_name, $phone, $address);
                $stmt->execute();

                $connection->commit();
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } catch (Exception $e) {
                $connection->rollback();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Pilot | Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

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

        .form-container {
            max-width: 600px;
            background-color: #1765c0;
            color: white;
            margin: 50px auto;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-container h1 {
            margin-bottom: 10px;
            text-align: center;
        }

        .form-container p {
            text-align: center;
            margin-bottom: 20px;
        }

        form input, form button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
        }

        form input {
            background-color: #fff;
            color: #000;
        }

        form button {
            background-color: #e9ad10;
            color: #000;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #f0b732;
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row input {
            flex: 1;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #ffcccc;
            color: #990000;
            border-radius: 5px;
        }

        .success-message {
            background-color: #ccffcc;
            color: #006600;
            border-radius: 5px;
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
        }

        .register-link a {
            color: #ffd700;
            text-decoration: underline;
        }

        footer {
            background-color: #0a316c;
            color: white;
            text-align: left;
            padding: 10px 20px;
            font-size: 14px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="login.php">Login</a>
            <a href="#about">About Us</a>
            <a href="#contact">Contact Us</a>
            <a href="dashboard.php">Dashboard</a>
        </nav>
    </header>

    <div class="form-container">
        <h1>Create Account</h1>
        <p>Join Trip Pilot today</p>

        <?php if(!empty($error)): ?>
            <div class="message error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="message success-message"><?php echo $success; ?></div>
        <?php else: ?>
        <form method="POST">
            <div class="form-row">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="tel" name="phone" placeholder="Phone Number">
            <input type="text" name="address" placeholder="Address">
            <button type="submit" name="register">Register</button>
        </form>
        <?php endif; ?>

        <div class="register-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <footer>
        Sheena Mae Jaquez || Joana Carla Gako || Zendy Mariel Dy || BSCS - 2
    </footer>
</body>
</html>
