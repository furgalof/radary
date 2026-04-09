<?php

try {
    $pdo = new PDO("pgsql:host=db;dbname=radar", "radar", "radar");

    $pdo->exec("CREATE TABLE IF NOT EXISTS test (id SERIAL PRIMARY KEY, msg TEXT)");

    $pdo->exec("INSERT INTO test (msg) VALUES ('DB funguje')");

    $stmt = $pdo->query("SELECT * FROM test ORDER BY id DESC LIMIT 1");

    $row = $stmt->fetch();

    echo "OK: " . $row['msg'];

} catch(Exception $e){
    echo "ERROR: " . $e->getMessage();
}
