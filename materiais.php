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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $origem = trim($_POST['origem']);

    // Validações
    if ($nome === '' || $origem === '') {
        $msg = "Por favor, preencha todos os campos obrigatórios.";
        $msg_type = "error";
    } elseif (strlen($nome) < 3) {
        $msg = "O nome do material deve ter no mínimo 3 caracteres.";
        $msg_type = "error";
    } else {
        // Verificar se o material já existe
        $check_sql = "SELECT id FROM materiais WHERE nome = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if ($check_stmt) {
            $check_stmt->bind_param("s", $nome);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $msg = "Este material já está cadastrado no sistema.";
                $msg_type = "error";
            } else {
                // Inserir novo material
                $sql = "INSERT INTO materiais (nome, origem) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    die("Erro na preparação da consulta: " . $conn->error);
                }
                
                $stmt->bind_param("ss", $nome, $origem);

                if ($stmt->execute()) {
                    $msg = "✅ Material cadastrado com sucesso!";
                    $msg_type = "success";
                    
                    // Limpar campos após sucesso
                    $nome = '';
                    $origem = '';
                } else {
                    $msg = "❌ Erro ao cadastrar material: " . htmlspecialchars($stmt->error);
                    $msg_type = "error";
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
    <title>Cadastro de Materiais - Auca Engenharia</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css?v=<?php echo time(); ?>" />
    <style>
        /* ====================================================================
           ESTILOS ESPECÍFICOS PARA CADASTRO DE MATERIAIS
           ==================================================================== */
        
        /* Form Container Premium */
        .form-container {
            background: white;
            padding: 0;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
            max-width: 650px;
            margin: 0 auto;
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
            padding: 35px;
            text-align: center;
            color: white;
            position: relative;
        }

        .form-icon {
            font-size: 56px;
            display: block;
            margin-bottom: 15px;
            animation: rotate 3s ease-in-out infinite;
        }

        @keyframes rotate {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .form-header h3 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .form-container form {
            padding: 40px;
            max-width: none;
            box-shadow: none;
            background: transparent;
        }

        /* Input Groups com Ícones */
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 15px;
        }

        .label-icon {
            font-size: 18px;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            box-sizing: border-box;
            font-size: 16px;
            background-color: #f8f9fa;
            color: #2c3e50;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #668245;
            box-shadow: 0 0 15px rgba(102, 130, 69, 0.4);
            background-color: white;
        }

        .input-group input:hover,
        .input-group select:hover {
            border-color: #668245;
        }

        /* Ícone dentro do input */
        .input-icon {
            position: absolute;
            left: 15px;
            bottom: 15px;
            font-size: 20px;
            color: #668245;
            pointer-events: none;
        }

        /* Dica abaixo dos inputs */
        .input-hint {
            display: block;
            font-size: 13px;
            color: #666;
            margin-top: 6px;
            font-style: italic;
        }

        /* Select customizado */
        select {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Cpath fill='%23668245' d='M10 12l-5-5h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
            padding-right: 45px;
        }

        /* Mensagens */
        .msg {
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .msg.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 6px solid #28a745;
        }

        .msg.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 6px solid #dc3545;
        }

        /* Botão com ícone */
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #668245 0%, #506831 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 6px 20px rgba(102, 130, 69, 0.4);
            margin-top: 30px;
        }

        .btn-icon {
            font-size: 20px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 130, 69, 0.6);
            background: linear-gradient(135deg, #506831 0%, #668245 100%);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        /* Footer do formulário */
        .form-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 2px solid #e0e0e0;
        }

        .form-footer p {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #666;
        }

        .form-footer a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2196f3;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-footer a:hover {
            background-color: #e3f2fd;
            transform: translateX(5px);
        }

        /* Responsividade */
        @media (max-width: 600px) {
            .form-container form {
                padding: 30px 20px;
            }

            .form-header {
                padding: 25px 20px;
            }

            .form-icon {
                font-size: 42px;
            }

            .form-header h3 {
                font-size: 22px;
            }

            .input-group input,
            .input-group select {
                padding: 12px 14px 12px 40px;
                font-size: 15px;
            }

            .input-icon {
                bottom: 13px;
                font-size: 18px;
            }

            .btn {
                padding: 14px;
                font-size: 16px;
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
    <?php if ($msg !== ''): ?>
    <div class="msg <?php echo $msg_type; ?>">
        <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="form-header">
            <h3>Novo Material</h3>
            <p>Preencha os dados abaixo para cadastrar</p>
        </div>

        <form method="POST" action="">
            <div class="input-group">
                <label for="nome">
                    <span class="label-icon"></span> Nome do Material:
                </label>
                <input 
                    type="text" 
                    id="nome" 
                    name="nome" 
                    value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                    required 
                    minlength="3"
                />
            </div>

            <div class="input-group">
                <label for="origem">
                    <span class="label-icon"></span> Origem do Material:
                </label>
                <span class="input-icon"></span>
                <select 
                    id="origem" 
                    name="origem" 
                    required
                >
                    <option value="">-- Selecione a origem --</option>
                    <option value="Escritório" <?php echo (isset($origem) && $origem === 'Escritório') ? 'selected' : ''; ?>>🏢 Escritório</option>
                    <option value="Obra" <?php echo (isset($origem) && $origem === 'Obra') ? 'selected' : ''; ?>>🏗️ Obra</option>
                </select>
                <small class="input-hint">Selecione de onde o material será disponibilizado</small>
            </div>

            <button type="submit" class="btn">
                <span class="btn-icon">✓</span> Cadastrar Material
            </button>
        </form>

        <div class="form-footer">
            <p><strong>Após cadastrar:</strong> O material estará disponível para associação com colaboradores</p>
            <a href="listar_materiais.php">
                <span>📋</span> Ver Lista de Materiais
            </a>
        </div>
    </div>
</div>

</body>
</html>
