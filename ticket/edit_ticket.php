<?php
require_once '../config/config.php';

include '../includes/header.php';

$id_ticket = $_GET['id'];

// Buscar informações do ticket
$sql_ticket = "SELECT * FROM tickets WHERE id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $id_ticket);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();
$ticket = $result_ticket->fetch_assoc();

// Obter a lista de status
$sql_status = "SELECT * FROM ticket_status";
$result_status = $conn->query($sql_status);
$statuses = [];
while ($row = $result_status->fetch_assoc()) {
    $statuses[] = $row;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Editar Ticket</h5>
            <form action="update_ticket.php" method="post">
                <input type="hidden" name="id" value="<?php echo $id_ticket; ?>">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" name="titulo" class="form-control" value="<?php echo $ticket['titulo']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" class="form-control" required><?php echo $ticket['descricao']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="id_status">Status</label>
                    <select name="id_status" class="form-control" required>
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo $status['id']; ?>" <?php echo $status['id'] == $ticket['id_status'] ? 'selected' : ''; ?>>
                                <?php echo $status['status_name']; ?>
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
