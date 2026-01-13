
<!-- Modal para alterar o status -->
<div class="modal fade" id="statusModalBrasilCard" tabindex="-1" aria-labelledby="statusModalBrasilCardLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalBrasilCardLabel">Alterar Status Brasil Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusFormBrasilCard" action="update_status_brasilcard.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label for="statusAtualBrasilCard" class="form-label">Selecione o novo status</label>
                        <select class="form-select" id="statusAtualBrasilCard" name="status_atual" required>
                            <option value="" selected disabled>Escolha um status</option>
                            <option value="Cadastro no Sistema">Cadastro no Sistema</option>
                            <option value="Enviado CRM">Enviado CRM</option>
                            <option value="Contrato Enviado">Contrato Enviado</option>
                            <option value="PDV Gerado">PDV Gerado</option>
                            <option value="Aguardando Treinamento">Aguardando Treinamento</option>
                            <option value="Treinamento Realizado">Treinamento Realizado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="statusFormBrasilCard">Salvar</button>
            </div>
        </div>
    </div>
</div>
