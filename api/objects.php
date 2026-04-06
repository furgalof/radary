<?php
header('Content-Type: application/json');
require 'db.php';

$input = json_decode(file_get_contents("php://input"), true);

$minLat = $input['minLat'] ?? null;
$maxLat = $input['maxLat'] ?? null;
$minLng = $input['minLng'] ?? null;
$maxLng = $input['maxLng'] ?? null;

$country = strtoupper($input['country'] ?? '');
$region  = strtoupper($input['region'] ?? '');

if(!$minLat || !$maxLat || !$minLng || !$maxLng){
    echo json_encode([]);
    exit;
}

$sql = "
SELECT id,name,description,lat,lng,type,camera_url,youtube_url,reload_url,coordinates
FROM map_objects
WHERE status='approved'
AND (expires_at IS NULL OR expires_at > NOW())

AND (
    (type='Usek' AND coordinates IS NOT NULL)
    OR
    (type!='Usek' AND lat BETWEEN :minLat AND :maxLat AND lng BETWEEN :minLng AND :maxLng)
)
";

$params = [
    ':minLat'=>$minLat,
    ':maxLat'=>$maxLat,
    ':minLng'=>$minLng,
    ':maxLng'=>$maxLng
];


// 🔥 KLÍČOVÁ LOGIKA
// události (udalost + Kolona) IGNORUJÍ filtr
if(!empty($country)){
    $sql .= " AND (type IN ('udalost','Kolona') OR country = :country)";
    $params[':country'] = $country;
}

if(!empty($region)){
    $sql .= " AND (type IN ('udalost','Kolona') OR region = :region)";
    $params[':region'] = $region;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));