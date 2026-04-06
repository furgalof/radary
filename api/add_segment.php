<?php
session_start();

if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    exit("Musíš být přihlášen.");
}

require "db.php";

$name = trim($_POST['name'] ?? '');
$type = $_POST['type'] ?? 'Usek';
$coordinates = $_POST['coordinates'] ?? '';

if(!$name || !$coordinates){
    http_response_code(400);
    exit("Chybí data.");
}

try{

$stmt = $pdo->prepare("
INSERT INTO map_objects
(name,type,coordinates,created_at)
VALUES
(:name,:type,:coordinates,NOW())
");

$stmt->execute([
":name"=>$name,
":type"=>$type,
":coordinates"=>$coordinates
]);

echo "Úsek byl přidán.";

}catch(PDOException $e){

http_response_code(500);
echo "Chyba DB: ".$e->getMessage();

}