<?php
require_once '../config/config.php';
include '../includes/header.php';

$sql = "SELECT * FROM representante";
$result = $conn->query($sql);
?>

<style>
    .action-buttons {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .action-buttons form {
        display: inline;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Representante</h5>

            <a href="create.php" class="btn btn-primary mb-3">Adicionar Representante</a>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nome']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Editar</a>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#changePasswordModal<?php echo $row['id']; ?>">Senha</button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConfig" onclick="editarConfig(<?php echo $row['id']; ?>)">
                                        Configurar
                                    </button>
                                    <form action="function.php" method="post" onsubmit="return confirm('Tem certeza que deseja excluir este representante?');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="delete" value="1">
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </div>

                                <!-- Modal para trocar senha -->
                                <div class="modal fade" id="changePasswordModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="function.php" method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Senha</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <div class="form-group">
                                                        <label for="password">Nova Senha</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                    <button type="submit" name="change_password" class="btn btn-primary">Salvar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fim do modal -->
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfig" tabindex="-1" aria-labelledby="modalConfigLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfigLabel">Configurar Representante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formConfig">
                    <input type="hidden" id="configIdRepresentante" name="id_representante">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="statusBrasilCard" name="status_brasil_card">
                        <label class="form-check-label" for="statusBrasilCard">Status Brasil Card</label>
                    </div>
                    <div class="mb-3">
                        <label for="comPercBCard" class="form-label">Comissão Brasil Card (%)</label>
                        <input type="text"  class="form-control" id="comPercBCard" name="com_perc_bcard">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="statusSouFacil" name="status_soufacil">
                        <label class="form-check-label" for="statusSouFacil">Status Sou Fácil</label>
                    </div>
                    <div class="mb-3">
                        <label for="comPercSouFacil" class="form-label">Comissão Sou Fácil (%)</label>
                        <input type="text" class="form-control" id="comPercSouFacil" name="com_perc_soufacil">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="statusPagBank" name="status_pagbank">
                        <label class="form-check-label" for="statusPagBank">Status PagBank</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="statusAdesao" name="status_adesao">
                        <label class="form-check-label" for="statusAdesao">Status Adesão</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editarConfig(idRepresentante) {
        fetch(`obter_config.php?id_representante=${idRepresentante}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('configIdRepresentante').value = data.config.id_representante;
                    document.getElementById('statusBrasilCard').checked = data.config.status_brasil_card;
                    document.getElementById('comPercBCard').value = data.config.com_perc_bcard || '';
                    document.getElementById('statusSouFacil').checked = data.config.status_soufacil;
                    document.getElementById('comPercSouFacil').value = data.config.com_perc_soufacil || '';
                    document.getElementById('statusPagBank').checked = data.config.status_pagbank;
                    document.getElementById('statusAdesao').checked = data.config.status_adesao;
                } else {
                    alert('Erro ao carregar configurações: ' + data.error);
                }
            });
    }

    document.getElementById('formConfig').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('salvar_config.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Configurações salvas com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao salvar configurações: ' + data.error);
                }
            });
    });
</script>



<?php include '../includes/footer.php'; ?>