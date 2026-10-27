<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigirLogin();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');
        $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (
            $nome === '' ||
            strlen($cpf) !== 11 ||
            $telefone === ''
        ) {
            $mensagem = 'Preencha os dados corretamente.';
        } else {
            // Segredo apenas demonstrativo.
            // Em produção, carregue-o de variável de ambiente.
            $chave = getenv('CPF_HASH_KEY');

            if (!$chave) {
                http_response_code(500);
                exit('Configure a chave de proteção do CPF.');
            }

            $cpfHash = hash_hmac('sha256', $cpf, $chave);

            try {
                $sql = 'INSERT INTO clientes
                        (nome, cpf_hash, telefone, email)
                        VALUES (:nome, :cpf, :telefone, :email)';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nome' => $nome,
                    'cpf' => $cpfHash,
                    'telefone' => $telefone,
                    'email' => $email ?: null
                ]);

                $mensagem = 'Cliente cadastrado com sucesso!';
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $mensagem = 'Não foi possível cadastrar. Verifique se o CPF já existe.';
                } else {
                    error_log($e->getMessage());
                    $mensagem = 'Erro ao cadastrar cliente.';
                }
            }
        }
    }
}

$busca = trim($_GET['busca'] ?? '');

if ($busca !== '') {
    $sql = 'SELECT id_cliente, nome, telefone, email
            FROM clientes
            WHERE nome LIKE :busca
               OR telefone LIKE :busca
               OR email LIKE :busca
            ORDER BY nome';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['busca' => "%$busca%"]);
} else {
    $stmt = $pdo->query(
        'SELECT id_cliente, nome, telefone, email
         FROM clientes
         ORDER BY nome'
    );
}

$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Clientes - TechAssist</title>
</head>
<body>

<h1>Clientes</h1>

<a href="dashboard.php">Voltar ao painel</a>

<p><?= htmlspecialchars($mensagem) ?></p>

<h2>Cadastrar cliente</h2>

<form method="POST">
    <input type="hidden" name="acao" value="cadastrar">

    <label>Nome:</label>
    <input type="text" name="nome" maxlength="120" required>

    <br><br>

    <label>CPF:</label>
    <input type="text" name="cpf"
           inputmode="numeric" maxlength="14" required>

    <br><br>

    <label>Telefone:</label>
    <input type="text" name="telefone" maxlength="20" required>

    <br><br>

    <label>E-mail:</label>
    <input type="email" name="email" maxlength="150">

    <br><br>

    <button type="submit">Cadastrar</button>
</form>

<hr>

<h2>Pesquisar clientes</h2>

<form method="GET">
    <input
        type="search"
        name="busca"
        placeholder="Nome, telefone ou e-mail"
        value="<?= htmlspecialchars($busca) ?>"
    >

    <button type="submit">Buscar</button>
    <a href="clientes.php">Limpar</a>
</form>

<h2>Lista de clientes</h2>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>E-mail</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= (int)$cliente['id_cliente'] ?></td>
                <td><?= htmlspecialchars($cliente['nome']) ?></td>
                <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                <td><?= htmlspecialchars($cliente['email'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>