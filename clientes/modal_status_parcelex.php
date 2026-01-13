<!-- Modal para alterar o status -->
<div class="modal fade" id="statusModalParcelex" tabindex="-1" aria-labelledby="statusModalParcelexLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalParcelexLabel">Alterar Status Parcelex</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusFormParcelex" action="update_status_parcelex.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label for="statusAtualParcelex" class="form-label">Selecione o novo status</label>
                        <select class="form-select" id="statusAtualParcelex" name="status_atual" required>
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
                <button type="submit" class="btn btn-primary" form="statusFormParcelex">Salvar</button>
            </div>
        </div>
    </div>
</div>

