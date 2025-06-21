<?php
require_once 'googleLib/GoogleAuthenticator.php';
$ga = new GoogleAuthenticator();

$secret = $ga->createSecret();
$username = 'admin';
$issuer = 'DBMGT'; // change this to your system name

$qrCodeUrl = "otpauth://totp/{$issuer}:{$username}?secret={$secret}&issuer={$issuer}";
$qrCodeImage = "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($qrCodeUrl);

echo "<h3>Scan this QR with Duo/Google Authenticator</h3>";
echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl) . '">';
echo "<p>Secret: <strong>$secret</strong></p>";
echo "<p><em>Save this in your DB under 'auth_secret' for user 'admin'</em></p>";
