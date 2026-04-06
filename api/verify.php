<?php
require '../api/db.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE verify_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Neplatný token.");
}

$pdo->prepare("
    UPDATE users 
    SET is_verified = 1, verify_token = NULL 
    WHERE id = ?
")->execute([$user['id']]);

echo "Email byl ověřen. Můžeš se přihlásit.";