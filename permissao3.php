<?php
// Função para verificar a permissão de acesso
function verificarPermissao() {
    session_start(); // Inicia a sessão

    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        echo "Acesso negado. Por favor, faça login.";
        exit;
    }

    // Essa página pode ser acessada por qualquer usuário, então não fazemos restrição de role_id
    // Caso queira uma verificação para todos, basta remover o condicional abaixo
}

// Chama a função para garantir que o acesso seja restrito
verificarPermissao();
?>
