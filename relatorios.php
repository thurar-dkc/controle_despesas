<?php
// relatorios.php
// Este arquivo é incluído pelo index.php.

// Lógica para filtros (exemplo básico: por mês e ano, e por categoria)
$filtro_mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m'); // Mês e ano atual por padrão
$filtro_categoria_id = isset($_GET['categoria_id']) ? $_GET['categoria_id'] : '';

$where_clauses = [];
$params = [];
$types = "";

// Array para os parâmetros do link do PDF
$pdf_params = [];

if (!empty($filtro_mes_ano)) {
    if (preg_match('/^\d{4}-\d{2}$/', $filtro_mes_ano)) {
        $where_clauses[] = "DATE_FORMAT(d.data_despesa, '%Y-%m') = ?";
        $params[] = $filtro_mes_ano;
        $types .= "s";
        $pdf_params['mes_ano'] = $filtro_mes_ano;
    } else {
        $filtro_mes_ano = date('Y-m');
        $where_clauses[] = "DATE_FORMAT(d.data_despesa, '%Y-%m') = ?";
        $params[] = $filtro_mes_ano;
        $types .= "s";
        $pdf_params['mes_ano'] = $filtro_mes_ano;
    }
}

if (!empty($filtro_categoria_id) && is_numeric($filtro_categoria_id)) {
    $where_clauses[] = "d.categoria_id = ?";
    $params[] = $filtro_categoria_id;
    $types .= "i";
    $pdf_params['categoria_id'] = $filtro_categoria_id;
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
    $result_despesas = false;
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
$temp_result_for_sum = []; // Array para armazenar os resultados
if ($result_despesas) {
    while($row = $result_despesas->fetch_assoc()){
        $total_despesas_periodo += $row['valor'];
        $temp_result_for_sum[] = $row;
    }
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
    <div class="filter-buttons">
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="index.php?page=relatorios" class="btn btn-secondary btn-sm">Limpar Filtros</a>
        <!-- BOTÃO PARA BAIXAR PDF -->
        <a href="gerar_pdf.php?<?php echo http_build_query($pdf_params); ?>" target="_blank" class="btn btn-danger btn-sm">Baixar PDF</a>
    </div>
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
            <?php foreach ($temp_result_for_sum as $row): ?>
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
    flex-wrap: wrap; /* Permite que os itens quebrem a linha em telas menores */
    gap: 15px;
    align-items: flex-end;
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
.filter-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-left: auto; /* Empurra os botões para a direita */
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
    padding: 8px 12px;
    font-size: 0.9em;
}
.btn-danger {
    background-color: #d9534f;
    color: white;
}
.btn-danger:hover {
    background-color: #c9302c;
}
</style>