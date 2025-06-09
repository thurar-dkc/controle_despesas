<?php
// gerar_pdf.php

// 1. Carregar o autoloader do Composer - ESSENCIAL E DEVE SER O PRIMEIRO!
// Esta linha diz ao PHP onde encontrar a classe Mpdf e outras dependências.
require_once 'vendor/autoload.php';

// 2. Carregar a conexão com o banco de dados
require_once 'db.php';

// Inicia uma instância do mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P' // 'P' para retrato (portrait), 'L' para paisagem (landscape)
]);

// --- LÓGICA PARA BUSCAR OS DADOS (A MESMA DO RELATÓRIO) ---

$filtro_mes_ano = isset($_GET['mes_ano']) ? $_GET['mes_ano'] : date('Y-m');
$filtro_categoria_id = isset($_GET['categoria_id']) ? $_GET['categoria_id'] : '';

$where_clauses = [];
$params = [];
$types = "";

$nome_categoria_filtro = 'Todas';
$nome_mes_ano_filtro = 'Todos';

if (!empty($filtro_mes_ano) && preg_match('/^\d{4}-\d{2}$/', $filtro_mes_ano)) {
    $where_clauses[] = "DATE_FORMAT(d.data_despesa, '%Y-%m') = ?";
    $params[] = $filtro_mes_ano;
    $types .= "s";
    
    list($ano, $mes) = explode('-', $filtro_mes_ano);
    $nome_mes_ano_filtro = "$mes/$ano";
}

if (!empty($filtro_categoria_id) && is_numeric($filtro_categoria_id)) {
    $where_clauses[] = "d.categoria_id = ?";
    $params[] = $filtro_categoria_id;
    $types .= "i";
    
    $sql_cat_nome = "SELECT nome FROM categorias WHERE id = ? LIMIT 1";
    if($stmt_cat = $mysqli->prepare($sql_cat_nome)){
        $stmt_cat->bind_param('i', $filtro_categoria_id);
        $stmt_cat->execute();
        $result_cat = $stmt_cat->get_result();
        if($cat_row = $result_cat->fetch_assoc()){
            $nome_categoria_filtro = $cat_row['nome'];
        }
        $stmt_cat->close();
    }
}

$sql_despesas = "SELECT d.descricao, d.valor, DATE_FORMAT(d.data_despesa, '%d/%m/%Y') as data_formatada, c.nome as nome_categoria
                 FROM despesas d
                 JOIN categorias c ON d.categoria_id = c.id";
if (!empty($where_clauses)) {
    $sql_despesas .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql_despesas .= " ORDER BY d.data_despesa DESC, d.id DESC";

$stmt_despesas = $mysqli->prepare($sql_despesas);
$total_despesas_periodo = 0;
$dados_tabela = [];
if ($stmt_despesas) {
    if (!empty($params)) {
        $stmt_despesas->bind_param($types, ...$params);
    }
    $stmt_despesas->execute();
    $result_despesas = $stmt_despesas->get_result();
    if($result_despesas){
        while($row = $result_despesas->fetch_assoc()){
            $total_despesas_periodo += $row['valor'];
            $dados_tabela[] = $row;
        }
    }
    $stmt_despesas->close();
}
$mysqli->close();

// --- CONSTRUÇÃO DO HTML PARA O PDF ---

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório de Despesas</title>
    <style>
        body { font-family: sans-serif; }
        h1 { color: #333; text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info-filtro { font-size: 12px; color: #555; text-align: center; margin-bottom: 20px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .total-summary { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Relatório de Despesas</h1>
    <div class="info-filtro">
        Filtros Aplicados: Mês/Ano: <strong><?php echo htmlspecialchars($nome_mes_ano_filtro); ?></strong> | 
        Categoria: <strong><?php echo htmlspecialchars($nome_categoria_filtro); ?></strong>
    </div>
    <?php if (count($dados_tabela) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Data</th>
                    <th>Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados_tabela as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                    <td><?php echo htmlspecialchars($row['data_formatada']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($row['valor'], 2, ',', '.')); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-summary">
            Total das Despesas: R$ <?php echo htmlspecialchars(number_format($total_despesas_periodo, 2, ',', '.')); ?>
        </div>
    <?php else: ?>
        <p>Nenhuma despesa encontrada para os filtros selecionados.</p>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();

$mpdf->WriteHTML($html);

$mpdf->Output('relatorio_despesas.pdf', 'D');

exit;
?>