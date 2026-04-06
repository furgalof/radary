<?php

$host = "192.168.0.70";
$port = "3307";
$db   = "radarmapa";
$user = "root";
$pass = "Furgalof2020";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB chyba");
}