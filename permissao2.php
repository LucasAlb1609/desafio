<?php
// Função para verificar a permissão de acesso
function verificarPermissao() {
    session_start(); // Inicia a sessão

    // Verifica se o usuário está logado e se o role_id corresponde ao esperado
    if (!isset($_SESSION['user_id'])) {
        echo "Acesso negado. Por favor, faça login.";
        exit;
    }

    // Verifica se o usuário tem a permissão de Administrador (role_id = 1) ou Gerente (role_id = 2)
    if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) {
        echo "Acesso negado. Você não tem permissão para acessar esta página.";
        exit;
    }
}

// Chama a função para garantir que o acesso seja restrito
verificarPermissao();
?>
