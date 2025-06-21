<?php
session_start();
require_once 'googleLib/GoogleAuthenticator.php';
$ga = new GoogleAuthenticator();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "barcode_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $otp_code = $_POST['otp_code'];

    // Find user by username
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check password
        if (password_verify($password, $row['password'])) {
            $secret = $row['auth_secret']; // This should already be in your DB
            if ($ga->verifyCode($secret, $otp_code)) {
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid Authenticator code.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
