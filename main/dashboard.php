<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigirLogin();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>TechAssist - Painel</title>
</head>
<body>

<h1>TechAssist</h1>

<p>
    Bem-vindo(a),
    <strong>
        <?= htmlspecialchars($_SESSION['nome_usuario']) ?>
    </strong>!
</p>

<h2>Menu principal</h2>

<ul>
    <li><a href="clientes.php">Clientes</a></li>
    <li><a href="equipamentos.php">Equipamentos</a></li>
    <li><a href="ordens_servico.php">Ordens de serviço</a></li>
</ul>

<a href="logout.php">Sair do sistema</a>

</body>
</html>