<?php
session_start();
require_once '../config/config.php';
include '../includes/header.php';

$id_cliente = $_GET['id'];

// Buscar informações do cliente
$sql_cliente = "SELECT * FROM cliente WHERE id = ?";
$stmt_cliente = $conn->prepare($sql_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();
$cliente = $result_cliente->fetch_assoc();

// Buscar todos os representantes
$sql_representantes = "SELECT * FROM representante";
$result_representantes = $conn->query($sql_representantes);
$representantes = [];
while ($row = $result_representantes->fetch_assoc()) {
    $representantes[] = $row;
}

// Buscar representantes associados ao cliente
$sql_cliente_representantes = "SELECT id_representante FROM cliente_representante WHERE id_cliente = ?";
$stmt_cliente_representantes = $conn->prepare($sql_cliente_representantes);
$stmt_cliente_representantes->bind_param("i", $id_cliente);
$stmt_cliente_representantes->execute();
$result_cliente_representantes = $stmt_cliente_representantes->get_result();
$cliente_representantes = [];
while ($row = $result_cliente_representantes->fetch_assoc()) {
    $cliente_representantes[] = $row['id_representante'];
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Editar Cliente</h5>
            <form action="update.php" method="post">
                <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_pessoa">Tipo de Pessoa</label>
                            <select name="tipo_pessoa" id="tipo_pessoa" class="form-control" required>
                                <option value="fisica" <?php if ($cliente['tipo_pessoa'] == 'fisica') echo 'selected'; ?>>Física</option>
                                <option value="juridica" <?php if ($cliente['tipo_pessoa'] == 'juridica') echo 'selected'; ?>>Jurídica</option>
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
                                <input type="text" name="cnpj" id="cnpj" value="<?= $cliente['cnpj']; ?> "class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="razao_social">Razão Social</label>
                                <input type="text" name="razao_social" value="<?= $cliente['razao_social']; ?>" id="razao_social" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nome_fantasia">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" value="<?= $cliente['nome_fantasia']; ?>" id="nome_fantasia" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3" id="ramo_atividade_group">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ramo_atividade">Inscrição Estadual</label>
                                <input type="text" name="insc_est" id="insc_est" value="<?= $cliente['insc_est']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="ramo_atividade">Ramo de Atividade</label>
                                <input type="text" name="ramo_atividade" value="<?= $cliente['ramo_atividade']; ?>" id="ramo_atividade" class="form-control">
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
                                <input type="text" name="telefone1" id="telefone1" value="<?= $cliente['telefone1']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="telefone2">WhatsApp</label>
                                <input type="text" name="telefone2" id="telefone2" value="<?= $cliente['telefone2']; ?>"  class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email"value="<?= $cliente['email']; ?>" class="form-control">
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
                                <input type="text" name="logradouro" value="<?= $cliente['logradouro']; ?>" id="logradouro" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="numero">Número</label>
                                <input type="text" name="numero" id="numero" value="<?= $cliente['numero']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="complemento">Complemento</label>
                                <input type="text" name="complemento" value="<?= $cliente['complemento']; ?>" id="complemento" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bairro">Bairro</label>
                                <input type="text" name="bairro" value="<?= $cliente['bairro']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" name="cidade" value="<?= $cliente['cidade']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="uf">UF</label>
                                <input type="text" name="uf" value="<?= $cliente['uf']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cep">CEP</label>
                                <input type="text" name="cep" value="<?= $cliente['cep']; ?>" class="form-control">
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
                            <input type="text" name="adm_nome" value="<?= $cliente['adm_nome']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_cpf">CPF do Administrador</label>
                            <input type="text" name="adm_cpf" value="<?= $cliente['adm_cpf']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_rg">RG do Administrador</label>
                            <input type="text" name="adm_rg" value="<?= $cliente['adm_rg']; ?>" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_nacionalidade">Nacionalidade</label>
                            <input type="text" name="adm_nacionalidade" value="<?= $cliente['adm_nacionalidade']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_est_civil">Estado Civil</label>
                            <input type="text" name="adm_est_civil" value="<?= $cliente['adm_est_civil']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_data_nasc">Data de Nascimento</label>
                            <input type="date" name="adm_data_nasc" value="<?= $cliente['adm_data_nasc']; ?>" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Endereço do Administrador -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="adm_end">Endereço</label>
                            <input type="text" name="adm_end" value="<?= $cliente['adm_end']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="adm_numero">Número</label>
                            <input type="text" name="adm_numero" value="<?= $cliente['adm_numero']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_complemento">Complemento</label>
                            <input type="text" name="adm_complemento" value="<?= $cliente['adm_complemento']; ?>" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_bairro">Bairro</label>
                            <input type="text" name="adm_bairro" value="<?= $cliente['adm_bairro']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="adm_cidade">Cidade</label>
                            <input type="text" name="adm_cidade" value="<?= $cliente['adm_cidade']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="adm_uf">UF</label>
                            <input type="text" name="adm_uf" value="<?= $cliente['adm_uf']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="adm_cep">CEP</label>
                            <input type="text" name="adm_cep" value="<?= $cliente['adm_cep']; ?>" class="form-control">
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
                            <input type="text" name="ger_nome" value="<?= $cliente['ger_nome']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ger_telefone1">Telefone</label>
                            <input type="text" name="ger_telefone1" value="<?= $cliente['ger_telefone1']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ger_email">E-mail</label>
                            <input type="email" name="ger_email" value="<?= $cliente['ger_email']; ?>" class="form-control">
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
                            <input type="text" name="fin_nome" value="<?= $cliente['fin_nome']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fin_telefone1">Celular</label>
                            <input type="text" name="fin_telefone1" value="<?= $cliente['fin_telefone1']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fin_email">E-mail</label>
                            <input type="email" name="fin_email" value="<?= $cliente['fin_email']; ?>" class="form-control">
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
                            <input type="text" name="banco" value="<?= $cliente['banco']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="agencia">Agência</label>
                            <input type="text" name="agencia" value="<?= $cliente['agencia']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="conta">Conta</label>
                            <input type="text" name="conta" value="<?= $cliente['conta']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pix">Chave PIX</label>
                            <input type="text" name="pix" value="<?= $cliente['pix']; ?>" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="favorecido">Nome do Favorecido</label>
                            <input type="text" name="favorecido" value="<?= $cliente['favorecido']; ?>" class="form-control">
                        </div>
                    </div>
                </div>


                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valor">Valor Adesão</label>
                            <input type="text" name="valor" value="<?= $cliente['valor']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="valor_extenso">Valor por Extenso</label>
                            <input type="text" name="valor_extenso" value="<?= $cliente['valor_extenso']; ?>" class="form-control">
                        </div>
                    </div>
                </div>
                <br>

                <br>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label><input type="checkbox" name="brasil_card" <?php if ($cliente['brasil_card']) echo 'checked'; ?>> Brasil Card</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="fgts" <?php if ($cliente['fgts']) echo 'checked'; ?>> FGTS</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="pagseguro" <?php if ($cliente['pagseguro']) echo 'checked'; ?>> PagSeguro</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="soufacil" <?php if ($cliente['soufacil']) echo 'checked'; ?>> Sou Fácil</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="fliper" <?php if ($cliente['fliper']) echo 'checked'; ?>> Fliper</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="parcela_facil" <?php if ($cliente['parcela_facil']) echo 'checked'; ?>> Parcela Fácil</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="boltcard" <?php if ($cliente['boltcard']) echo 'checked'; ?>> BoltCard</label>
                    </div>
                    <div class="col-md-2">
                        <label><input type="checkbox" name="parcelex" <?php if (isset($cliente['parcelex']) && $cliente['parcelex']) echo 'checked'; ?>> Parcelex</label>
                    </div>
                </div>

                <br>
                <div class="form-group mt-3">
                    <label for="representantes">Representantes</label>
                    <select name="representantes[]" id="representantes" class="form-control" multiple>
                        <?php foreach ($representantes as $representante) : ?>
                            <option value="<?php echo $representante['id']; ?>" 
                                <?php if (in_array($representante['id'], $cliente_representantes)) echo 'selected'; ?>>
                                <?php echo $representante['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">Salvar</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
