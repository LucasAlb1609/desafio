<?php
session_start();
include 'conexao.php';

/**
 * Verifica se um usuário tem pelo menos um dos papéis necessários.
 * 
 * @param mysqli $conn Conexão com o banco de dados.
 * @param int $userId ID do usuário.
 * @param array $roleIds Lista de IDs de papéis permitidos.
 * @return bool Retorna true se o usuário tiver algum dos papéis, false caso contrário.
 */
function validarPermissoes($conn, $userId, $roleIds) {
    $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
    $stmt = $conn->prepare("SELECT 1 FROM pessoas_roles WHERE pessoa_id = ? AND role_id IN ($placeholders)");

    $types = str_repeat('i', count($roleIds) + 1);
    $params = array_merge([$userId], $roleIds);

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0; // Retorna true se alguma permissão existir
}

/**
 * Middleware para validar múltiplas permissões e redirecionar se necessário.
 * 
 * @param mysqli $conn Conexão com o banco de dados.
 * @param array $roleIds Lista de IDs de papéis permitidos.
 */
function checarPermissoes($conn, $roleIds) {
    if (!isset($_SESSION['user_id'])) {
        echo "Acesso negado. Por favor, faça login.";
        exit;
    }

    $roleIds = (array)$roleIds; // Converte para array se não for
    $userId = $_SESSION['user_id'];
    if (!validarPermissoes($conn, $userId, $roleIds)) {
        echo "Você não tem permissão para acessar esta página.";
        exit;
    }
}
?>
