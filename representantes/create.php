<?php
require_once '../config/config.php';
include '../includes/header.php';

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Cadastrar Representante</h5>
<br><br>
<form action="function.php" method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" name="nome" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control">
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="form-group">
                <label for="telefone1">Celular</label>
                <input type="text" name="telefone1" id="telefone1" class="form-control" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="telefone2">Telefone</label>
                <input type="text" name="telefone2" id="telefone2" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="logradouro">Logradouro</label>
                <input type="text" name="logradouro" class="form-control" required>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="numero">Número</label>
                <input type="text" name="numero" class="form-control" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="complemento">Complemento</label>
                <input type="text" name="complemento" class="form-control">
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="bairro">Bairro</label>
                <input type="text" name="bairro" class="form-control" required>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" name="cidade" class="form-control" required>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label for="uf">UF</label>
                <input type="text" name="uf" class="form-control" required>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" name="cep" id="cep" class="form-control" required>
            </div>
        </div>
    </div>

     <!-- Campos existentes -->
     <div class="row">
        <!-- Outros campos -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>
    </div>

    <div class="row mt-3">
    <div class="col-md-12">
    <button type="submit" style="float: right;" class="btn btn-primary">Salvar</button>
    </div>
    </div>
</form>



        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
