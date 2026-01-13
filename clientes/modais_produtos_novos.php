<!-- Modal para editar Parcela Fácil -->
<div class="modal fade" id="modalParcelaFacil" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Parcela Fácil</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formParcelaFacil" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_parcela_facil" value="1">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $parcela_facil['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" value="<?php echo $parcela_facil['senha'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($parcela_facil['status']) && $parcela_facil['status'] == 'Pendente') ? 'selected' : (!isset($parcela_facil['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($parcela_facil['status']) && $parcela_facil['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($parcela_facil['status']) && $parcela_facil['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($parcela_facil['status']) && $parcela_facil['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <select class="form-select" name="plano" id="plano">
                            <option value="Bronze R$ 149,00" <?php echo (isset($parcela_facil['plano']) && $parcela_facil['plano'] == 'Bronze R$ 149,00') ? 'selected' : ''; ?>>Bronze R$ 149,00</option>
                            <option value="Prata R$ 249,00" <?php echo (isset($parcela_facil['plano']) && $parcela_facil['plano'] == 'Prata R$ 249,00') ? 'selected' : ''; ?>>Prata R$ 249,00</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $parcela_facil['obs'] ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formParcelaFacil').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar BoltCard -->
<div class="modal fade" id="modalBoltCard" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">BoltCard</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBoltCard" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_boltcard" value="1">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $boltcard['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" value="<?php echo $boltcard['senha'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($boltcard['status']) && $boltcard['status'] == 'Pendente') ? 'selected' : (!isset($boltcard['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($boltcard['status']) && $boltcard['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($boltcard['status']) && $boltcard['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($boltcard['status']) && $boltcard['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <select class="form-select" name="plano" id="plano">
                            <option value="Classic" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Classic') ? 'selected' : ''; ?>>Classic</option>
                            <option value="Power" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Power') ? 'selected' : ''; ?>>Power</option>
                            <option value="Platinum" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Platinum') ? 'selected' : ''; ?>>Platinum</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modelo_maquininha" class="form-label">Modelo da Maquininha</label>
                        <select class="form-select" name="modelo_maquininha" id="modelo_maquininha">
                            <option value="D195" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'D195') ? 'selected' : ''; ?>>D195</option>
                            <option value="S920" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'S920') ? 'selected' : ''; ?>>S920</option>
                            <option value="Q92X" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'Q92X') ? 'selected' : ''; ?>>Q92X</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="chip" class="form-label">Chip</label>
                        <select class="form-select" name="chip" id="chip">
                            <option value="Claro" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'Claro') ? 'selected' : ''; ?>>Claro</option>
                            <option value="Vivo" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'Vivo') ? 'selected' : ''; ?>>Vivo</option>
                            <option value="TIM" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'TIM') ? 'selected' : ''; ?>>TIM</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valor_maquina" class="form-label">Valor da Máquina</label>
                        <input type="text" class="form-control" name="valor_maquina" id="valor_maquina" value="<?php echo $boltcard['valor_maquina'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $boltcard['obs'] ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formBoltCard').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>
