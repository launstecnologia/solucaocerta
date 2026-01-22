<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

include '../includes/header.php';

$id_ticket = $_GET['id'];
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Responder Ticket</h5>
            <form action="<?php echo ticket_url('save_reply.php'); ?>" method="post">
                <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">
                <div class="form-group">
                    <label for="resposta">Resposta</label>
                    <textarea name="resposta" class="form-control" required></textarea>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">
                    <i class="fas fa-paper-plane me-1"></i> Enviar Resposta
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
