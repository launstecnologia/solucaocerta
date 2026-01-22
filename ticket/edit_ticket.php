<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

include '../includes/header.php';

$id_ticket = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_ticket <= 0) {
    header('Location: ' . ticket_url('index.php'));
    exit;
}

// Buscar informações do ticket
$sql_ticket = "SELECT * FROM tickets WHERE id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $id_ticket);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();
$ticket = $result_ticket->fetch_assoc();

if (!$ticket) {
    header('Location: index.php');
    exit;
}

// Obter a lista de status
$sql_status = "SELECT * FROM ticket_status";
$result_status = $conn->query($sql_status);
$statuses = [];
while ($row = $result_status->fetch_assoc()) {
    $statuses[] = $row;
}

// Formatar data_retorno para input datetime-local
$data_retorno_formatada = '';
if ($ticket['data_retorno']) {
    $data_retorno_formatada = date('Y-m-d\TH:i', strtotime($ticket['data_retorno']));
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Editar Ticket #<?php echo $ticket['id']; ?></h5>
            <form action="<?php echo ticket_url('update_ticket.php'); ?>" method="post">
                <input type="hidden" name="id" value="<?php echo $id_ticket; ?>">
                <div class="form-group">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($ticket['titulo']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição <span class="text-danger">*</span></label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="5" required><?php echo htmlspecialchars($ticket['descricao']); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_status">Status <span class="text-danger">*</span></label>
                            <select name="id_status" id="id_status" class="form-control" required>
                                <?php foreach ($statuses as $status) : ?>
                                    <option value="<?php echo $status['id']; ?>" <?php echo $status['id'] == $ticket['id_status'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_retorno">Data e Hora de Retorno</label>
                            <input type="datetime-local" name="data_retorno" id="data_retorno" class="form-control" value="<?php echo $data_retorno_formatada; ?>">
                            <small class="form-text text-muted">Opcional - Você será notificado quando chegar esta data/hora</small>
                        </div>
                    </div>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">
                    <i class="fas fa-save me-1"></i> Salvar Alterações
                </button>
                <a href="<?php echo ticket_url('view_ticket.php?id=' . $id_ticket); ?>" class="btn btn-secondary" style="float: right; margin-right: 10px;">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
