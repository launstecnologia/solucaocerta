<!-- Modal para alterar o status -->
<div class="modal fade" id="statusModalFliper" tabindex="-1" aria-labelledby="statusModalFliperLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalFliperLabel">Alterar Status Fliper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusFormFliper" action="update_status_fliper.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label for="statusAtual" class="form-label">Selecione o novo status</label>
                        <select class="form-select" id="statusAtual" name="status_atual" required>
                            <option value="" selected disabled>Escolha um status</option>
                            <option value="Cadastrado no Sistema">Cadastrado no Sistema</option>
                            <option value="Enviado Doc Fliper">Enviado Doc Fliper</option>
                            <option value="Gerado Contrato">Gerado Contrato</option>
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
                <button type="submit" class="btn btn-primary" form="statusFormSouFacil">Salvar</button>
            </div>
        </div>
    </div>
</div>
