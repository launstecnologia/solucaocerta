<?php
require_once '../config/config.php';
include '../includes/header.php';

$id_ticket = $_GET['id'];
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Responder Ticket</h5>
            <form action="save_reply.php" method="post">
                <input type="hidden" name="id_ticket" value="<?php echo $id_ticket; ?>">
                <div class="form-group">
                    <label for="resposta">Resposta</label>
                    <textarea name="resposta" class="form-control" required></textarea>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">Salvar</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
