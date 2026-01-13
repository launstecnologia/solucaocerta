<?php
require_once '../config/config.php';
include '../includes/header.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM representante WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
        } else {
            echo "Registro não encontrado.";
            exit();
        }
    } else {
        echo "Erro na preparação: " . $conn->error;
        exit();
    }
} else {
    echo "ID inválido.";
    exit();
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Editar Represenatante</h5>
            <form action="function.php" method="post">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo $row['nome']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?php echo $row['cpf']; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="telefone1">Celular</label>
                            <input type="text" name="telefone1" id="telefone1" class="form-control" value="<?php echo $row['telefone1']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="telefone2">Telefone</label>
                            <input type="text" name="telefone2" id="telefone2" class="form-control" value="<?php echo $row['telefone2']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $row['email']; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logradouro">Logradouro</label>
                            <input type="text" name="logradouro" class="form-control" value="<?php echo $row['logradouro']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="numero">Número</label>
                            <input type="text" name="numero" class="form-control" value="<?php echo $row['numero']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" name="complemento" class="form-control" value="<?php echo $row['complemento']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bairro">Bairro</label>
                            <input type="text" name="bairro" class="form-control" value="<?php echo $row['bairro']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?php echo $row['cidade']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="uf">UF</label>
                            <input type="text" name="uf" class="form-control" value="<?php echo $row['uf']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cep">CEP</label>
                            <input type="text" name="cep" class="form-control" value="<?php echo $row['cep']; ?>" required>
                        </div>
                    </div>
                </div>

                <br><br><br>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" style="float: right;" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>