<?php
require_once '../config/config.php';
include '../includes/header.php';

// Obter a lista de status
$sql_status = "SELECT * FROM ticket_status";
$result_status = $conn->query($sql_status);
$statuses = [];
while ($row = $result_status->fetch_assoc()) {
    $statuses[] = $row;
}

// Obter a lista de clientes
$sql_clientes = "SELECT id, nome_fantasia FROM cliente ORDER BY nome_fantasia ASC";
$result_clientes = $conn->query($sql_clientes);
$clientes = [];
while ($row = $result_clientes->fetch_assoc()) {
    $clientes[] = $row;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Criar Ticket</h5>
            <form action="save_ticket.php" method="post">
                <div class="form-group">
                    <label for="id_cliente">Cliente</label>
                    <select name="id_cliente" class="form-control" required>
                        <?php foreach ($clientes as $cliente) : ?>
                            <option value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nome_fantasia']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="id_status">Status</label>
                    <select name="id_status" class="form-control" required>
                        <?php foreach ($statuses as $status) : ?>
                            <option value="<?php echo $status['id']; ?>"><?php echo $status['status_name']; ?></option>
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
