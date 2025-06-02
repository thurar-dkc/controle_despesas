<?php
// footer.php
?>
    </main> <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Controle de Despesas Pessoais. Todos os direitos reservados.</p>
        </div>
    </footer>
    </body>
</html>
<?php
// Fecha a conexão com o banco de dados se estiver aberta
// É uma boa prática, embora o PHP geralmente feche conexões automaticamente no final do script.
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>