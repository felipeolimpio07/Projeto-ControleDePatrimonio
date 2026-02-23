<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Filtrar Relatório de Colaboradores</title>
    <link rel="stylesheet" href="css/colaboradores_novo_usuario.css" />
</head>
<body>

<div class="navbar">
    <img src="imagens/logo_nova.png" alt="Logo AUCA" class="logo" />
    <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuário'); ?>!</h1>
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
    <h1>Filtrar Relatório de Colaboradores</h1>
    <form method="GET" action="relatorio_colaboradores.php" target="_blank">
        <label>Nome:</label>
        <input type="text" name="nome" placeholder="Filtrar por nome" />
        <label>CPF:</label>
        <input type="text" name="cpf" placeholder="Filtrar por CPF" />
        <button type="submit">Gerar Relatório</button>
    </form>
</div>

</body>
</html>
