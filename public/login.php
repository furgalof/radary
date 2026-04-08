<?php
session_start();
?>
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

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(!$email || !$password){
        echo "<div class='text-danger mt-3'>❌ Vyplň všechna pole</div>";
    } else {

        // 🔥 JSON payload
        $payload = json_encode([
            "email" => $email,
            "password" => $password
        ]);

        // 🔥 CURL na API
        $ch = curl_init("https://www.naviox.eu/radar/api/login_api.php");

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);

        if(curl_error($ch)){
            echo "<div class='text-danger mt-3'>❌ CURL chyba: ".curl_error($ch)."</div>";
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if(!$data){
            echo "<div class='text-danger mt-3'>❌ Neplatná odpověď serveru</div>";
            exit;
        }

        // ✅ SUCCESS LOGIN
        if(isset($data['success']) && $data['success'] === true){

            $_SESSION['user_id'] = $data['user_id'];

            echo "<div class='text-success mt-3'>✅ Přihlášení úspěšné</div>";

            // redirect (volitelný)
            echo "<script>setTimeout(()=>window.location='index.php',1000)</script>";

        } else {

            $error = $data['error'] ?? 'Neznámá chyba';
            echo "<div class='text-danger mt-3'>❌ $error</div>";

        }
    }
}

?>

</body>
</html>
