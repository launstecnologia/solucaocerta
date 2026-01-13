<?php
require_once '../config/config.php';
include '../includes/header.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM usuario WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
        } else {
            echo "Usuário não encontrado.";
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
            <h5 class="card-title fw-semibold mb-4">Editar Usuários</h5>
<form action="function.php" method="post">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" name="nome" class="form-control" value="<?php echo $row['nome']; ?>" required>
    </div>
    <div class="form-group">
        <label for="cpf">CPF</label>
        <input type="text" name="cpf" class="form-control" value="<?php echo $row['cpf']; ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo $row['email']; ?>" required>
    </div>
    <div class="form-group">
        <label for="nivel">Nível</label>
        <select name="nivel" class="form-control" required>
            <option value="admin" <?php if ($row['nivel'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="user" <?php if ($row['nivel'] == 'user') echo 'selected'; ?>>User</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
</form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
