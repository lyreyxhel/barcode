<?php
include('includes/header.php');
session_start();
?>

<div class="container">
    <!-- Outer Row -->
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-12 col-md-6">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="p-5">
                               <div class="text-center mb-4">
                                <img src="bg.png" alt="Logo" style="width: 150px; height: auto; margin-bottom: 15px;">
                                <h1 class="h4 text-gray-900">LOGIN</h1>
                            </div>


                                <?php
                                if (isset($_SESSION['error'])) {
                                    echo "<div class='alert alert-danger text-center'>" . $_SESSION['error'] . "</div>";
                                    unset($_SESSION['error']);
                                }
                                ?>

                                <form class="user" action="code.php" method="POST">
                                    <div class="form-group">
                                        <input type="text" name="username" class="form-control form-control-user" placeholder="Username"required> 
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user" placeholder="Password" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="otp_code" class="form-control form-control-user" placeholder="Authenticator Code" required>
                                    </div>
                                    <button type="submit" name="login_btn" class="btn btn-primary btn-user btn-block">Login</button>
                                </form>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/scripts.php'); ?>
