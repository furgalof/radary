<?php

require 'db.php';

ignore_user_abort(true);
set_time_limit(300);

echo "===== START =====\n";

// složka cache
$cacheDir = __DIR__ . "/cache/";
if(!is_dir($cacheDir)){
    mkdir($cacheDir,0777,true);
}

// ROTACE
$limit = 5;
$offsetFile = __DIR__."/offset.txt";
$offset = file_exists($offsetFile) ? (int)file_get_contents($offsetFile) : 0;

// načtení kamer
$stmt = $pdo->prepare("
SELECT * FROM map_objects 
WHERE type='Kamera' 
AND camera_url IS NOT NULL
LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit',$limit,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();

$cameras = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Offset: $offset | Kamer: ".count($cameras)."\n";


// 🔥 DETEKCE POHYBU (ROI + grayscale)
function imageDifference($img1, $img2){

    $w = imagesx($img1);
    $h = imagesy($img1);

    $diff = 0;

    $xStart = (int)($w * 0.5);
    $xEnd   = (int)$w;

    $yStart = (int)($h * 0.3);
    $yEnd   = (int)($h * 0.9);

    for($x = $xStart; $x < $xEnd; $x += 10){
        for($y = $yStart; $y < $yEnd; $y += 10){

            $rgb1 = imagecolorat($img1,$x,$y);
            $rgb2 = imagecolorat($img2,$x,$y);

            $g1 = (($rgb1 >> 16) & 0xFF)*0.3 + (($rgb1 >> 8) & 0xFF)*0.59 + ($rgb1 & 0xFF)*0.11;
            $g2 = (($rgb2 >> 16) & 0xFF)*0.3 + (($rgb2 >> 8) & 0xFF)*0.59 + ($rgb2 & 0xFF)*0.11;

            if(abs($g1 - $g2) > 25){
                $diff++;
            }

        }
    }

    return $diff;
}


// 🔥 DETEKCE PROVOZU (jsou tam auta?)
function hasTraffic($img){

    $w = imagesx($img);
    $h = imagesy($img);

    $count = 0;

    for($x = (int)($w*0.5); $x < $w; $x += 10){
        for($y = (int)($h*0.3); $y < (int)($h*0.9); $y += 10){

            $rgb = imagecolorat($img,$x,$y);

            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $brightness = ($r + $g + $b) / 3;

            if($brightness > 180){
                $count++;
            }

        }
    }

    return $count;
}


// 🔥 HLAVNÍ LOOP
foreach($cameras as $cam){

    echo "\n--- Kamera ID: ".$cam['id']." ---\n";

    $imgUrl = str_replace('&amp;', '&', $cam['camera_url']);

    $ch = curl_init($imgUrl);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10
    ]);

    $imageData = curl_exec($ch);

    if(!$imageData){
        echo "❌ Nelze stáhnout\n";
        continue;
    }

    echo "✔ Staženo\n";

    $current = @imagecreatefromstring($imageData);
    if(!$current){
        echo "❌ Nevalidní obrázek\n";
        continue;
    }

    $trafficLevel = hasTraffic($current);
    echo "TRAFFIC: ".$trafficLevel."\n";

    $cacheFile = $cacheDir.$cam['id'].".jpg";
    $stateFile = $cacheDir."state_".$cam['id'].".txt";

    $state = file_exists($stateFile) ? (int)file_get_contents($stateFile) : 0;

    if(file_exists($cacheFile)){

        $old = @imagecreatefromjpeg($cacheFile);

        if($old){

            $diff = imageDifference($current,$old);

            echo "DIFF: ".$diff."\n";

            // 🔥 FINÁLNÍ LOGIKA
            if($trafficLevel > 50 && $diff > 20 && $diff < 200){

                $state++;
                echo "🟠 MOZNA KOLONA | STATE: $state\n";

                // bonus: úplně stojí
                if($diff < 20){
                    $state += 2;
                }

                if($state >= 3){

                    echo "🚗 KOLONA POTVRZENA\n";

                    $stmt = $pdo->prepare("
					INSERT INTO map_objects (name,type,lat,lng,description,status,expires_at)
					VALUES ('🚗 Kolona','Kolona',?,?,?,'approved',DATE_ADD(NOW(), INTERVAL 15 MINUTE))
					ON DUPLICATE KEY UPDATE expires_at=DATE_ADD(NOW(), INTERVAL 15 MINUTE)
					");

                    $stmt->execute([
                        $cam['lat'],
                        $cam['lng'],
                        "Diff=".$diff." | traffic=".$trafficLevel." | state=".$state
                    ]);

                }

            } else {

                echo "🟢 PROVOZ / RESET\n";
                $state = 0;

            }

        }

    }

    imagejpeg($current,$cacheFile,70);
    file_put_contents($stateFile,$state);
}


// 🔁 rotace
$newOffset = $offset + $limit;

$total = $pdo->query("SELECT COUNT(*) FROM map_objects WHERE type='Kamera'")->fetchColumn();

if($newOffset >= $total){
    $newOffset = 0;
}

file_put_contents($offsetFile,$newOffset);

echo "Nový offset: $newOffset\n";


// 🧹 mazání kolon
$pdo->query("DELETE FROM map_objects WHERE type='Kolona' AND expires_at < NOW()");

echo "===== KONEC =====\n";