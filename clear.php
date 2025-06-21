<?php
$conn = new mysqli("localhost", "root", "", "barcode_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear only attendance-related fields
$reset = "UPDATE students 
          SET date = NULL, 
              time_in = NULL, 
              time_out = NULL, 
              status = 'Enrolled'";

if ($conn->query($reset) === TRUE) {
    // Also clear session scanned data
    session_start();
    unset($_SESSION['scanned']);

    header("Location: index.php");
    exit();
} else {
    echo "Error resetting data: " . $conn->error;
}

$conn->close();
?>
