<?php
// conexao.php
$host = "localhost";
$user = "root"; // usuário padrão do MySQL no XAMPP
$password = ""; // senha em branco para o usuário root no XAMPP
$dbname = "minha_database";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>
