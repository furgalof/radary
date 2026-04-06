<?php
session_start();
if(!isset($_SESSION['user_id'])) exit("Musíš být přihlášen.");

require 'db.php';

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$description = trim($_POST['description'] ?? '');
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

if(!$name || !$type || !$lat || !$lng) exit("Vyplň všechna pole.");

// ===== ULOŽENÍ DO DB =====
$stmt = $pdo->prepare("
INSERT INTO map_objects 
(name,description,type,lat,lng,status,created_by) 
VALUES (?,?,?,?,?,'pending',?)
");

$stmt->execute([
    $name,
    $description,
    $type,
    $lat,
    $lng,
    $_SESSION['user_id']
]);


// ===== DISCORD BOT =====
$data = [
    "type" => $type,
    "name" => $name,
    "description" => $description,
    "lat" => $lat,
    "lng" => $lng
];

$ch = curl_init("http://127.0.0.1:3000/notify");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 2 // aby se web nesekal když bot nejede
]);

curl_exec($ch);
curl_close($ch);


// ===== ODPOVĚĎ =====
echo "Radar přidán, čeká na schválení.";