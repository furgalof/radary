<?php session_start(); ?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title>Přihlášení</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h2>Přihlášení</h2>

<form method="POST">
    <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Heslo" required>
    <button class="btn btn-success">Přihlásit</button>
</form>

<?php

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $ch = curl_init("https://www.naviox.eu/radar/api/login_api.php");

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $_POST,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => __DIR__."/cookies.txt",
        CURLOPT_COOKIEFILE => __DIR__."/cookies.txt"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo "<div class='mt-3'>$response</div>";
}

?>

</body>
</html>
