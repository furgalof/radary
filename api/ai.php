<?php

header('Content-Type: application/json');
require 'db.php';

set_time_limit(120);

$prompt = $_POST['prompt'] ?? $_GET['prompt'] ?? '';

if(!$prompt){
 echo json_encode(["error"=>"missing prompt"]);
 exit;
}


/* ================= GEOCODING ================= */

function geocodePlace($query){

    $url = "https://nominatim.openstreetmap.org/search?format=json&q=".urlencode($query);

    $opts = [
        "http" => [
            "header" => "User-Agent: RadarMapa.cz\r\n"
        ]
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if(!$response) return null;

    $data = json_decode($response, true);

    if(empty($data)) return null;

    return [
        "lat" => $data[0]["lat"],
        "lng" => $data[0]["lon"]
    ];
}


/* ================= AI (MODEL) ================= */

$data = [
 "model" => "qwen2:0.5b",
 "prompt" => "Z dopravního dotazu vytáhni silnici, kilometr NEBO místo. Vrať pouze JSON {\"road\":\"\",\"km\":\"\",\"place\":\"\"}. Dotaz: ".$prompt,
 "stream" => false
];

$ch = curl_init("http://192.168.0.82:11434/api/generate");

curl_setopt_array($ch,[
 CURLOPT_RETURNTRANSFER => true,
 CURLOPT_POST => true,
 CURLOPT_TIMEOUT => 60,
 CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
 CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);

$result = json_decode($response,true);
$aiText = $result["response"] ?? "";


/* ================= PARSE ================= */

preg_match('/\{.*\}/s',$aiText,$matches);
$parsed = json_decode($matches[0] ?? "{}",true);

$road  = $parsed["road"] ?? "";
$km    = $parsed["km"] ?? "";
$place = $parsed["place"] ?? "";


/* ================= FALLBACK ================= */

if(!$place){
    $place = $prompt;
}


/* ================= GEOCODE ================= */

$geo = geocodePlace($place);

if(!$geo){
 echo json_encode([
  "error"=>"Nepodařilo se najít místo"
 ]);
 exit;
}

$lat = floatval($geo["lat"]);
$lng = floatval($geo["lng"]);


/* ================= NAJDI OBJEKTY DO 5 KM ================= */

$stmt = $pdo->prepare("
SELECT *,
(
 6371 * ACOS(
  COS(RADIANS(:lat)) *
  COS(RADIANS(lat)) *
  COS(RADIANS(lng) - RADIANS(:lng)) +
  SIN(RADIANS(:lat)) *
  SIN(RADIANS(lat))
 )
) AS distance
FROM map_objects
WHERE lat IS NOT NULL AND lng IS NOT NULL
HAVING distance <= 4
ORDER BY distance ASC
LIMIT 10
");

$stmt->execute([
 ":lat"=>$lat,
 ":lng"=>$lng
]);

$objects = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ================= VÝSTUP ================= */

echo json_encode([
 "place"=>$place,
 "base"=>[
  "lat"=>$lat,
  "lng"=>$lng
 ],
 "objects"=>$objects
]);