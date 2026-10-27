<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $sql = 'SELECT id_usuario, nome, email, senha_hash
            FROM usuarios
            WHERE email = :email AND ativo = 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    $usuario = $stmt->fetch();

    if (
        $usuario &&
        password_verify($senha, $usuario['senha_hash'])
    ) {
        session_regenerate_id(true);

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nome_usuario'] = $usuario['nome'];
        $_SESSION['ultima_atividade'] = time();

        header('Location: dashboard.php');
        exit;
    }

    $erro = 'E-mail ou senha inválidos.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - TechAssist</title>
</head>
<body>

<h1>TechAssist</h1>
<h2>Entrar no sistema</h2>

<?php if (isset($_GET['expirada'])): ?>
    <p>Sua sessão expirou. Faça login novamente.</p>
<?php endif; ?>

<?php if ($erro): ?>
    <p><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<form method="POST">
    <label>E-mail:</label>
    <input type="email" name="email" required>

    <br><br>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <br><br>

    <button type="submit">Entrar</button>
</form>

</body>
</html>