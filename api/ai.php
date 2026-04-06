<?php
header('Content-Type: application/json');

$prompt = $_POST['prompt'] ?? '';

if(!$prompt){
    echo json_encode(["error"=>"missing prompt"]);
    exit;
}

$ch = curl_init("https://radary.furgalofteam.cz/radar/api/ai.php");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => "prompt=".urlencode($prompt),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);

if($response === false){
    echo json_encode(["error"=>"api error"]);
    exit;
}

echo $response;
