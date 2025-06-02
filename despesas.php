<?php
// despesas.php
// Este arquivo é incluído pelo index.php.

// Lógica para processar formulários de Despesa (Adicionar, Editar, Deletar)
$edit_mode_despesa = false;
$despesa_id = null;
$categoria_id_despesa = '';
$descricao_despesa = '';
$valor_despesa = '';
$data_despesa = date('Y-m-d'); // Data atual por padrão
$observacoes_despesa = '';

// Processar Ações (Salvar, Editar, Deletar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_expense'])) {
        $categoria_id_despesa = $_POST['categoria_id'];
        $descricao_despesa = trim($_POST['descricao_despesa']);
        $valor_despesa = str_replace(',', '.', $_POST['valor_despesa']); // Aceita vírgula como decimal
        $data_despesa = $_POST['data_despesa'];
        $observacoes_despesa = trim($_POST['observacoes_despesa']);
        $id_para_editar = isset($_POST['despesa_id']) ? $_POST['despesa_id'] : null;

        if (!empty($categoria_id_despesa) && !empty($descricao_despesa) && is_numeric($valor_despesa) && $valor_despesa > 0 && !empty($data_despesa)) {
            if (!empty($id_para_editar)) { // Edição
                $sql = "UPDATE despesas SET categoria_id = ?, descricao = ?, valor = ?, data_despesa = ?, observacoes = ? WHERE id = ?";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("isdssi", $categoria_id_despesa, $descricao_despesa, $valor_despesa, $data_despesa, $observacoes_despesa, $id_para_editar);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Despesa atualizada com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao atualizar despesa: " . $stmt->error;
                        $_SESSION['message_type'] = "danger";
                    }
                    $stmt->close();
                }
            } else { // Nova Despesa
                $sql = "INSERT INTO despesas (categoria_id, descricao, valor, data_despesa, observacoes) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("isdss", $categoria_id_despesa, $descricao_despesa, $valor_despesa, $data_despesa, $observacoes_despesa);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Despesa adicionada com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao adicionar despesa: " . $stmt->error;
                        $_SESSION['message_type'] = "danger";
                    }
                    $stmt->close();
                }
            }
        } else {
            $_SESSION['message'] = "Por favor, preencha todos os campos obrigatórios corretamente (Categoria, Descrição, Valor > 0, Data).";
            $_SESSION['message_type'] = "warning";
        }
        header("Location: index.php?page=despesas");
        exit;
    }
}

// Ação de Deletar Despesa
if (isset($_GET['action']) && $_GET['action'] == 'delete_expense' && isset($_GET['id'])) {
    $id_para_deletar = $_GET['id'];
    $sql = "DELETE FROM despesas WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_para_deletar);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Despesa deletada com sucesso!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Erro ao deletar despesa: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: index.php?page=despesas");
    exit;
}

// Ação de Editar Despesa (carregar dados para o formulário)
if (isset($_GET['action']) && $_GET['action'] == 'edit_expense' && isset($_GET['id'])) {
    $edit_mode_despesa = true;
    $despesa_id = $_GET['id'];
    $sql = "SELECT categoria_id, descricao, valor, data_despesa, observacoes FROM despesas WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $despesa_id);
        $stmt->execute();
        $stmt->bind_result($categoria_id_despesa, $descricao_despesa, $valor_despesa, $data_despesa, $observacoes_despesa);
        $stmt->fetch();
        $stmt->close();
    }
}

// Buscar categorias para o dropdown
$categorias_options = [];
$sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$result_cat = $mysqli->query($sql_cat);
if ($result_cat && $result_cat->num_rows > 0) {
    while ($cat = $result_cat->fetch_assoc()) {
        $categorias_options[] = $cat;
    }
    $result_cat->free();
}
?>

<h2>Gerenciar Despesas</h2>

<form action="index.php?page=despesas" method="POST" class="form-card">
    <h3><?php echo $edit_mode_despesa ? 'Editar Despesa' : 'Adicionar Nova Despesa'; ?></h3>
    <?php if ($edit_mode_despesa): ?>
        <input type="hidden" name="despesa_id" value="<?php echo htmlspecialchars($despesa_id); ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="categoria_id">Categoria:</label>
        <select id="categoria_id" name="categoria_id" required>
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categorias_options as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $categoria_id_despesa) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="descricao_despesa">Descrição:</label>
        <input type="text" id="descricao_despesa" name="descricao_despesa" value="<?php echo htmlspecialchars($descricao_despesa); ?>" required>
    </div>

    <div class="form-group">
        <label for="valor_despesa">Valor (R$):</label>
        <input type="text" id="valor_despesa" name="valor_despesa" value="<?php echo htmlspecialchars(number_format((float)$valor_despesa, 2, ',', '.')); ?>" placeholder="Ex: 25,50" required pattern="^\d+([,.]\d{1,2})?$">
    </div>

    <div class="form-group">
        <label for="data_despesa">Data da Despesa:</label>
        <input type="date" id="data_despesa" name="data_despesa" value="<?php echo htmlspecialchars($data_despesa); ?>" required>
    </div>

    <div class="form-group">
        <label for="observacoes_despesa">Observações (Opcional):</label>
        <textarea id="observacoes_despesa" name="observacoes_despesa"><?php echo htmlspecialchars($observacoes_despesa); ?></textarea>
    </div>

    <button type="submit" name="save_expense" class="btn btn-primary">
        <?php echo $edit_mode_despesa ? 'Atualizar Despesa' : 'Salvar Despesa'; ?>
    </button>
    <?php if ($edit_mode_despesa): ?>
        <a href="index.php?page=despesas" class="btn btn-secondary">Cancelar Edição</a>
    <?php endif; ?>
</form>

<hr style="margin: 30px 0;">

<h3>Despesas Registradas</h3>
<?php
$sql_despesas = "SELECT d.id, d.descricao, d.valor, DATE_FORMAT(d.data_despesa, '%d/%m/%Y') as data_formatada, d.observacoes, c.nome as nome_categoria
                 FROM despesas d
                 JOIN categorias c ON d.categoria_id = c.id
                 ORDER BY d.data_despesa DESC, d.id DESC"; // Ordena pela data mais recente
$result_despesas = $mysqli->query($sql_despesas);

if ($result_despesas && $result_despesas->num_rows > 0):
?>
    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Valor (R$)</th>
                <th>Data</th>
                <th>Observações</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_despesas->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                <td><?php echo htmlspecialchars(number_format($row['valor'], 2, ',', '.')); ?></td>
                <td><?php echo htmlspecialchars($row['data_formatada']); ?></td>
                <td><?php echo htmlspecialchars($row['observacoes'] ? $row['observacoes'] : '-'); ?></td>
                <td class="action-links">
                    <a href="index.php?page=despesas&action=edit_expense&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="index.php?page=despesas&action=delete_expense&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar esta despesa?');">Deletar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhuma despesa encontrada. Adicione uma nova despesa acima.</p>
<?php endif; ?>

<?php
if ($result_despesas) {
    $result_despesas->free();
}
?>
<style>
.form-card {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.btn-sm {
    padding: 5px 10px;
    font-size: 0.9em;
}
</style>