<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
include '../includes/header.php';

// Consulta os dados da tabela `comissao_bcard_rep`
$sql = "
    SELECT 
        cbr.id, 
        cbr.id_rep, 
        r.nome AS representante_nome, 
        cbr.mes, 
        cbr.ano, 
        cbr.popupar, 
        cbr.comissao_popular, 
        cbr.cdc, 
        cbr.comissao_cdc, 
        cbr.comissao_total,
        cbr.status,
        cbr.obs,
        cbr.data_alt 
    FROM 
        comissao_bcard_rep cbr
    LEFT JOIN 
        representante r 
    ON 
        cbr.id_rep = r.id
    ORDER BY 
        cbr.ano DESC, cbr.mes DESC
";
$result = $conn->query($sql);

// Consulta os representantes para o dropdown
$sqlRepresentantes = "SELECT id, nome FROM representante ORDER BY nome ASC";
$representantesResult = $conn->query($sqlRepresentantes);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Comissão BCard - CRUD</h5>

            <!-- Botão para abrir o modal de cadastro -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="openModal()">
                Novo Registro
            </button>

            <!-- Tabela de Dados -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Representante</th>
                            <th>Competência</th>
                            <th>Popular</th>
                            <th>Comissão Popular</th>
                            <th>CDC</th>
                            <th>Comissão CDC</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Última Alteração</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                                <td><?php echo str_pad($row['mes'], 2, '0', STR_PAD_LEFT); ?>/<?php echo $row['ano']; ?></td>
                                <td><?php echo $row['popupar']; ?></td>
                                <td><?php echo $row['comissao_popular']; ?></td>
                                <td><?php echo $row['cdc']; ?></td>
                                <td><?php echo $row['comissao_cdc']; ?></td>
                                <td><?php echo $row['comissao_total']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['data_alt'])); ?></td>

                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm" onclick="editRecord(<?php echo htmlspecialchars(json_encode($row)); ?>)">Editar</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRecord(<?php echo $row['id']; ?>)">Excluir</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Formulário -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRecord">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormLabel">Novo Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="mb-3">
                        <label for="id_rep" class="form-label">Representante</label>
                        <select class="form-select" id="id_rep" name="id_rep" required>
                            <option value="">Selecione</option>
                            <?php while ($rep = $representantesResult->fetch_assoc()) { ?>
                                <option value="<?php echo $rep['id']; ?>"><?php echo htmlspecialchars($rep['nome']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mes" class="form-label">Mês</label>
                        <select class="form-select" id="mes" name="mes" required>
                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" class="form-control" id="ano" name="ano" required>
                    </div>
                    <div class="mb-3">
                        <label for="popupar" class="form-label">Popular</label>
                        <input type="text" class="form-control" id="popupar" name="popupar" required>
                    </div>
                    <div class="mb-3">
                        <label for="comissao_popular" class="form-label">Comissão Popular</label>
                        <input type="text" class="form-control" id="comissao_popular" name="comissao_popular" required>
                    </div>
                    <div class="mb-3">
                        <label for="cdc" class="form-label">CDC</label>
                        <input type="text" class="form-control" id="cdc" name="cdc" required>
                    </div>
                    <div class="mb-3">
                        <label for="comissao_cdc" class="form-label">Comissão CDC</label>
                        <input type="text" class="form-control" id="comissao_cdc" name="comissao_cdc" required>
                    </div>
                    <div class="mb-3">
                        <label for="comissao_total" class="form-label">Comissão Total</label>
                        <input type="text" class="form-control" id="comissao_total" name="comissao_total" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Faturamento">Faturamento</option>
                            <option value="Conciliação">Conciliação</option>
                            <option value="Pago">Pago</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" id="obs" name="obs" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('formRecord').reset();
        document.getElementById('id').value = '';
        document.getElementById('modalFormLabel').innerText = 'Novo Registro';
    }

    function editRecord(record) {
        document.getElementById('id').value = record.id;
        document.getElementById('id_rep').value = record.id_rep;
        document.getElementById('mes').value = record.mes;
        document.getElementById('ano').value = record.ano;
        document.getElementById('popupar').value = record.popupar;
        document.getElementById('comissao_popular').value = record.comissao_popular;
        document.getElementById('cdc').value = record.cdc;
        document.getElementById('comissao_cdc').value = record.comissao_cdc;
        document.getElementById('comissao_total').value = record.comissao_total;
        document.getElementById('status').value = record.status;
        document.getElementById('obs').value = record.obs;
        document.getElementById('modalFormLabel').innerText = 'Editar Registro';
    }


    function deleteRecord(id) {
        if (confirm('Tem certeza que deseja excluir este registro?')) {
            fetch('delete_comissao.php?id=' + id, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Registro excluído com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao excluir: ' + data.error);
                    }
                });
        }
    }

    document.getElementById('formRecord').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('save_comissao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registro salvo com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao salvar: ' + data.error);
                }
            });
    });
</script>

<?php include '../includes/footer.php'; ?>