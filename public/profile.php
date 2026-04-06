<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Vítej na profilu</h2>
<p>Jsi přihlášen.</p>
<a href="logout.php">Odhlásit</a>