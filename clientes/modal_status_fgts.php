<!-- Modal para alterar o status -->
<div class="modal fade" id="statusModalFGTS" tabindex="-1" aria-labelledby="statusModalFGTSLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalFGTSLabel">Alterar Status FGTS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusFormFGTS" action="update_status_fgts.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label for="statusAtualFGTS" class="form-label">Selecione o novo status</label>
                        <select class="form-select" id="statusAtualFGTS" name="status_atual" required>
                            <option value="" selected disabled>Escolha um status</option>
                            <option value="Cadastrado no Sistema">Cadastrado no Sistema</option>
                            <option value="Enviado para FGTS">Enviado para FGTS</option>
                            <option value="Gerado Contrato">Gerado Contrato</option>
                            <option value="Aguardando Assinatura">Aguardando Assinatura</option>
                            <option value="Contrato Assinado">Contrato Assinado</option>
                            <option value="Acessos Criados">Acessos Criados</option>
                            <option value="Aguardando Treinamento">Aguardando Treinamento</option>
                            <option value="Treinamento Realizado">Treinamento Realizado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="statusFormFGTS">Salvar</button>
            </div>
        </div>
    </div>
</div>
