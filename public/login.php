<?php session_start(); ?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title>Pøihlášení</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
<h2>Pøihlášení</h2>
<form method="POST">
    <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Heslo" required>
    <button class="btn btn-success">Pøihlásit</button>
</form>

<?php

$ch = curl_init("https://radary.furgalofteam.cz/radar/api/login.php");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $_POST,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => "/tmp/cookies.txt",
    CURLOPT_COOKIEFILE => "/tmp/cookies.txt"
]);

$response = curl_exec($ch);

echo $response;
</body>
</html>
