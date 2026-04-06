<?php
header('Content-Type: application/json');

$input = file_get_contents("php://input");

// 🔥 zavolá TVOJE API
$ch = curl_init("https://radary.furgalofteam.cz/radar/api/objects.php");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $input,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_TIMEOUT => 5
]);

$response = curl_exec($ch);

if($response === false){
    echo json_encode([]);
    exit;
}

echo $response;
