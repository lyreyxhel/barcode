<?php
session_start();
date_default_timezone_set('Asia/Manila');
include('includes/header.php');
include('includes/navbar.php');
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">admin</span>
                        <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>
        </nav>

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Students</h1>
                <a href="#" id="start-scanner-btn" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-barcode fa-sm text-white-50"></i> Scan Barcode
                </a>
            </div>

            <!-- Clear Table Button -->
            <form action="clear.php" method="POST" onsubmit="return confirm('Are you sure you want to clear attendance data?')">
                <button type="submit" class="btn btn-warning mb-3">🧹 Clear Table Information</button>
            </form>

            <!-- Status Message -->
            <?php
            if (isset($_GET['status']) && isset($_GET['scanned'])) {
                $msg = "";
                $scanned = htmlspecialchars($_GET['scanned']);
                switch ($_GET['status']) {
                    case "in": $msg = "🟢 Time In recorded for Student #$scanned"; break;
                    case "out": $msg = "🔵 Time Out recorded for Student #$scanned"; break;
                    case "done": $msg = "⚠️ Already scanned in and out for Student #$scanned"; break;
                    case "notfound": $msg = "⛔ Student #$scanned not found."; break;
                    case "invalid": $msg = "⛔ Cannot scan Student #$scanned — status not Enrolled."; break;
                }
                echo "<div class='alert alert-info text-center'>$msg</div>";
            }
            ?>

            <!-- Scanner Section -->
            <div id="scanner-section" class="mb-4" style="display: none;">
                <h5 class="text-center">Scan Student Barcode</h5>
                <p id="scanner-status" class="text-center text-info">Waiting to start scanner...</p>
                <div class="d-flex justify-content-center">
                    <div id="reader" style="width:300px;"></div>
                </div>
                <form method="POST" action="process.php" class="mt-3 text-center">
                    <div class="form-group">
                        <label for="barcode">Scanned Barcode:</label>
                        <input type="text" class="form-control text-center" id="barcode" name="barcode" readonly required>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="button" class="btn btn-danger ml-2" id="stop-scanning-btn">Stop Scanning</button>
                </form>
            </div>

            <!-- Student Table -->
            <?php
            $conn = new mysqli("localhost", "root", "", "barcode_system");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $students = [];

            if (isset($_SESSION['scanned']) && count($_SESSION['scanned']) > 0) {
                $placeholders = implode(',', array_fill(0, count($_SESSION['scanned']), '?'));
                $stmt = $conn->prepare("SELECT * FROM students WHERE student_number IN ($placeholders)");
                $stmt->bind_param(str_repeat('s', count($_SESSION['scanned'])), ...$_SESSION['scanned']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $students[] = $row;
                }
            }
            ?>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Student Number</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $row): ?>
                            <tr>

                                <td><?= $row['student_number'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['Gender'] ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['time_in'] ?></td>
                                <td><?= $row['time_out'] ?></td>
                                <td><?= $row['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">Please scan a barcode to show student record.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div> <!-- End container-fluid -->

    </div> <!-- End content -->

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>© 2025 Barcode Attendance System - Developed by Fritz Ria Lyreyzhel Casemero</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->
</div> <!-- End content-wrapper -->

<?php
include('includes/scripts.php');
?>

<!-- Barcode Scanner Script -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrCode;
let scannerRunning = false;

const startBtn = document.getElementById("start-scanner-btn");
const stopBtn = document.getElementById("stop-scanning-btn");
const scannerSection = document.getElementById("scanner-section");
const barcodeField = document.getElementById("barcode");
const statusMsg = document.getElementById("scanner-status");

startBtn.addEventListener("click", function (e) {
    e.preventDefault();
    scannerSection.style.display = "block";
    statusMsg.innerText = "🟢 Initializing camera...";

    if (!scannerRunning) {
        html5QrCode = new Html5Qrcode("reader");
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                html5QrCode.start(
                    cameras[0].id,
                    { fps: 10, qrbox: 250 },
                    decodedText => {
                        barcodeField.value = decodedText;
                        statusMsg.innerText = "✅ Barcode scanned: " + decodedText;
                        html5QrCode.stop();
                        scannerRunning = false;
                        document.getElementById("reader").innerHTML = "<p class='text-success'>Scan complete.</p>";
                    },
                    error => {
                        statusMsg.innerText = "🔄 Scanning...";
                    }
                );
                scannerRunning = true;
                statusMsg.innerText = "📷 Scanner ready. Waiting for barcode...";
            }
        }).catch(err => {
            console.error("Camera error:", err);
            statusMsg.innerText = "⛔ Error starting camera: " + err;
        });
    }
});

stopBtn.addEventListener("click", () => {
    if (scannerRunning) {
        html5QrCode.stop().then(() => {
            document.getElementById("reader").innerHTML = "<p class='text-muted'>Camera stopped.</p>";
            statusMsg.innerText = "🛑 Scanner stopped.";
            scannerRunning = false;
            scannerSection.style.display = "none";
        }).catch(err => {
            console.error("Error stopping camera:", err);
            statusMsg.innerText = "⚠️ Error stopping scanner.";
        });
    } else {
        scannerSection.style.display = "none";
        statusMsg.innerText = "";
    }
});
</script>