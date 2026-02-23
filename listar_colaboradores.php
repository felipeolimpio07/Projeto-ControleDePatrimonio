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

$filtro = '';
if (isset($_GET['filtro'])) {
    $filtro = trim($_GET['filtro']);
}

if ($filtro != '') {
    $sql = "SELECT c.id, c.nome, c.cpf, c.cargo, 
        GROUP_CONCAT(m.nome SEPARATOR ', ') AS materiais,
        COUNT(DISTINCT cm.material_id) as total_materiais
        FROM colaboradores c
        LEFT JOIN colaborador_materiais cm ON c.id = cm.colaborador_id
        LEFT JOIN materiais m ON cm.material_id = m.id
        WHERE c.nome LIKE CONCAT('%', ?, '%') OR c.cpf LIKE CONCAT('%', ?, '%')
        GROUP BY c.id, c.nome, c.cpf, c.cargo
        ORDER BY c.nome";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $filtro, $filtro);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT c.id, c.nome, c.cpf, c.cargo, 
        GROUP_CONCAT(m.nome SEPARATOR ', ') AS materiais,
        COUNT(DISTINCT cm.material_id) as total_materiais
        FROM colaboradores c
        LEFT JOIN colaborador_materiais cm ON c.id = cm.colaborador_id
        LEFT JOIN materiais m ON cm.material_id = m.id
        GROUP BY c.id, c.nome, c.cpf, c.cargo
        ORDER BY c.nome";
    $result = $conn->query($sql);
}

$total_colaboradores = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Colaboradores - Auca Engenharia</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css?v=<?php echo time(); ?>" />
    <style>
        /* ====================================================================
           ESTILOS ESPECÍFICOS PARA LISTAGEM DE COLABORADORES
           ==================================================================== */
        
        /* Formulário de filtro estilizado */
        .filter-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            border: 3px solid #668245;
            position: relative;
            overflow: hidden;
        }

        .filter-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #668245 0%, #506831 50%, #668245 100%);
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-header h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 250px;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
        }

        .filter-group input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
            background-color: #f8f9fa;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        .filter-group input[type="text"]:focus {
            outline: none;
            border-color: #668245;
            box-shadow: 0 0 15px rgba(102, 130, 69, 0.4);
            background-color: white;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }

        .btn-filter.primary {
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 130, 69, 0.3);
        }

        .btn-filter.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 130, 69, 0.5);
        }

        .btn-filter.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-filter.secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.5);
        }

        .btn-filter.info {
            background: linear-gradient(135deg, #2196f3 0%, #1565c0 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .btn-filter.info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.5);
        }

        /* Container da tabela */
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
            padding: 16px 15px;
            text-align: left;
            color: white;
            font-weight: 700;
            font-size: 14px;
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
            transform: scale(1.005);
            box-shadow: 0 4px 12px rgba(102, 130, 69, 0.2);
        }

        td {
            padding: 14px 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #2c3e50;
            font-size: 14px;
        }

        tbody tr:last-child td:first-child {
            border-radius: 0 0 0 12px;
        }

        tbody tr:last-child td:last-child {
            border-radius: 0 0 12px 0;
        }

        /* Badge de materiais */
        .badge-materiais {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .badge-materiais.none {
            background-color: #f5f5f5;
            color: #999;
        }

        .badge-materiais.has {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        /* Botões de ação */
        .btn-table {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            background-color: #2196f3;
            color: white;
            border: 2px solid #2196f3;
        }

        .btn-table:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
        }

        /* Estado vazio */
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

        /* Materiais tooltip */
        .materiais-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
        }

        .materiais-cell:hover {
            white-space: normal;
            overflow: visible;
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .filter-container {
                padding: 20px;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .filter-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-filter {
                width: 100%;
                justify-content: center;
            }

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
                font-size: 13px;
            }

            th, td {
                padding: 10px 8px;
            }
        }

        @media (max-width: 600px) {
            .filter-container {
                padding: 15px;
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
    <h2>Gerenciamento de Colaboradores</h2>

    <!-- Filtro de Busca -->
    <div class="filter-container">
        <div class="filter-header">
            <span style="font-size: 24px;">🔍</span>
            <h3>Filtrar Colaboradores</h3>
        </div>
        
        <form method="GET" action="" class="filter-form">
            <div class="filter-group">
                <label for="filtro">Buscar por Nome ou CPF:</label>
                <input 
                    type="text" 
                    id="filtro" 
                    name="filtro" 
                    value="<?php echo htmlspecialchars($filtro); ?>" 
                    placeholder="Digite o nome ou CPF do colaborador"
                />
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter primary">
                    <span>🔎</span> Buscar
                </button>
                <a href="listar_colaboradores.php" class="btn-filter secondary">
                    <span>🔄</span> Limpar
                </a>
                <a href="filtros_relatorios.php" class="btn-filter info">
                    <span>📊</span> Relatórios
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Resultados -->
    <div class="table-container">
        <div class="table-header">
            <h3>
                <span>👥</span> Lista de Colaboradores
            </h3>
            <div class="table-stats">
                <?php if ($filtro != ''): ?>
                    Encontrados: <?php echo $total_colaboradores; ?>
                <?php else: ?>
                    Total: <?php echo $total_colaboradores; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($result && $total_colaboradores > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Cargo</th>
                        <th>Materiais</th>
                        <th>Qtd</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($row['id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($row['cargo'] ?? '-'); ?></td>
                        <td class="materiais-cell" title="<?php echo htmlspecialchars($row['materiais'] ?? 'Nenhum material associado'); ?>">
                            <?php echo htmlspecialchars($row['materiais'] ?? 'Nenhum'); ?>
                        </td>
                        <td>
                            <?php 
                            $total_mat = $row['total_materiais'];
                            $badge_class = $total_mat > 0 ? 'badge-materiais has' : 'badge-materiais none';
                            $badge_text = $total_mat > 0 ? "📦 $total_mat" : "0";
                            ?>
                            <span class="<?php echo $badge_class; ?>">
                                <?php echo $badge_text; ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar_colaborador.php?id=<?php echo $row['id']; ?>" class="btn-table">
                                <span>✏️</span> Editar
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <h3>
                <?php if ($filtro != ''): ?>
                    Nenhum Colaborador Encontrado
                <?php else: ?>
                    Nenhum Colaborador Cadastrado
                <?php endif; ?>
            </h3>
            <p>
                <?php if ($filtro != ''): ?>
                    Não foram encontrados colaboradores com os filtros aplicados.<br>
                    Tente buscar com outros termos.
                <?php else: ?>
                    Comece cadastrando seu primeiro colaborador no sistema.
                <?php endif; ?>
            </p>
            <?php if ($filtro == ''): ?>
            <a href="colaboradores.php" class="btn-filter primary">
                <span>➕</span> Cadastrar Primeiro Colaborador
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
