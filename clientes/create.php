<?php
require_once '../config/config.php';
require_once '../login/session.php';
protectPage();
include '../includes/header.php';

// Obter a lista de representantes
$sql = "SELECT id, nome FROM representante";
$result = $conn->query($sql);
$representantes = [];
while ($row = $result->fetch_assoc()) {
    $representantes[] = $row;
}
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Cadastrar Clientes</h5>
            <form action="function.php" method="post">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_pessoa">Tipo de Pessoa</label>
                            <select name="tipo_pessoa" id="tipo_pessoa" class="form-control" required>
                                <option value="juridica">Jurídica</option>
                                <option value="fisica">Física</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="juridica_fields">
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="card-title fw-semibold">Dados Empresarial</h5>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cnpj">CNPJ</label>
                                <input type="text" name="cnpj" id="cnpj" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="razao_social">Razão Social</label>
                                <input type="text" name="razao_social" id="razao_social" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nome_fantasia">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" id="nome_fantasia" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" id="ramo_atividade_group">
                    <div class="col-md-3">
                            <div class="form-group">
                                <label for="ramo_atividade">Inscrição Estadual</label>
                                <input type="text" name="insc_est" id="insc_est" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="ramo_atividade">Ramo de Atividade</label>
                                <input type="text" name="ramo_atividade" id="ramo_atividade" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="card-title fw-semibold">Contato Comercial</h5>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="telefone1">Telefone 1</label>
                                <input type="text" name="telefone1" id="telefone1" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="telefone2">WhatsApp</label>
                                <input type="text" name="telefone2" id="telefone2" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="card-title fw-semibold">Endereço Comercial</h5>
                        </div>
                    </div>


                    <div class="row mt-1">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="logradouro">Logradouro</label>
                                <input type="text" name="logradouro" id="logradouro" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="numero">Número</label>
                                <input type="text" name="numero" id="numero" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="complemento">Complemento</label>
                                <input type="text" name="complemento" id="complemento" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bairro">Bairro</label>
                                <input type="text" name="bairro" id="bairro" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" name="cidade" id="cidade" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="uf">UF</label>
                                <input type="text" name="uf" id="uf" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cep">CEP</label>
                                <input type="text" name="cep" id="cep" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Dados do Administrador -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="card-title fw-semibold">Dados do Administrador</h5>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="adm_nome">Nome do Administrador</label>
                            <input type="text" name="adm_nome" id="adm_nome" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_cpf">CPF do Administrador</label>
                            <input type="text" name="adm_cpf" id="adm_cpf" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_rg">RG do Administrador</label>
                            <input type="text" name="adm_rg" id="adm_rg" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_nacionalidade">Nacionalidade</label>
                            <input type="text" name="adm_nacionalidade" id="adm_nacionalidade" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_est_civil">Estado Civil</label>
                            <input type="text" name="adm_est_civil" id="adm_est_civil" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_data_nasc">Data de Nascimento</label>
                            <input type="date" name="adm_data_nasc" id="adm_data_nasc" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Endereço do Administrador -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="adm_end">Endereço</label>
                            <input type="text" name="adm_end" id="adm_end" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="adm_numero">Número</label>
                            <input type="text" name="adm_numero" id="adm_numero" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_complemento">Complemento</label>
                            <input type="text" name="adm_complemento" id="adm_complemento" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_bairro">Bairro</label>
                            <input type="text" name="adm_bairro" id="adm_bairro" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_cidade">Cidade</label>
                            <input type="text" name="adm_cidade" id="adm_cidade" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="adm_uf">UF</label>
                            <input type="text" name="adm_uf" id="adm_uf" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_cep">CEP</label>
                            <input type="text" name="adm_cep" id="adm_cep" class="form-control">
                        </div>
                    </div>
                </div>


                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="card-title fw-semibold">Dados do Gerente</h5>
                    </div>
                </div>

                <!-- Dados Gerenciais -->
                <div class="row mt-1">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ger_nome">Nome</label>
                            <input type="text" name="ger_nome" id="ger_nome" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ger_telefone1">Telefone</label>
                            <input type="text" name="ger_telefone1" id="ger_telefone1" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ger_email">E-mail</label>
                            <input type="email" name="ger_email" id="ger_email" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Dados Financeiros -->

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="card-title fw-semibold">Dados do Financeiro</h5>
                    </div>
                </div>
                <div class="row mt-31">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fin_nome">Nome</label>
                            <input type="text" name="fin_nome" id="fin_nome" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fin_telefone1">Celular</label>
                            <input type="text" name="fin_telefone1" id="fin_telefone1" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fin_email">E-mail</label>
                            <input type="email" name="fin_email" id="fin_email" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="card-title fw-semibold">Dados Bancário</h5>
                    </div>
                </div>
                <!-- Dados Bancários -->
                <div class="row mt-1">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="banco">Banco</label>
                            <input type="text" name="banco" id="banco" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="agencia">Agência</label>
                            <input type="text" name="agencia" id="agencia" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="conta">Conta</label>
                            <input type="text" name="conta" id="conta" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pix">Chave PIX</label>
                            <input type="text" name="pix" id="pix" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="favorecido">Nome do Favorecido</label>
                            <input type="text" name="favorecido" id="favorecido" class="form-control">
                        </div>
                    </div>
                </div>


                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valor">Valor Adesão</label>
                            <input type="text" name="valor" id="valor" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="valor_extenso">Valor por Extenso</label>
                            <input type="text" name="valor_extenso" id="valor_extenso" class="form-control">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label><input type="checkbox" name="brasil_card"> Brasil Card</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="fgts"> FGTS</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="pagseguro"> PagSeguro</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="soufacil"> Sou Fácil</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="fliper"> Fliper</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="parcela_facil"> Parcela Fácil</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="boltcard"> BoltCard</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="parcelex"> Parcelex</label>
                    </div>
                </div>
                <br>
                <div class="form-group mt-3">
                    <label for="representantes">Representantes</label>
                    <select name="representantes[]" id="representantes" class="form-control" multiple>
                        <?php foreach ($representantes as $representante) : ?>
                            <option value="<?php echo $representante['id']; ?>"><?php echo $representante['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">Salvar</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('tipo_pessoa').addEventListener('change', function() {
        var tipoPessoa = this.value;
        var juridicaFields = document.getElementById('juridica_fields');
        var ramoAtividadeGroup = document.getElementById('ramo_atividade_group');
        var cpfGroup = document.getElementById('cpf_group');
        var rgGroup = document.getElementById('rg_group');

        if (tipoPessoa === 'juridica') {
            juridicaFields.style.display = 'block';
            ramoAtividadeGroup.style.display = 'block';
            cpfGroup.style.display = 'block';
            rgGroup.style.display = 'block';
        } else {
            juridicaFields.style.display = 'none';
            ramoAtividadeGroup.style.display = 'none';
            cpfGroup.style.display = 'block';
            rgGroup.style.display = 'block';
        }
    });

    // Disparar o evento change ao carregar a página para definir a visibilidade inicial dos campos
    document.getElementById('tipo_pessoa').dispatchEvent(new Event('change'));

    document.getElementById('cnpj').addEventListener('blur', function() {
        var cnpj = this.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        if (cnpj.length === 14) {
            console.log('CNPJ formatado:', cnpj); // Log do CNPJ formatado
            fetch(`fetch_cnpj.php?cnpj=${cnpj}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta da API:', data); // Log da resposta da API
                    if (data.status === "OK") {
                        document.getElementById('razao_social').value = data.nome;
                        document.getElementById('nome_fantasia').value = data.fantasia;
                        document.getElementById('ramo_atividade').value = data.atividade_principal.length > 0 ? data.atividade_principal[0].text : '';
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('numero').value = data.numero;
                        document.getElementById('complemento').value = data.complemento;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.municipio;
                        document.getElementById('uf').value = data.uf;
                        document.getElementById('cep').value = data.cep;
                    } else {
                        alert('CNPJ não encontrado.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CNPJ:', error); // Log detalhado do erro
                    alert('Erro ao buscar CNPJ. Verifique o console para mais detalhes.');
                });
        }
    });
</script>


<?php include '../includes/footer.php'; ?>