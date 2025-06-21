<?php
$plain_password = "admin123";
$hashed = password_hash($plain_password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed;
?>
