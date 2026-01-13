<?php
// Inicia o buffer de saída
ob_start();

// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
include '../includes/header.php';

// Consulta para listar os dados
$sql = "
    SELECT 
    cpr.*, 
    r.nome AS representante_nome
FROM 
    comissao_pag_rep cpr
LEFT JOIN 
    representante r ON FIND_IN_SET(r.id, cpr.id_rep) > 0
ORDER BY 
    ano DESC,
    FIELD(
        mes, 
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ) DESC;

";
$result = $conn->query($sql);

// Lógica para Adicionar (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $mes = $_POST['mes'];
    $ano = $_POST['ano'];
    $tpv = str_replace(',', '.', str_replace('.', '', $_POST['tpv'])); // Tratar valor decimal
    $markup = str_replace(',', '.', str_replace('.', '', $_POST['markup']));
    $comissao = str_replace(',', '.', str_replace('.', '', $_POST['comissao']));
    $status = $_POST['status'];
    $obs = $_POST['obs'];
    $id_rep = implode(',', $_POST['id_rep']); // Recebe múltiplos IDs de representantes

    $insertSQL = "
        INSERT INTO comissao_pag_rep (mes, ano, tpv, markup, comissao, status, id_rep, obs)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertSQL);
    $stmt->bind_param("siddssss", $mes, $ano, $tpv, $markup, $comissao, $status, $id_rep, $obs);

    if ($stmt->execute()) {
        header("Location: comissao_pagseguro_rep.php");
        exit();
    } else {
        echo "Erro ao adicionar registro: " . $conn->error;
    }
}

// Lógica para Editar (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $mes = $_POST['mes'];
    $ano = $_POST['ano'];
    $tpv = str_replace(',', '.', str_replace('.', '', $_POST['tpv']));
    $markup = str_replace(',', '.', str_replace('.', '', $_POST['markup']));
    $comissao = str_replace(',', '.', str_replace('.', '', $_POST['comissao']));
    $status = $_POST['status'];
    $obs = $_POST['obs'];
    $id_rep = implode(',', $_POST['id_rep']);

    $updateSQL = "
        UPDATE comissao_pag_rep
        SET mes = ?, ano = ?, tpv = ?, markup = ?, comissao = ?, status = ?, id_rep = ?, obs = ?
        WHERE id = ?
    ";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("siddssssi", $mes, $ano, $tpv, $markup, $comissao, $status, $id_rep, $obs, $id);
    


    if ($stmt->execute()) {
        header("Location: comissao_pagseguro_rep.php");
        exit();
    } else {
        echo "Erro ao editar registro: " . $conn->error;
    }
}

// Lógica para Excluir (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $deleteSQL = "DELETE FROM comissao_pag_rep WHERE id = ?";
    $stmt = $conn->prepare($deleteSQL);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: comissao_pagseguro_rep.php");
        exit();
    } else {
        echo "Erro ao excluir registro: " . $conn->error;
    }
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Comissão PagSeguro (paytime) - CRUD</h5>

            <!-- Botão para Abrir Modal de Adicionar -->
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Adicionar Comissão</button>

            <!-- Tabela de Dados -->
            <div class="table-responsive mt-3">
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Ano</th>
                            <th>TPV Total</th>
                            <th>Markup Total</th>
                            <th>Comissão Total</th>
                            <th>Status</th>
                            <th>Representante</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['mes']; ?></td>
                                <td><?php echo htmlspecialchars($row['ano']); ?></td>
                                <td><?php echo number_format($row['tpv'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($row['markup'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($row['comissao'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                                <td>
                                    <!-- Botão para Editar -->
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal-<?php echo $row['id']; ?>">Editar</button>

                                    <!-- Modal de Editar -->
                                    <div class="modal fade" id="editModal-<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel-<?php echo $row['id']; ?>">Editar Comissão</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="edit" value="1">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="mes-<?php echo $row['id']; ?>" class="form-label">Mês</label>
                                                            <input type="text" name="mes" class="form-control" id="mes-<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['mes']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="ano-<?php echo $row['id']; ?>" class="form-label">Ano</label>
                                                            <input type="number" name="ano" class="form-control" id="ano-<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['ano']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="tpv-<?php echo $row['id']; ?>" class="form-label">TPV</label>
                                                            <input type="text" name="tpv" class="form-control" id="tpv-<?php echo $row['id']; ?>" value="<?php echo str_replace('.', ',', $row['tpv']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="markup-<?php echo $row['id']; ?>" class="form-label">Markup</label>
                                                            <input type="text" name="markup" class="form-control" id="markup-<?php echo $row['id']; ?>" value="<?php echo str_replace('.', ',', $row['markup']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="comissao-<?php echo $row['id']; ?>" class="form-label">Comissão</label>
                                                            <input type="text" name="comissao" class="form-control" id="comissao-<?php echo $row['id']; ?>" value="<?php echo str_replace('.', ',', $row['comissao']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="status-<?php echo $row['id']; ?>" class="form-label">Status</label>
                                                            <input type="text" name="status" class="form-control" id="status-<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['status']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="obs-<?php echo $row['obs']; ?>" class="form-label">Observação</label>
                                                            <textarea name="obs" class="form-control" id="obs-<?php echo $row['id']; ?>" rows="3"><?php echo htmlspecialchars($row['obs']); ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="id_rep-<?php echo $row['id']; ?>" class="form-label">Representantes</label>
                                                            <select name="id_rep[]" id="id_rep-<?php echo $row['id']; ?>" class="form-control" multiple required>
                                                                <?php
                                                                $repResult = $conn->query("SELECT id, nome FROM representante");
                                                                while ($repRow = $repResult->fetch_assoc()) {
                                                                    $selected = in_array($repRow['id'], explode(',', $row['id_rep'])) ? 'selected' : '';
                                                                    echo "<option value='{$repRow['id']}' $selected>{$repRow['nome']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botão para Excluir -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este registro?');">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Adicionar -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Adicionar Comissão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="create" value="1">
                    <div class="mb-3">
                        <label for="mes" class="form-label">Mês</label>
                        <input type="text" name="mes" class="form-control" id="mes" required>
                    </div>
                    <div class="mb-3">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" name="ano" class="form-control" id="ano" required>
                    </div>
                    <div class="mb-3">
                        <label for="tpv" class="form-label">TPV</label>
                        <input type="text" name="tpv" class="form-control" id="tpv" required>
                    </div>
                    <div class="mb-3">
                        <label for="markup" class="form-label">Markup</label>
                        <input type="text" name="markup" class="form-control" id="markup">
                    </div>
                    <div class="mb-3">
                        <label for="comissao" class="form-label">Comissão</label>
                        <input type="text" name="comissao" class="form-control" id="comissao" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" name="status" class="form-control" id="status" required>
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observação</label>
                        <textarea name="obs" class="form-control" id="obs" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="id_rep" class="form-label">Representantes</label>
                        <select name="id_rep[]" id="id_rep" class="form-control" multiple required>
                            <?php
                            $repResult = $conn->query("SELECT id, nome FROM representante");
                            while ($repRow = $repResult->fetch_assoc()) {
                                echo "<option value='{$repRow['id']}'>{$repRow['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
// Encerra o buffer de saída
ob_end_flush();
?>
