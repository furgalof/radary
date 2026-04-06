<?php
session_start();
if(!isset($_SESSION['user_id'])||$_SESSION['role']!=='admin') exit("Přístup zakázán.");
require '../api/db.php';
$stmt=$pdo->query("SELECT m.id,m.name,m.type,m.description,u.username FROM map_objects m JOIN users u ON m.created_by=u.id WHERE status='pending'");
$objects=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>Admin panel – Pending radary</h2>
<table border=1 cellpadding=5>
<tr><th>ID</th><th>Název</th><th>Typ</th><th>Popis</th><th>Uživatel</th><th>Akce</th></tr>
<?php foreach($objects as $o): ?>
<tr>
<td><?=$o['id']?></td>
<td><?=$o['name']?></td>
<td><?=$o['type']?></td>
<td><?=$o['description']?></td>
<td><?=$o['username']?></td>
<td>
<a href="approve.php?id=<?=$o['id']?>">Schválit</a> |
<a href="reject.php?id=<?=$o['id']?>">Zamítnout</a>
</td>
</tr>
<?php endforeach; ?>
</table>