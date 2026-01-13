<?php
require_once '../config/config.php';
include '../includes/header.php';

// Buscar todos os tickets
$sql_tickets = "SELECT t.id, c.nome_fantasia AS nome_fantasia, t.titulo, t.data_criacao, s.status_name, u.nome as nome_usuario 
                FROM tickets t 
                JOIN ticket_status s ON t.id_status = s.id
                JOIN cliente c ON c.id = t.id_cliente
                JOIN usuario u ON t.id_usuario = u.id";
$result_tickets = $conn->query($sql_tickets);
if (!$result_tickets) {
    die("Erro na consulta SQL: " . $conn->error);
}


?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Lista de Tickets</h5>
            <a href="create_ticket.php" class="btn btn-primary">Criar Ticket</a>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estabelecimento</th>
                        <th>Título</th>
                        <th>Data de Criação</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ticket = $result_tickets->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $ticket['id']; ?></td>
                            <td><?php echo $ticket['nome_fantasia']; ?></td>
                            <td><?php echo $ticket['titulo']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($ticket['data_criacao'])); ?></td>
                            <td><?php echo $ticket['status_name']; ?></td>
                            <td>
                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm">Ver</a>
                                <a href="edit_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="reply_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary btn-sm">Responder</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
