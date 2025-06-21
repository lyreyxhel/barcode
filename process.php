<?php
session_start();
date_default_timezone_set('Asia/Manila');


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];

    $conn = new mysqli("localhost", "root", "", "barcode_system");

    $sql = "SELECT * FROM students WHERE student_number = '$barcode'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        //Store in session
        if (!isset($_SESSION['scanned'])) {
            $_SESSION['scanned'] = [];
        }
        if (!in_array($barcode, $_SESSION['scanned'])) {
            $_SESSION['scanned'][] = $barcode;
        }

        //Replace this logic with your attendance logic
        $today = date("Y-m-d");
        $timeNow = date("H:i:s");

        $student = $result->fetch_assoc();
        $status = "";

        if (empty($student['date']) || $student['date'] !== $today) {
            // First time in today – set date and time_in only
            $conn->query("UPDATE students SET date='$today', time_in='$timeNow', time_out=NULL WHERE student_number='$barcode'");
            $status = "in";
        } elseif (empty($student['time_out'])) {
            // Already timed in, now time out
            $conn->query("UPDATE students SET time_out='$timeNow' WHERE student_number='$barcode'");
            $status = "out";
        } else {
            // Already timed in and out
            $status = "done";
        }


        header("Location: index.php?status=$status&scanned=$barcode");
    } else {
        header("Location: index.php?status=notfound&scanned=$barcode");
    }

    $conn->close();
}
