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
    header("Location: listar_colaboradores.php");
    exit();
}

$id = intval($_GET['id']);
$msg = '';
$msgClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['deletar'])) {
        // Deletar colaborador
        $stmt_del = $conn->prepare("DELETE FROM colaboradores WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        if ($stmt_del->execute()) {
            $stmt_del->close();
            $conn->close();
            header("Location: listar_colaboradores.php?msg=Colaborador+deletado+com+sucesso");
            exit();
        } else {
            $msg = "Erro ao deletar colaborador: " . $stmt_del->error;
            $msgClass = "msg error";
            $stmt_del->close();
        }
    } else {
        // Atualizar colaborador
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $cargo = $_POST['cargo'];
        $materiais_selecionados = isset($_POST['materiais']) ? $_POST['materiais'] : [];

        $stmt_update = $conn->prepare("UPDATE colaboradores SET nome=?, cpf=?, cargo=? WHERE id=?");
        $stmt_update->bind_param("sssi", $nome, $cpf, $cargo, $id);
        if ($stmt_update->execute()) {
            $msg = "Colaborador atualizado com sucesso!";
            $msgClass = "msg success";

            // Atualizar associações de materiais
            $stmt_del_mat = $conn->prepare("DELETE FROM colaborador_materiais WHERE colaborador_id=?");
            $stmt_del_mat->bind_param("i", $id);
            $stmt_del_mat->execute();
            $stmt_del_mat->close();

            if (count($materiais_selecionados) > 0) {
                $stmt_ins_mat = $conn->prepare("INSERT INTO colaborador_materiais (colaborador_id, material_id) VALUES (?, ?)");
                foreach ($materiais_selecionados as $material_id) {
                    $mid = intval($material_id);
                    $stmt_ins_mat->bind_param("ii", $id, $mid);
                    $stmt_ins_mat->execute();
                }
                $stmt_ins_mat->close();
            }
        } else {
            $msg = "Erro ao atualizar colaborador: " . $stmt_update->error;
            $msgClass = "msg error";
        }
        $stmt_update->close();
    }
}

// Buscar dados do colaborador
$stmt = $conn->prepare("SELECT nome, cpf, cargo FROM colaboradores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: listar_colaboradores.php");
    exit();
}

$colaborador = $result->fetch_assoc();
$stmt->close();

// Buscar materiais associados
$stmtMat = $conn->prepare("
    SELECT m.id, m.nome
    FROM materiais m
    INNER JOIN colaborador_materiais cm ON m.id = cm.material_id
    WHERE cm.colaborador_id = ?
    ORDER BY m.nome
");
$stmtMat->bind_param("i", $id);
$stmtMat->execute();
$resMat = $stmtMat->get_result();

$materiais_associados = [];
while ($rowMat = $resMat->fetch_assoc()) {
    $materiais_associados[] = $rowMat;
}
$stmtMat->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Editar Colaborador</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
<script>
function confirmarExclusao() {
    return confirm('Tem certeza que deseja excluir este colaborador? Esta ação não pode ser desfeita.');
}
</script>
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
    <h2>Editar Colaborador</h2>

    <?php if ($msg): ?>
        <p class="<?php echo $msgClass; ?>"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($colaborador['nome']); ?>">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" maxlength="14" required value="<?php echo htmlspecialchars($colaborador['cpf']); ?>">

        <label for="cargo">Cargo:</label>
        <input type="text" id="cargo" name="cargo" required value="<?php echo htmlspecialchars($colaborador['cargo']); ?>">

        <div class="materiais-list">
            <label>Materiais Associados:</label>
            <?php if (count($materiais_associados) > 0): ?>
                <?php foreach ($materiais_associados as $material): ?>
                    <label>
                        <input type="checkbox" name="materiais[]" value="<?php echo $material['id']; ?>" checked>
                        <?php echo htmlspecialchars($material['nome']); ?>
                    </label>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum material associado.</p>
            <?php endif; ?>
        </div>

        <button type="submit">Atualizar</button>
    </form>

    <form method="POST" action="" onsubmit="return confirmarExclusao();">
        <input type="hidden" name="deletar" value="1" />
        <button type="submit" class="btn-delete">Excluir Colaborador</button>
    </form>

</div>

</body>
</html>
