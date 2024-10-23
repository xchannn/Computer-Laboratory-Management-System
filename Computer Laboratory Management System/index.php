<?php
session_start();

if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'assistant') {
        header("Location: assistant/dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'includes/db.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL Injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // Fetch user data
    $sql = "SELECT id, username, role FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['id'] = $row['id']; // Store user ID in session
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: assistant/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style2.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('images/loginbg.jpg');
            background-size: cover;
            font-family: sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 25rem;
            padding: 2.5rem;
            box-sizing: border-box;
            border-radius: 0.625rem;
            
        }

        .box h2 {
            margin: 0 0 1.875rem;
            padding: 0;
            color: #F9F6F3; 
            text-align: center;
        }

        .box .inputBox {
            position: relative;
        }

        .box .inputBox input {
            width: 100%;
            padding: 0.625rem 0;
            font-size: 1rem;
            color: #F9F6F3; 
            letter-spacing: 0.062rem;
            margin-bottom: 1.875rem;
            border: none;
            border-bottom: 0.065rem solid #F9F6F3; 
            outline: none;
            background: transparent;
        }

        .box .inputBox label {
            position: absolute;
            top: 0;
            left: 0;
            padding: 0.625rem 0;
            font-size: 1rem;
            color: #F9F6F3; 
            pointer-events: none;
            transition: 0.5s;
        }

        .box .inputBox input:focus ~ label,
        .box .inputBox input:valid ~ label,
        .box .inputBox input:not([value=""]) ~ label {
            top: -1.125rem;
            left: 0;
            color: #D9DFE3;
            font-size: 0.75rem;
        }

        .box input[type="submit"] {
            border: none;
            outline: none;
            color: #fff;
            background-color: #2C2828;
            padding: 0.625rem 1.25rem;
            cursor: pointer;
            border-radius: 0.312rem;
            font-size: 1rem;
        }

        .box input[type="submit"]:hover {
            background-color: #304851;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="inputBox">
                <input type="text" name="username" required onkeyup="this.setAttribute('value', this.value);" value="">
                <label>Username</label>
            </div>
            <div class="inputBox">
                <input type="password" name="password" required onkeyup="this.setAttribute('value', this.value);" value="">
                <label>Password</label>
            </div>
            <input type="submit" name="sign-in" value="Sign In">
        </form>
    </div>
</body>
</html>
