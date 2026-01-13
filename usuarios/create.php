<?php
require_once '../config/config.php';
include '../includes/header.php';
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Cadastrar Usuários</h5>
<form action="function.php" method="post">
    <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" name="nome" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="cpf">CPF</label>
        <input type="text" name="cpf" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password">Senha</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="password_confirm">Confirmação de Senha</label>
        <input type="password" name="password_confirm" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="nivel">Nível</label>
        <select name="nivel" class="form-control" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
</form>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>
