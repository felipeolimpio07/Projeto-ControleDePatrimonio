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

$msg = '';
$msg_type = '';

// Deletar material quando passar delete_id via GET
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Verificar se o material está associado a algum colaborador
    $stmt_check = $conn->prepare("SELECT COUNT(*) as count_assoc FROM colaborador_materiais WHERE material_id = ?");
    
    if ($stmt_check) {
        $stmt_check->bind_param("i", $delete_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        $row_check = $res_check->fetch_assoc();
        $stmt_check->close();

        if ($row_check['count_assoc'] > 0) {
            $msg = "⚠️ Material não pode ser excluído porque está associado a " . $row_check['count_assoc'] . " colaborador(es).";
            $msg_type = "error";
        } else {
            $stmt_del = $conn->prepare("DELETE FROM materiais WHERE id = ?");
            
            if ($stmt_del) {
                $stmt_del->bind_param("i", $delete_id);
                
                if ($stmt_del->execute()) {
                    $msg = "✅ Material excluído com sucesso!";
                    $msg_type = "success";
                } else {
                    $msg = "❌ Erro ao excluir material: " . htmlspecialchars($stmt_del->error);
                    $msg_type = "error";
                }
                
                $stmt_del->close();
            }
        }
    }
}

// Buscar lista de materiais com contagem de associações
$sql = "SELECT m.id, m.nome, 
        COUNT(cm.colaborador_id) as total_associacoes
        FROM materiais m
        LEFT JOIN colaborador_materiais cm ON m.id = cm.material_id
        GROUP BY m.id, m.nome
        ORDER BY m.nome";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na query: " . $conn->error);
}

$total_materiais = $result->num_rows;

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Materiais - Auca Engenharia</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css?v=<?php echo time(); ?>" />
    <style>
        /* ====================================================================
           ESTILOS ESPECÍFICOS PARA TABELA
           ==================================================================== */
        .table-container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
            max-width: 1200px;
            margin: 0 auto;
            overflow-x: auto;
        }

        .table-header {
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
            padding: 25px 30px;
            margin: -30px -30px 25px -30px;
            border-radius: 20px 20px 0 0;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-stats {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .top-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 130, 69, 0.3);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 130, 69, 0.5);
        }

        .btn-action.secondary {
            background: linear-gradient(135deg, #2196f3 0%, #1565c0 100%);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .btn-action.secondary:hover {
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.5);
        }

        /* Tabela moderna */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        thead {
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
        }

        th {
            padding: 16px 20px;
            text-align: left;
            color: white;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        tbody tr {
            background-color: white;
            transition: all 0.3s ease;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e8f5e9;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(102, 130, 69, 0.2);
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #e0e0e0;
            color: #2c3e50;
            font-size: 15px;
        }

        tbody tr:last-child td:first-child {
            border-radius: 0 0 0 12px;
        }

        tbody tr:last-child td:last-child {
            border-radius: 0 0 12px 0;
        }

        /* Badge de associações */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .badge.warning {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .badge.success {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        /* Botões de ação na tabela */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-table {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-edit {
            background-color: #2196f3;
            color: white;
            border-color: #2196f3;
        }

        .btn-edit:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
            border-color: #f44336;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }

        /* Mensagem vazia */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            margin-top: 20px;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 16px;
            margin-bottom: 25px;
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .table-container {
                padding: 20px;
            }

            .table-header {
                padding: 20px;
                margin: -20px -20px 20px -20px;
                flex-direction: column;
                text-align: center;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 12px 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-table {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 600px) {
            .top-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .table-container {
                padding: 15px;
            }

            .table-header h3 {
                font-size: 20px;
            }

            /* Tabela responsiva - scroll horizontal */
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
    <script>
        function confirmDelete(materialName, totalAssoc) {
            if (totalAssoc > 0) {
                alert('Este material está associado a ' + totalAssoc + ' colaborador(es) e não pode ser excluído.');
                return false;
            }
            return confirm('Tem certeza que deseja excluir o material "' + materialName + '"?\n\nEsta ação não pode ser desfeita!');
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
    <h2>Gerenciamento de Materiais</h2>

    <?php if ($msg != ''): ?>
    <div class="msg <?php echo $msg_type; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>

    <div class="top-buttons">
        <a href="materiais.php" class="btn-action">
            <span>➕</span> Cadastrar Novo Material
        </a>
        <a href="relatorio_colaboradores_materias.php" class="btn-action secondary">
            <span>📊</span> Gerar Relatório
        </a>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3>
                <span></span> Lista de Materiais
            </h3>
            <div class="table-stats">
                Total: <?php echo $total_materiais; ?> materiais
            </div>
        </div>

        <?php if ($result && $total_materiais > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Material</th>
                        <th>Associações</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($row['id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td>
                            <?php 
                            $total_assoc = $row['total_associacoes'];
                            $badge_class = '';
                            
                            if ($total_assoc == 0) {
                                $badge_class = 'badge';
                                $badge_text = '🆓 Disponível';
                            } elseif ($total_assoc == 1) {
                                $badge_class = 'badge success';
                                $badge_text = '👤 1 colaborador';
                            } else {
                                $badge_class = 'badge warning';
                                $badge_text = '👥 ' . $total_assoc . ' colaboradores';
                            }
                            ?>
                            <span class="<?php echo $badge_class; ?>">
                                <?php echo $badge_text; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="editar_material.php?id=<?php echo $row['id']; ?>" class="btn-table btn-edit">
                                    <span>✏️</span> Editar
                                </a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" 
                                   class="btn-table btn-delete" 
                                   onclick="return confirmDelete('<?php echo htmlspecialchars(addslashes($row['nome'])); ?>', <?php echo $total_assoc; ?>)">
                                    <span>🗑️</span> Excluir
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h3>Nenhum Material Cadastrado</h3>
            <p>Comece cadastrando seu primeiro material no sistema.</p>
            <a href="materiais.php" class="btn-action">
                <span>➕</span> Cadastrar Primeiro Material
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
