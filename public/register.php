<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Registrace - RadarMapa.cz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h2>Registrace</h2>

<form id="registerForm">
    <input class="form-control mb-2" name="username" placeholder="Uživatelské jméno" required>
    <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
    <input class="form-control mb-2" type="password" name="password" placeholder="Heslo" required>
    <button class="btn btn-primary">Registrovat</button>
</form>

<div id="msg" class="mt-3"></div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('/radar/api/register.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('msg').innerHTML = data;
    });
});
</script>

</body>
</html>