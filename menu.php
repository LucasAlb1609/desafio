<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Menu de Opções</title>
</head>
<body>
    <?php
    session_start();

    // Verifique se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        echo "Acesso negado. Por favor, faça login.";
        exit;
    }

    $role_id = $_SESSION['role_id']; // Obtém o tipo de usuário logado
    ?>

    <h1>Escolha uma opção</h1>

    <!-- Link para Cadastrar Pessoas (visível apenas para Administrador - role_id = 1) -->
    <?php if ($role_id == 1): ?>
        <p><a href="pessoas.php">Cadastrar Novo Usuário</a></p>
    <?php endif; ?>

    <!-- Link para Gerar Declaração (visível para todos) -->
    <p><a href="gerar_declaracao.php">Gerar Declaração</a></p>

    <!-- Link para Cadastrar Vínculo (visível apenas para Administrador - role_id = 1) -->
    <?php if ($role_id == 1): ?>
        <p><a href="vinculo.php">Cadastrar Vínculo</a></p>
    <?php endif; ?>

    <!-- Link para Buscar Vínculo por CPF (visível para todos) -->
    <p><a href="consulta_cpf.php">Buscar Vínculo</a></p>

    <!-- Link para Buscar Vínculo pelo Hash Code (visível para Administrador e Gerente - role_id = 1 ou 2) -->
    <?php if ($role_id == 1 || $role_id == 2): ?>
        <p><a href="consulta_hash.php">Buscar Hash Code</a></p>
    <?php endif; ?>

</body>
</html>
