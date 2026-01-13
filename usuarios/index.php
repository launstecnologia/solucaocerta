<?php
require_once '../config/config.php';
include '../includes/header.php';

$sql = "SELECT * FROM usuario";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Usuários</h5>

<a href="create.php" class="btn btn-primary">Adicionar Usuário</a>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF</th>
            <th>Email</th>
            <th>Nível</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['nome']; ?></td>
                <td><?php echo $row['cpf']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['nivel']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Editar</a>
                    <a href="toggle_status.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary">
                        <?php echo $row['status'] == 'ativo' ? 'Bloquear' : 'Desbloquear'; ?>
                    </a>
                    <button class="btn btn-info" data-toggle="modal" data-target="#passwordModal" data-id="<?php echo $row['id']; ?>">Trocar Senha</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="passwordForm" action="update_password.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="passwordModalLabel">Trocar Senha</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="userId">
          <div class="form-group">
            <label for="new_password">Nova Senha</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirmar Nova Senha</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$('#passwordModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var userId = button.data('id');
  var modal = $(this);
  modal.find('.modal-body #userId').val(userId);
});
</script>

<?php include '../includes/footer.php'; ?>
