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

    $idEquipamento = filter_input(
        INPUT_POST,
        'id_equipamento',
        FILTER_VALIDATE_INT
    );

    $descricao = trim($_POST['descricao'] ?? '');

    if (!$idCliente || !$idEquipamento || $descricao === '') {
        $mensagem = 'Preencha todos os campos.';
    } else {
        // Confirma que o equipamento pertence ao cliente selecionado.
        $stmt = $pdo->prepare(
            'SELECT id_equipamento
             FROM equipamentos
             WHERE id_equipamento = :equipamento
               AND id_cliente = :cliente'
        );

        $stmt->execute([
            'equipamento' => $idEquipamento,
            'cliente' => $idCliente
        ]);

        if (!$stmt->fetch()) {
            $mensagem = 'O equipamento não pertence ao cliente selecionado.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO ordens_servico
                (id_cliente, id_equipamento, id_usuario,
                 descricao_problema, status)
                VALUES
                (:cliente, :equipamento, :usuario,
                 :descricao, :status)'
            );

            $stmt->execute([
                'cliente' => $idCliente,
                'equipamento' => $idEquipamento,
                'usuario' => $_SESSION['id_usuario'],
                'descricao' => $descricao,
                'status' => 'Aberta'
            ]);

            $mensagem = 'Ordem de serviço cadastrada!';
        }
    }
}

$clientes = $pdo->query(
    'SELECT id_cliente, nome
     FROM clientes
     ORDER BY nome'
)->fetchAll();

$equipamentos = $pdo->query(
    'SELECT id_equipamento, id_cliente, tipo, marca, modelo
     FROM equipamentos
     ORDER BY modelo'
)->fetchAll();

$sql = 'SELECT
            os.id_ordem,
            os.data_abertura,
            os.descricao_problema,
            os.status,
            os.valor,
            c.nome AS cliente,
            e.tipo,
            e.marca,
            e.modelo,
            u.nome AS responsavel
        FROM ordens_servico os
        INNER JOIN clientes c
            ON c.id_cliente = os.id_cliente
        INNER JOIN equipamentos e
            ON e.id_equipamento = os.id_equipamento
        INNER JOIN usuarios u
            ON u.id_usuario = os.id_usuario
        ORDER BY os.data_abertura DESC';

$ordens = $pdo->query($sql)->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ordens de serviço - TechAssist</title>
</head>
<body>

<h1>Ordens de serviço</h1>

<a href="dashboard.php">Voltar ao painel</a>

<p><?= htmlspecialchars($mensagem) ?></p>

<h2>Nova ordem de serviço</h2>

<form method="POST">

    <label>Cliente:</label>

    <select name="id_cliente" id="cliente" required>
        <option value="">Selecione</option>

        <?php foreach ($clientes as $cliente): ?>
            <option value="<?= (int)$cliente['id_cliente'] ?>">
                <?= htmlspecialchars($cliente['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Equipamento:</label>

    <select name="id_equipamento" id="equipamento" required>
        <option value="">Selecione um equipamento</option>

        <?php foreach ($equipamentos as $equipamento): ?>
            <option
                value="<?= (int)$equipamento['id_equipamento'] ?>"
                data-cliente="<?= (int)$equipamento['id_cliente'] ?>"
            >
                <?= htmlspecialchars(
                    $equipamento['marca'] . ' ' .
                    $equipamento['modelo'] . ' (' .
                    $equipamento['tipo'] . ')'
                ) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>Descrição do problema:</label>

    <textarea name="descricao" required></textarea>

    <br><br>

    <button type="submit">Cadastrar ordem</button>

</form>

<h2>Ordens cadastradas</h2>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>Número</th>
            <th>Data de abertura</th>
            <th>Cliente</th>
            <th>Equipamento</th>
            <th>Problema</th>
            <th>Status</th>
            <th>Valor</th>
            <th>Responsável</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($ordens as $ordem): ?>
            <tr>
                <td><?= (int)$ordem['id_ordem'] ?></td>

                <td>
                    <?= htmlspecialchars($ordem['data_abertura']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ordem['cliente']) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $ordem['marca'] . ' ' . $ordem['modelo']
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ordem['descricao_problema']) ?>
                </td>

                <td><?= htmlspecialchars($ordem['status']) ?></td>

                <td>
                    R$ <?= number_format(
                        (float)$ordem['valor'],
                        2,
                        ',',
                        '.'
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($ordem['responsavel']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
const cliente = document.getElementById('cliente');
const equipamento = document.getElementById('equipamento');

function filtrarEquipamentos() {
    const idCliente = cliente.value;

    for (const opcao of equipamento.options) {
        if (opcao.value === '') {
            opcao.hidden = false;
            continue;
        }

        opcao.hidden = opcao.dataset.cliente !== idCliente;
    }

    equipamento.value = '';
}

cliente.addEventListener('change', filtrarEquipamentos);
filtrarEquipamentos();
</script>

</body>
</html>