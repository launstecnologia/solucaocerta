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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referencia1" class="form-label">Referência 1</label>
                                <input type="text" class="form-control" name="referencia1" id="referencia1" value="<?php echo $parcelex['referencia1'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefone1" class="form-label">Telefone 1</label>
                                <input type="text" class="form-control" name="telefone1" id="telefone1" value="<?php echo $parcelex['telefone1'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referencia2" class="form-label">Referência 2</label>
                                <input type="text" class="form-control" name="referencia2" id="referencia2" value="<?php echo $parcelex['referencia2'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefone2" class="form-label">Telefone 2</label>
                                <input type="text" class="form-control" name="telefone2" id="telefone2" value="<?php echo $parcelex['telefone2'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="private" class="form-label">Private</label>
                        <input type="text" class="form-control" name="private" id="private" value="<?php echo $parcelex['private'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="porcentagem" class="form-label">Porcentagem</label>
                        <input type="text" class="form-control" name="porcentagem" id="porcentagem" value="<?php echo $parcelex['porcentagem'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="pdv" class="form-label">PDV</label>
                        <input type="text" class="form-control" name="pdv" id="pdv" value="<?php echo $parcelex['pdv'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="data_liberacao_pdv" class="form-label">Data Liberação PDV</label>
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
                        <label for="obs" class="form-label">Observações</label>
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

