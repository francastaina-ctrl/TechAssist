<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigirLogin();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCliente = filter_input(
        INPUT_POST,
        'id_cliente',
        FILTER_VALIDATE_INT
    );

    $tipo = trim($_POST['tipo'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $serie = trim($_POST['numero_serie'] ?? '');

    if (!$idCliente || !$tipo || !$marca || !$modelo) {
        $mensagem = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            $sql = 'INSERT INTO equipamentos
                    (id_cliente, tipo, marca, modelo, numero_serie)
                    VALUES
                    (:cliente, :tipo, :marca, :modelo, :serie)';

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'cliente' => $idCliente,
                'tipo' => $tipo,
                'marca' => $marca,
                'modelo' => $modelo,
                'serie' => $serie ?: null
            ]);

            $mensagem = 'Equipamento cadastrado!';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $mensagem = 'Cliente inexistente ou número de série duplicado.';
            } else {
                error_log($e->getMessage());
                $mensagem = 'Erro ao cadastrar equipamento.';
            }
        }
    }
}

$clientes = $pdo->query(
    'SELECT id_cliente, nome
     FROM clientes
     ORDER BY nome'
)->fetchAll();

$equipamentos = $pdo->query(
    'SELECT e.id_equipamento, e.tipo, e.marca, e.modelo,
            e.numero_serie, c.nome AS cliente
     FROM equipamentos e
     INNER JOIN clientes c
        ON c.id_cliente = e.id_cliente
     ORDER BY e.id_equipamento DESC'
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Equipamentos - TechAssist</title>
</head>
<body>

<h1>Equipamentos</h1>

<a href="dashboard.php">Voltar ao painel</a>

<p><?= htmlspecialchars($mensagem) ?></p>

<h2>Cadastrar equipamento</h2>

<form method="POST">

    <label>Cliente:</label>

    <select name="id_cliente" required>
        <option value="">Selecione um cliente</option>

        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= (int)$cliente['id_cliente'] ?>">
                <?= htmlspecialchars($cliente['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Tipo:</label>
    <input type="text" name="tipo" required>

    <br><br>

    <label>Marca:</label>
    <input type="text" name="marca" required>

    <br><br>

    <label>Modelo:</label>
    <input type="text" name="modelo" required>

    <br><br>

    <label>Número de série:</label>
    <input type="text" name="numero_serie">

    <br><br>

    <button type="submit">Cadastrar equipamento</button>

</form>

<h2>Equipamentos cadastrados</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Número de série</th>
    </tr>

    <?php foreach ($equipamentos as $equipamento): ?>
        <tr>
            <td><?= (int)$equipamento['id_equipamento'] ?></td>
            <td><?= htmlspecialchars($equipamento['cliente']) ?></td>
            <td><?= htmlspecialchars($equipamento['tipo']) ?></td>
            <td><?= htmlspecialchars($equipamento['marca']) ?></td>
            <td><?= htmlspecialchars($equipamento['modelo']) ?></td>
            <td><?= htmlspecialchars($equipamento['numero_serie'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>