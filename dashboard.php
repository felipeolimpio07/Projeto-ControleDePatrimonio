<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
    <style>
        /* Área principal para o carrossel */
        .main-content {
            margin-left: 250px;
            padding: 90px 30px 30px;
            background-color: white;
            min-height: calc(100vh - 60px);
            border-radius: 8px;
            box-sizing: border-box;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 2px 2px 12px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        /* Container do carrossel */
        .carousel {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .carousel img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .carousel img.active {
            opacity: 1;
            position: relative;
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
    <h2>Dashboard</h2>



</body>
</html>
