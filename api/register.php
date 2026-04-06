<?php
require '../api/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$email || !$password) {
    exit("Vyplň všechna pole.");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password, verify_token)
    VALUES (?, ?, ?, ?)
");

try {
    $stmt->execute([$username, $email, $hash, $token]);
} catch (Exception $e) {
    exit("Uživatel nebo email již existuje.");
}

/*
ZDE by se normálně posílal email.
Prozatím vypíšeme verifikační odkaz:
*/

$link = "http://tvojedomena.cz/verify.php?token=$token";

echo "Registrace proběhla.<br>";
echo "Pro test klikni zde:<br>";
echo "<a href='$link'>$link</a>";