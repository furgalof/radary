<?php

$ch = curl_init("https://radary.furgalofteam.cz/radar/api/add_segment.php");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $_POST,

    // 🔥 důležité pro login
    CURLOPT_COOKIEJAR => "/tmp/cookies.txt",
    CURLOPT_COOKIEFILE => "/tmp/cookies.txt"
]);

$response = curl_exec($ch);

if($response === false){
    http_response_code(500);
    echo "API error";
    exit;
}

echo $response;
