<?php
// Ativar exibição de erros para debug (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Configuração da conexão
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$msg = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // Validação dos campos
    if ($nome == '' || $usuario == '' || $senha == '' || $confirmar_senha == '') {
        $msg = "Por favor, preencha todos os campos.";
        $msg_type = 'error';
    } elseif (strlen($senha) < 6) {
        $msg = "A senha deve ter no mínimo 6 caracteres.";
        $msg_type = 'error';
    } elseif ($senha !== $confirmar_senha) {
        $msg = "As senhas não coincidem. Por favor, digite novamente.";
        $msg_type = 'error';
    } elseif (strlen($usuario) < 4) {
        $msg = "O nome de usuário deve ter no mínimo 4 caracteres.";
        $msg_type = 'error';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
        $msg = "O usuário pode conter apenas letras, números e underscore (_).";
        $msg_type = 'error';
    } else {
        // Verifica se o usuário já existe
        $check_sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if ($check_stmt) {
            $check_stmt->bind_param("s", $usuario);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $msg = "Este nome de usuário já está em uso. Por favor, escolha outro.";
                $msg_type = 'error';
            } else {
                // Hash da senha para segurança
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Insere usuário com senha criptografada
                $sql = "INSERT INTO usuarios (nome, usuario, senha) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    die("Erro na preparação da consulta: " . $conn->error);
                }

                $stmt->bind_param("sss", $nome, $usuario, $senha_hash);

                if ($stmt->execute()) {
                    $msg = "✅ Usuário cadastrado com sucesso!";
                    $msg_type = 'success';
                    
                    // Limpar campos após sucesso
                    $nome = '';
                    $usuario = '';
                    $senha = '';
                    $confirmar_senha = '';
                } else {
                    $msg = "Erro ao cadastrar usuário. Tente novamente.";
                    $msg_type = 'error';
                }

                $stmt->close();
            }
            
            $check_stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo Usuário - Auca Engenharia</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css?v=<?php echo time(); ?>" />
</head>
<body>

<div class="navbar">
    <img src="imagens/logo_nova.png" alt="Logo AUCA" class="logo">
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
    <a href="logout.php" class="logout">Sair</a>
</div>

<div class="sidebar">
    <a href="colaboradores.php">Cadastrar Colaboradores</a>
    <a href="listar_colaboradores.php">Listar Colaboradores</a>
    <a href="materiais.php">Cadastrar Materiais</a>
    <a href="listar_materiais.php">Editar Materiais</a>
    <a href="novo_usuario.php">Cadastrar novo usuário</a>
    <a href="associar_materiais.php">Associar Materiais a Colaboradores</a>
</div>

<div class="main-content">
    <h2>Cadastrar Novo Usuário</h2>

    <?php if ($msg != ''): ?>
    <div class="msg <?php echo $msg_type; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="form-header">
            <span class="form-icon">👤</span>
            <h3>Informações do Usuário</h3>
        </div>

        <form method="POST" action="">
            <div class="input-group">
                <label for="nome">
                    <span class="label-icon">📝</span> Nome Completo:
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    placeholder="Digite o nome completo"
                    value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                    required
                >
            </div>

            <div class="input-group">
                <label for="usuario">
                    <span class="label-icon">👨‍💼</span> Nome de Usuário:
                </label>
                <input 
                    type="text" 
                    id="usuario" 
                    name="usuario" 
                    placeholder="Mínimo 4 caracteres (letras, números e _)"
                    value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>"
                    pattern="[a-zA-Z0-9_]{4,}"
                    title="Mínimo 4 caracteres (letras, números e underscore)"
                    required
                >
                <small class="input-hint">Use apenas letras, números e underscore (_)</small>
            </div>

            <div class="input-group">
                <label for="senha">
                    <span class="label-icon">🔒</span> Senha:
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    placeholder="Mínimo 6 caracteres"
                    minlength="6"
                    required
                >
                <small class="input-hint">Mínimo de 6 caracteres</small>
            </div>

            <div class="input-group">
                <label for="confirmar_senha">
                    <span class="label-icon">🔐</span> Confirmar Senha:
                </label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    placeholder="Digite a senha novamente"
                    minlength="6"
                    required
                >
            </div>

            <button type="submit" class="btn">
                <span class="btn-icon">✓</span> Cadastrar Usuário
            </button>
        </form>

        <div class="form-footer">
            <p>
                <strong>Dica de Segurança:</strong> Use senhas fortes com letras, números e caracteres especiais.
            </p>
        </div>
    </div>
</div>

</body>
</html>
