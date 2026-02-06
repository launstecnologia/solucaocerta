<!-- Modal para editar Parcelex -->
<div class="modal fade" id="modalParcelex" tabindex="-1" aria-labelledby="modalParcelexLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalParcelexLabel">Editar Parcelex</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formParcelex" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_parcelex" value="1">

                    <div class="mb-3">
                        <label for="porcentagem" class="form-label">Taxa</label>
                        <input type="text" class="form-control" name="porcentagem" id="porcentagem" value="<?php echo $parcelex['porcentagem'] ?? ''; ?>" placeholder="Ex: 2,5%">
                    </div>

                    <div class="mb-3">
                        <label for="data_liberacao_pdv" class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_liberacao_pdv" id="data_liberacao_pdv" value="<?php echo $parcelex['data_liberacao_pdv'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($parcelex['status']) && $parcelex['status'] == 'Pendente') ? 'selected' : (!isset($parcelex['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($parcelex['status']) && $parcelex['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($parcelex['status']) && $parcelex['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($parcelex['status']) && $parcelex['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="adesao" class="form-label">Adesão</label>
                        <select class="form-select" name="adesao" id="adesao">
                            <option value="">Selecione</option>
                            <option value="Sim" <?php echo (isset($parcelex['adesao']) && $parcelex['adesao'] === 'Sim') ? 'selected' : ''; ?>>Sim</option>
                            <option value="Não" <?php echo (isset($parcelex['adesao']) && $parcelex['adesao'] === 'Não') ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor</label>
                        <input type="text" class="form-control" name="valor" id="valor" value="<?php echo isset($parcelex['valor']) && $parcelex['valor'] !== '' && $parcelex['valor'] !== null ? htmlspecialchars($parcelex['valor']) : ''; ?>" placeholder="Ex: 1.500,00">
                    </div>

                    <div class="mb-3">
                        <label for="obs" class="form-label">Observação</label>
                        <textarea class="form-control" name="obs" id="obs" rows="3"><?php echo $parcelex['obs'] ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formParcelex').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>
