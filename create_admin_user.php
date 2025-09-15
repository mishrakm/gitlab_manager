<?php
// Run this script once to create an initial admin user, then delete it for security.
require_once 'connection.php';

$username = 'dev';
$password = 'dev123'; // Change this after first login
$usertype = 'developer';

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, password, usertype) VALUES (?, ?, ?)");
$stmt->execute([$username, $hash, $usertype]);
echo "Admin user created. Username: admin, Password: admin123";
