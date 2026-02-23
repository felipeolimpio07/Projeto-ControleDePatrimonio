<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


$msg = ''; // Inicializa a variável para a mensagem de erro

// Configuração da conexão ao banco de dados
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

// Cria a conexão
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Lógica de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['usuario'] = $usuario;
        
        // Armazena a mensagem de boas-vindas na sessão antes de redirecionar
        $_SESSION['bem_vindo_msg'] = "Bem-vindo ao sistema!";
        
        header("Location: dashboard.php");
        exit();
    } else {
        // A mensagem de erro é atribuída aqui quando o login falha
        $msg = "Usuário ou senha incorretos.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login - Auca Engenharia</title>
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>" />
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="logo-container">
            <img src="imagens/logo_nova.png" alt="Logo Auca Engenharia" class="logo">
        </div>
        
        <h2>Tela de Login</h2>

        <?php if ($msg != ''): ?>
            <p class="error-msg"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="usuario">Usuário:</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="username">
            </div>

            <div class="input-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required autocomplete="current-password">
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>
</div>

</body>
</html>
