<?php
// header.php
// Inicia a sessão se ainda não estiver iniciada (útil para mensagens flash, etc.)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Despesas Pessoais</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="navbar-brand">Controle de Despesas</a>
                <ul class="navbar-nav">
                    <li><a href="index.php?page=categorias">Categorias</a></li>
                    <li><a href="index.php?page=despesas">Despesas</a></li>
                    <li><a href="index.php?page=relatorios">Relatórios</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
        <?php
        // Exibir mensagens de feedback (sessão flash)
        if (isset($_SESSION['message'])):
        ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>