<?php
// index.php
// Inclui o arquivo de conexão com o banco de dados
require_once 'db.php';

// Inclui o cabeçalho da página
include 'header.php';

// Determina qual página carregar com base no parâmetro 'page' da URL
$page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Página padrão é 'home'

// Carrega o conteúdo da página solicitada
// Em uma aplicação maior, você pode usar um sistema de roteamento mais robusto
switch ($page) {
    case 'categorias':
        include 'categorias.php';
        break;
    case 'despesas':
        include 'despesas.php';
        break;
    case 'relatorios':
        include 'relatorios.php';
        break;
    case 'home':
        // Página inicial pode ser o relatório ou uma dashboard
        echo "<h1>Bem-vindo ao Controle de Despesas</h1>";
        echo "<p>Selecione uma opção no menu acima para começar.</p>";
        // Poderia incluir relatorios.php aqui por padrão, se desejado.
        // include 'relatorios.php';
        break;
    default:
        // Página não encontrada
        echo "<h1>Página não encontrada (Erro 404)</h1>";
        echo "<p>A página que você está tentando acessar não existe.</p>";
        break;
}

// Inclui o rodapé da página
include 'footer.php';
?>