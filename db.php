<?php
// db.php
// Configurações do banco de dados
define('DB_SERVER', 'localhost'); // Geralmente 'localhost' para XAMPP
define('DB_USERNAME', 'root');    // Usuário padrão do XAMPP
define('DB_PASSWORD', '');        // Senha padrão do XAMPP (geralmente vazia)
define('DB_NAME', 'controle_despesas_db'); // Nome do banco de dados que você criou

// Tenta conectar ao banco de dados MySQL
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão
if ($mysqli === false || $mysqli->connect_error) {
    // Em uma aplicação real, logue o erro em vez de exibi-lo diretamente
    die("ERRO: Não foi possível conectar ao banco de dados. " . $mysqli->connect_error);
}

// Define o charset para utf8mb4 para suportar caracteres especiais
if (!$mysqli->set_charset("utf8mb4")) {
    // Em uma aplicação real, logue o erro
    // printf("Erro ao definir o charset utf8mb4: %s\n", $mysqli->error);
}

// echo "Conexão bem-sucedida!"; // Descomente para testar a conexão
?>