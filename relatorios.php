<?php
// relatorios.php
// Este arquivo é incluído pelo index.php.

// Lógica para filtros (exemplo básico: por mês e ano, e por categoria)
$filtro_mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m'); // Mês e ano atual por padrão
$filtro_categoria_id = isset($_GET['categoria_id']) ? $_GET['categoria_id'] : '';

$where_clauses = [];
$params = [];
$types = "";

if (!empty($filtro_mes_ano)) {
    // Assegura que o formato é YYYY-MM
    if (preg_match('/^\d{4}-\d{2}$/', $filtro_mes_ano)) {
        $where_clauses[] = "DATE_FORMAT(d.data_despesa, '%Y-%m') = ?";
        $params[] = $filtro_mes_ano;
        $types .= "s";
    } else {
        // Se o formato for inválido, pode resetar ou usar um padrão
        $filtro_mes_ano = date('Y-m');
        $where_clauses[] = "DATE_FORMAT(d.data_despesa, '%Y-%m') = ?";
        $params[] = $filtro_mes_ano;
        $types .= "s";
    }
}

if (!empty($filtro_categoria_id) && is_numeric($filtro_categoria_id)) {
    $where_clauses[] = "d.categoria_id = ?";
    $params[] = $filtro_categoria_id;
    $types .= "i";
}

$sql_despesas = "SELECT d.id, d.descricao, d.valor, DATE_FORMAT(d.data_despesa, '%d/%m/%Y') as data_formatada, d.observacoes, c.nome as nome_categoria
                 FROM despesas d
                 JOIN categorias c ON d.categoria_id = c.id";

if (!empty($where_clauses)) {
    $sql_despesas .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql_despesas .= " ORDER BY d.data_despesa DESC, d.id DESC";

$stmt_despesas = $mysqli->prepare($sql_despesas);
if ($stmt_despesas) {
    if (!empty($params)) {
        $stmt_despesas->bind_param($types, ...$params);
    }
    $stmt_despesas->execute();
    $result_despesas = $stmt_despesas->get_result();
} else {
    echo "Erro na preparação da query: " . $mysqli->error;
    $result_despesas = false; // Garante que não tentaremos usar um resultado inválido
}


// Buscar categorias para o filtro
$categorias_filtro = [];
$sql_cat_filtro = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$result_cat_filtro = $mysqli->query($sql_cat_filtro);
if ($result_cat_filtro && $result_cat_filtro->num_rows > 0) {
    while ($cat = $result_cat_filtro->fetch_assoc()) {
        $categorias_filtro[] = $cat;
    }
    $result_cat_filtro->free();
}

$total_despesas_periodo = 0;
if ($result_despesas) {
    // Recalcular o total com base nos resultados filtrados
    // Precisamos buscar os valores numéricos para somar
    $temp_result_for_sum = [];
    while($row = $result_despesas->fetch_assoc()){
        $total_despesas_periodo += $row['valor'];
        $temp_result_for_sum[] = $row; // Armazena para exibir na tabela depois
    }
    // Resetar o ponteiro do resultado para iterar novamente na tabela (ou usar o array $temp_result_for_sum)
    // $result_despesas->data_seek(0); // Não funciona bem com prepared statements e get_result() desta forma para re-iterar.
                                   // É melhor usar o array $temp_result_for_sum
}

?>

<h2>Relatório de Despesas</h2>

<form method="GET" action="index.php" class="form-filters">
    <input type="hidden" name="page" value="relatorios">
    <div class="filter-group">
        <label for="mes_ano">Mês/Ano:</label>
        <input type="month" id="mes_ano" name="mes_ano" value="<?php echo htmlspecialchars($filtro_mes_ano); ?>">
    </div>
    <div class="filter-group">
        <label for="categoria_id_filtro">Categoria:</label>
        <select id="categoria_id_filtro" name="categoria_id">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias_filtro as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $filtro_categoria_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    <a href="index.php?page=relatorios" class="btn btn-secondary btn-sm">Limpar Filtros</a>
</form>

<?php if ($result_despesas && count($temp_result_for_sum) > 0): ?>
    <div class="total-summary">
        <strong>Total das Despesas Filtradas: R$ <?php echo htmlspecialchars(number_format($total_despesas_periodo, 2, ',', '.')); ?></strong>
    </div>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Valor (R$)</th>
                <th>Data</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($temp_result_for_sum as $row): // Usando o array populado ?>
            <tr>
                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                <td><?php echo htmlspecialchars(number_format($row['valor'], 2, ',', '.')); ?></td>
                <td><?php echo htmlspecialchars($row['data_formatada']); ?></td>
                <td><?php echo htmlspecialchars($row['observacoes'] ? $row['observacoes'] : '-'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhuma despesa encontrada para os filtros selecionados ou no período atual.</p>
<?php endif; ?>

<?php
if ($stmt_despesas) {
    $stmt_despesas->close();
}
?>
<style>
.form-filters {
    background-color: #e9ecef;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 15px; /* Espaço entre os grupos de filtro */
    align-items: flex-end; /* Alinha os botões com a base dos inputs */
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    font-size: 0.9em;
    margin-bottom: 3px;
}
.filter-group input[type="month"],
.filter-group select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.total-summary {
    font-size: 1.2em;
    font-weight: bold;
    margin: 20px 0;
    padding: 10px;
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    border-radius: 4px;
}
.btn-sm {
    padding: 8px 12px; /* Ajuste para alinhar melhor com inputs */
    font-size: 0.9em;
}
</style>