<?php
// Ativar exibição de erros para debug (remova em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$colaboradores_result = $conn->query("SELECT id, nome FROM colaboradores ORDER BY nome");
if (!$colaboradores_result) {
    die("Erro ao buscar colaboradores: " . $conn->error);
}

$colaborador_id = 0;
$msg = '';

// Processar formulário de associação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colaborador_id = isset($_POST['colaborador_id']) ? intval($_POST['colaborador_id']) : 0;
    $materiais_selecionados = isset($_POST['materiais']) ? $_POST['materiais'] : [];

    if ($colaborador_id > 0) {
        // Limpa associações antigas deste colaborador
        $stmt_del = $conn->prepare("DELETE FROM colaborador_materiais WHERE colaborador_id = ?");
        if ($stmt_del) {
            $stmt_del->bind_param("i", $colaborador_id);
            $stmt_del->execute();
            $stmt_del->close();
        }

        if (count($materiais_selecionados) > 0) {
            $stmt_ins = $conn->prepare("INSERT INTO colaborador_materiais (colaborador_id, material_id) VALUES (?, ?)");
            
            if ($stmt_ins) {
                $sucesso = true;
                foreach ($materiais_selecionados as $material_id) {
                    $mid = intval($material_id);
                    $stmt_ins->bind_param("ii", $colaborador_id, $mid);

                    if (!$stmt_ins->execute()) {
                        if ($conn->errno == 1062) {
                            $msg = "Material já está associado a esse colaborador.";
                            $sucesso = false;
                            break;
                        } else {
                            $msg = "Erro ao associar material: " . $stmt_ins->error;
                            $sucesso = false;
                            break;
                        }
                    }
                }
                
                if ($sucesso) {
                    $msg = "Materiais associados com sucesso!";
                }
                
                $stmt_ins->close();
            }
        } else {
            $msg = "Nenhum material foi selecionado para associação.";
        }
    } else {
        $msg = "Nenhum colaborador selecionado.";
    }
} else {
    // Carregar colaborador via GET
    if (isset($_GET['colaborador_id'])) {
        $colaborador_id = intval($_GET['colaborador_id']);
    }
}

// Inicializar variáveis
$materiais_associados = [];
$materiais_result = null;

// Se um colaborador foi selecionado
if ($colaborador_id > 0) {
    // Busca os materiais já associados ao colaborador atual
    $stmt_mat = $conn->prepare("SELECT material_id FROM colaborador_materiais WHERE colaborador_id = ?");
    if ($stmt_mat) {
        $stmt_mat->bind_param("i", $colaborador_id);
        $stmt_mat->execute();
        $res_mat = $stmt_mat->get_result();
        
        while ($row = $res_mat->fetch_assoc()) {
            $materiais_associados[] = $row['material_id'];
        }
        $stmt_mat->close();
    }
    
    // Query: Mostra apenas materiais não associados a NENHUM colaborador
    $query = "SELECT m.id, m.nome 
              FROM materiais m
              LEFT JOIN colaborador_materiais cm ON m.id = cm.material_id
              WHERE cm.material_id IS NULL
              ORDER BY m.nome";
    
    $materiais_result = $conn->query($query);
    
    if (!$materiais_result) {
        die("Erro na query de materiais: " . $conn->error);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associar Materiais a Colaborador - Auca Engenharia</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css?v=<?php echo time(); ?>" />
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
    <h2>Associar Materiais a um Colaborador</h2>

    <form method="GET" action="" class="select-colaborador">
        <label for="colaborador_id">👤 Selecione o Colaborador:</label>
        <select id="colaborador_id" name="colaborador_id" onchange="this.form.submit()">
            <option value="0">-- Selecione um colaborador --</option>
            <?php 
            if ($colaboradores_result && $colaboradores_result->num_rows > 0):
                foreach ($colaboradores_result as $col): 
            ?>
            <option value="<?php echo $col['id']; ?>" <?php if ($col['id'] == $colaborador_id) echo 'selected'; ?>>
                <?php echo htmlspecialchars($col['nome']); ?>
            </option>
            <?php 
                endforeach;
            else:
            ?>
            <option value="0">Nenhum colaborador cadastrado</option>
            <?php 
            endif;
            ?>
        </select>
        <noscript><button type="submit">Selecionar</button></noscript>
    </form>

    <?php if ($colaborador_id > 0): ?>

        <?php if ($msg): ?>
        <p class="msg success"><?php echo htmlspecialchars($msg); ?></p>
        <?php endif; ?>

        <?php if ($materiais_result && $materiais_result->num_rows > 0): ?>
        <form method="POST" action="">
            <input type="hidden" name="colaborador_id" value="<?php echo $colaborador_id; ?>" />
            
            <div class="info-box">
                <p>📦 Materiais disponíveis (não associados a nenhum colaborador): <strong><?php echo $materiais_result->num_rows; ?></strong></p>
            </div>
            
            <div class="materiais-list">
                <?php foreach ($materiais_result as $mat): ?>
                <label>
                    <input type="checkbox" name="materiais[]" value="<?php echo $mat['id']; ?>">
                    <?php echo htmlspecialchars($mat['nome']); ?>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn">Salvar Associação</button>
        </form>
        <?php else: ?>
        <div class="no-materials">
            <p>😊 Todos os materiais já estão associados a colaboradores!</p>
            <p style="font-size: 14px; color: #666; margin-top: 10px;">
                Para associar novos materiais, cadastre-os primeiro ou remova associações existentes.
            </p>
        </div>
        <?php endif; ?>

    <?php elseif ($colaborador_id == 0 && ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['colaborador_id']))): ?>
        <div class="no-materials">
            <p>⚠️ Por favor, selecione um colaborador válido.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
