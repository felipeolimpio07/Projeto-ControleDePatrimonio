<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "auca_engenharia";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    header("Location: listar_materiais.php");
    exit();
}

$id = intval($_GET['id']);
$msg = '';
$msgClass = '';

$stmt = $conn->prepare("SELECT nome FROM materiais WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: listar_materiais.php");
    exit();
}

$material = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);

    if ($nome === '') {
        $msg = "O nome do material não pode ser vazio.";
        $msgClass = "msg error";
    } else {
        $stmt_update = $conn->prepare("UPDATE materiais SET nome = ? WHERE id = ?");
        $stmt_update->bind_param("si", $nome, $id);
        if ($stmt_update->execute()) {
            $msg = "Material atualizado com sucesso!";
            $msgClass = "msg success";
            $material['nome'] = $nome;
        } else {
            $msg = "Erro ao atualizar material: " . $stmt_update->error;
            $msgClass = "msg error";
        }
        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Material</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
</head>
<body>

<div class="navbar">
    <img src="imagens/logo_nova.png" alt="Logo AUCA" class="logo" />
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
    <h2>Editar Material</h2>

    <?php if ($msg): ?>
        <p class="<?php echo $msgClass; ?>"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nome">Nome do Material:</label>
        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($material['nome']); ?>">

        <button type="submit">Salvar Alteração</button>
    </form>

</div>

</body>
</html>
