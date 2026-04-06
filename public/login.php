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
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require '../api/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$_POST['email']]);
    $user=$stmt->fetch();
    if(!$user||!password_verify($_POST['password'],$user['password'])) echo "<div class='text-danger mt-2'>Špatné údaje</div>";
    elseif(!$user['is_verified']) echo "<div class='text-danger mt-2'>Neovìøený email</div>";
    elseif($user['status']==='banned') echo "<div class='text-danger mt-2'>Úèet zablokován</div>";
    else {
        $_SESSION['user_id']=$user['id'];
        $_SESSION['role']=$user['role'];
        $pdo->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
        header("Location: profile.php"); exit;
    }
}
?>
</body>
</html>