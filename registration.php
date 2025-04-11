<?php    
    include 'connect.php';    
?>

<!-- Styling for the Registration Page -->
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f6f9;
        color: #333;
    }

    .header {
        background-color: #0a316c;
        padding: 20px 0;
        text-align: center;
        color: #fcc931;
        position: relative;
    }

    .header h2 {
        margin: 0;
    }

    .header .back-btn {
        position: absolute;
        left: 20px;
        top: 20px;
        background-color: #e77a2c;
        color: white;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 6px;
        text-decoration: none;
    }

    .header .back-btn:hover {
        background-color: #1765c0;
    }

    .form-container {
        width: 60%;
        margin: 20px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-container pre {
        margin: 0;
        font-size: 16px;
    }

    .form-container input[type="text"],
    .form-container input[type="password"],
    .form-container select {
        width: 100%;
        padding: 12px;
        margin: 8px 0 16px;
        border: 1px solid #cad3da;
        border-radius: 6px;
        font-size: 16px;
        color: #333;
    }

    .form-container select {
        background-color: #f9f9f9;
    }

    .form-container input[type="submit"] {
        background-color: #e77a2c;
        color: #fff;
        border: none;
        padding: 14px 20px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 6px;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .form-container input[type="submit"]:hover {
        background-color: #1765c0;
    }

    .form-container label {
        font-size: 18px;
        color: #0a316c;
        margin-bottom: 6px;
        display: inline-block;
    }

    .form-container #employeeFields, .form-container #passengerFields {
        margin-top: 20px;
        background-color: #f0f6fc;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #cad3da;
    }

    .form-container #employeeFields select,
    .form-container #passengerFields input {
        background-color: #fff;
    }

    .form-container #driverFields {
        margin-top: 10px;
        background-color: #e0f2f7;
    }
</style>

<div class="header">
    <a href="home.php" class="back-btn">Back to Home</a>
    <h2>User Registration Page</h2>
</div>

<div class="form-container">
    <form method="post">
        <label for="txtfirstname">Firstname:</label>
        <input type="text" name="txtfirstname" id="txtfirstname" required>

        <label for="txtlastname">Lastname:</label>
        <input type="text" name="txtlastname" id="txtlastname" required>

        <label for="txtusername">Username:</label>
        <input type="text" name="txtusername" id="txtusername" required>

        <label for="txtpassword">Password:</label>
        <input type="password" name="txtpassword" id="txtpassword" required>

        <label for="accountType">Account Type:</label>
        <select name="txtaccountType" id="accountType" onchange="toggleFields()" required>
            <option value="">----</option>
            <option value="employee">Employee</option>
            <option value="passenger">Passenger</option>
            <option value="both">Employee and Passenger</option>
        </select>

        <div id="employeeFields" style="display:none;">
            <label for="employeeType">Employee Type:</label>
            <select name="txtemployeeType" id="employeeType" onchange="toggleDriverFields()" required>
                <option value="">----</option>
                <option value="driver">Driver</option>
                <option value="conductor">Conductor</option>
                <option value="admin">Admin</option>
            </select>
            <div id="driverFields" style="display:none;">
                <label for="txtlicenseNumber">License Number:</label>
                <input type="text" name="txtlicenseNumber" id="txtlicenseNumber" required>
            </div>
        </div>

        <div id="passengerFields" style="display:none;">
            <label for="txtcontactNumber">Contact Number:</label>
            <input type="text" name="txtcontactNumber" id="txtcontactNumber" required>
        </div>

        <input type="submit" name="btnRegister" value="Register">
    </form>
</div>

<script>
    // Function to toggle the fields based on Account Type (Employee, Passenger, or Both)
    function toggleFields() {
        var accountType = document.getElementById('accountType').value;
        var employeeFields = document.getElementById('employeeFields');
        var passengerFields = document.getElementById('passengerFields');
        
        // If 'Employee' or 'Both' is selected, show employee fields
        if (accountType == 'employee' || accountType == 'both') {
            employeeFields.style.display = 'block';
        } else {
            employeeFields.style.display = 'none';
            toggleDriverFields(); // Hide driver fields if not selected
        }

        // If 'Passenger' or 'Both' is selected, show passenger fields
        if (accountType == 'passenger' || accountType == 'both') {
            passengerFields.style.display = 'block';
        } else {
            passengerFields.style.display = 'none';
        }
    }

    // Function to toggle the driver-specific fields
    function toggleDriverFields() {
        var employeeType = document.getElementById('employeeType').value;
        var driverFields = document.getElementById('driverFields');
        
        if (employeeType == 'driver') {
            driverFields.style.display = 'block';
        } else {
            driverFields.style.display = 'none';
        }
    }
</script>

<?php  
    if(isset($_POST['btnRegister'])){        
        // Retrieve data from the form
        $fname = $_POST['txtfirstname'];        
        $lname = $_POST['txtlastname'];
        $uname = $_POST['txtusername'];        
        $pword = $_POST['txtpassword'];  
        $hashedpw = password_hash($pword, PASSWORD_DEFAULT);
        
        $accountType = $_POST['txtaccountType'];

        // For employee
        $employeeType = $_POST['txtemployeeType'] ?? null;
        $licenseNumber = $_POST['txtlicenseNumber'] ?? null;

        // For passenger
        $contactNumber = $_POST['txtcontactNumber'] ?? null;

        // Save data to tbluser
        $sql1 = "INSERT INTO tbluser (firstName, lastName, username, password, isEmployee, isPassenger) 
                 VALUES ('".$fname."', '".$lname."', '".$uname."', '".$hashedpw."', 
                         '".($accountType == 'employee' || $accountType == 'both' ? 1 : 0)."', 
                         '".($accountType == 'passenger' || $accountType == 'both' ? 1 : 0)."')";
        mysqli_query($connection, $sql1);
                
        $last_id = mysqli_insert_id($connection); // Get last inserted user ID

        // If the account is of type Employee, insert additional data
        if ($accountType == 'employee' || $accountType == 'both') {
            // Check if the employee is both driver and conductor
            if ($employeeType == 'driver') {
                // Insert driver data
                $sql2 = "INSERT INTO tbldriver (driverID, licenseNumber) 
                         VALUES ('".$last_id."', '".$licenseNumber."')";
                mysqli_query($connection, $sql2);
            } else if ($employeeType == 'conductor') {
                // Insert conductor data
                $sql2 = "INSERT INTO tblconductor (conductorID) 
                         VALUES ('".$last_id."')";
                mysqli_query($connection, $sql2);
            } else if ($employeeType == 'admin') {
                // Insert admin employee data
                $sql2 = "INSERT INTO tblemployee (employeeID, userType, uid) 
                         VALUES (NULL, 'admin', '".$last_id."')";
                mysqli_query($connection, $sql2);
            }
        }

        // If the account is of type Passenger, insert additional data
        if ($accountType == 'passenger' || $accountType == 'both') {
            // Insert passenger data
            $sql3 = "INSERT INTO tblpassenger (passengerID, contactNumber) 
                     VALUES ('".$last_id."', '".$contactNumber."')";
            mysqli_query($connection, $sql3);
        }

        echo "<script>alert('Registration successful!'); window.location = 'home.php';</script>";
    }
?>
