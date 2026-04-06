<?php

$ch = curl_init("https://radary.furgalofteam.cz/radar/api/add_object.php");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $_POST,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => "/tmp/cookies.txt",
    CURLOPT_COOKIEFILE => "/tmp/cookies.txt"
]);

echo curl_exec($ch);
