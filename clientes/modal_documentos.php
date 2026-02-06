<!-- Modal para upload de documentos -->
<div class="modal fade" id="uploadDocumentosModal" tabindex="-1" aria-labelledby="uploadDocumentosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentosModalLabel">Upload de Documentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" action="upload_documentos.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">

                    <!-- Selecionar tipo de documento -->
                    <div class="mb-3">
                        <label for="tipoDocumento" class="form-label">Tipo de Documento</label>
                        <select class="form-select" id="tipoDocumento" name="tipo_documento" required>
                            <option value="" disabled selected>Selecione o tipo</option>
                            <option value="Foto Interna">Foto Interna</option>
                            <option value="Foto Externa">Foto Externa</option>
                            <option value="Cartão CNPJ">Cartão CNPJ</option>
                            <option value="Contrato Social">Contrato Social</option>
                            <option value="CNH ou RG">CNH ou RG</option>
                            <option value="Comprovante Bancário">Comprovante Bancário</option>
                            <option value="Comprovante de Endereço">Comprovante de Endereço</option>
                        </select>
                    </div>

                    <!-- Selecionar produtos -->
                    <div class="mb-3">
                        <label for="produto" class="form-label">Associar ao Produto</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="soufacil" id="soufacil">
                            <label class="form-check-label" for="soufacil">Sou Fácil</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="brasilcard" id="brasilcard">
                            <label class="form-check-label" for="brasilcard">Brasil Card</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="fgts" id="fgts">
                            <label class="form-check-label" for="fgts">FGTS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="fliper" id="fliper">
                            <label class="form-check-label" for="fliper">Fliper</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="parcela_facil" id="parcela_facil">
                            <label class="form-check-label" for="parcela_facil">Parcela Fácil</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="boltcard" id="boltcard">
                            <label class="form-check-label" for="boltcard">BoltCard</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="produtos[]" value="parcelex" id="parcelex">
                            <label class="form-check-label" for="parcelex">Parcelex</label>
                        </div>
                    </div>

                    <!-- Upload do arquivo -->
                    <div class="mb-3">
                        <label for="arquivo" class="form-label">Selecionar Arquivo</label>
                        <input type="file" class="form-control" id="arquivo" name="arquivo" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary" form="uploadForm">Fazer Upload</button>
            </div>
        </div>
    </div>
</div>
