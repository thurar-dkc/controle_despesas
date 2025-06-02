<?php
// categorias.php
// Este arquivo é incluído pelo index.php, então db.php e header.php já foram carregados.
// Não é necessário require_once 'db.php'; aqui.

// Lógica para processar formulários de Categoria (Adicionar, Editar, Deletar)
$edit_mode = false;
$categoria_id = null;
$nome_categoria = '';
$descricao_categoria = '';

// Processar Ações (Salvar, Editar, Deletar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ação de salvar (nova ou edição)
    if (isset($_POST['save_category'])) {
        $nome_categoria = trim($_POST['nome_categoria']);
        $descricao_categoria = trim($_POST['descricao_categoria']);
        $id_para_editar = isset($_POST['categoria_id']) ? $_POST['categoria_id'] : null;

        if (!empty($nome_categoria)) {
            if (!empty($id_para_editar)) { // Edição
                $sql = "UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("ssi", $nome_categoria, $descricao_categoria, $id_para_editar);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Categoria atualizada com sucesso!";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Erro ao atualizar categoria: " . $stmt->error;
                        $_SESSION['message_type'] = "danger";
                    }
                    $stmt->close();
                }
            } else { // Nova Categoria
                $sql_check = "SELECT id FROM categorias WHERE nome = ?";
                if($stmt_check = $mysqli->prepare($sql_check)){
                    $stmt_check->bind_param("s", $nome_categoria);
                    $stmt_check->execute();
                    $stmt_check->store_result();
                    if($stmt_check->num_rows == 0){
                        $sql = "INSERT INTO categorias (nome, descricao) VALUES (?, ?)";
                        if ($stmt = $mysqli->prepare($sql)) {
                            $stmt->bind_param("ss", $nome_categoria, $descricao_categoria);
                            if ($stmt->execute()) {
                                $_SESSION['message'] = "Categoria adicionada com sucesso!";
                                $_SESSION['message_type'] = "success";
                            } else {
                                $_SESSION['message'] = "Erro ao adicionar categoria: " . $stmt->error;
                                $_SESSION['message_type'] = "danger";
                            }
                            $stmt->close();
                        }
                    } else {
                        $_SESSION['message'] = "Erro: Já existe uma categoria com este nome.";
                        $_SESSION['message_type'] = "danger";
                    }
                    $stmt_check->close();
                }
            }
        } else {
            $_SESSION['message'] = "O nome da categoria é obrigatório.";
            $_SESSION['message_type'] = "warning";
        }
        // Redireciona para limpar o POST e evitar reenvio
        header("Location: index.php?page=categorias");
        exit;
    }
}

// Ação de Deletar
if (isset($_GET['action']) && $_GET['action'] == 'delete_category' && isset($_GET['id'])) {
    $id_para_deletar = $_GET['id'];
    // Adicionar verificação se a categoria está em uso antes de deletar
    $sql_check_despesas = "SELECT COUNT(*) as total FROM despesas WHERE categoria_id = ?";
    if($stmt_check = $mysqli->prepare($sql_check_despesas)){
        $stmt_check->bind_param("i", $id_para_deletar);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        if($row_check['total'] > 0){
            $_SESSION['message'] = "Não é possível excluir a categoria, pois ela está associada a ".$row_check['total']." despesa(s).";
            $_SESSION['message_type'] = "danger";
        } else {
            $sql = "DELETE FROM categorias WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("i", $id_para_deletar);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Categoria deletada com sucesso!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Erro ao deletar categoria: " . $stmt->error;
                    $_SESSION['message_type'] = "danger";
                }
                $stmt->close();
            }
        }
    } else {
        $_SESSION['message'] = "Erro ao verificar despesas associadas.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: index.php?page=categorias");
    exit;
}

// Ação de Editar (carregar dados para o formulário)
if (isset($_GET['action']) && $_GET['action'] == 'edit_category' && isset($_GET['id'])) {
    $edit_mode = true;
    $categoria_id = $_GET['id'];
    $sql = "SELECT nome, descricao FROM categorias WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        $stmt->bind_result($nome_categoria, $descricao_categoria);
        $stmt->fetch();
        $stmt->close();
    }
}

?>

<h2>Gerenciar Categorias</h2>

<form action="index.php?page=categorias" method="POST" class="form-card">
    <h3><?php echo $edit_mode ? 'Editar Categoria' : 'Adicionar Nova Categoria'; ?></h3>
    <?php if ($edit_mode): ?>
        <input type="hidden" name="categoria_id" value="<?php echo htmlspecialchars($categoria_id); ?>">
    <?php endif; ?>
    <div class="form-group">
        <label for="nome_categoria">Nome da Categoria:</label>
        <input type="text" id="nome_categoria" name="nome_categoria" value="<?php echo htmlspecialchars($nome_categoria); ?>" required>
    </div>
    <div class="form-group">
        <label for="descricao_categoria">Descrição (Opcional):</label>
        <textarea id="descricao_categoria" name="descricao_categoria"><?php echo htmlspecialchars($descricao_categoria); ?></textarea>
    </div>
    <button type="submit" name="save_category" class="btn btn-primary">
        <?php echo $edit_mode ? 'Atualizar Categoria' : 'Salvar Categoria'; ?>
    </button>
    <?php if ($edit_mode): ?>
        <a href="index.php?page=categorias" class="btn btn-secondary">Cancelar Edição</a>
    <?php endif; ?>
</form>

<hr style="margin: 30px 0;">

<h3>Categorias Existentes</h3>
<?php
$sql = "SELECT id, nome, descricao, DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') as data_formatada FROM categorias ORDER BY nome ASC";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0):
?>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Criada em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                <td><?php echo htmlspecialchars($row['descricao'] ? $row['descricao'] : '-'); ?></td>
                <td><?php echo htmlspecialchars($row['data_formatada']); ?></td>
                <td class="action-links">
                    <a href="index.php?page=categorias&action=edit_category&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="index.php?page=categorias&action=delete_category&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar esta categoria?');">Deletar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhuma categoria encontrada. Adicione uma nova categoria acima.</p>
<?php endif; ?>

<?php
if ($result) {
    $result->free();
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