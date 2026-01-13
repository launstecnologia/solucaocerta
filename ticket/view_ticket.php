<?php
require_once '../config/config.php';

include '../includes/header.php';

$id_ticket = $_GET['id'];

// Buscar informações do ticket
$sql_ticket = "SELECT t.*, s.status_name, u.nome as nome_usuario, c.nome_fantasia AS nome_fantasia FROM tickets t 
               JOIN ticket_status s ON t.id_status = s.id
               JOIN usuario u ON t.id_usuario = u.id
               JOIN cliente c ON c.id = t.id_cliente
               WHERE t.id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $id_ticket);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();
$ticket = $result_ticket->fetch_assoc();

// Buscar respostas do ticket
$sql_responses = "SELECT r.*, u.nome as nome_usuario FROM ticket_responses r 
                  JOIN usuario u ON r.id_usuario = u.id 
                  WHERE r.id_ticket = ? ORDER BY r.data_resposta ASC";
$stmt_responses = $conn->prepare($sql_responses);
$stmt_responses->bind_param("i", $id_ticket);
$stmt_responses->execute();
$result_responses = $stmt_responses->get_result();
$responses = [];
while ($row = $result_responses->fetch_assoc()) {
    $responses[] = $row;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Detalhes do Ticket</h5>
            <p><strong>ID Cliente:</strong> <?php echo $ticket['id_cliente']; ?></p>
            <p><strong>ID Cliente:</strong> <?php echo $ticket['nome_fantasia']; ?></p>
            <p><strong>Usuário:</strong> <?php echo $ticket['nome_usuario']; ?></p>
            <p><strong>Título:</strong> <?php echo $ticket['titulo']; ?></p>
            <p><strong>Descrição:</strong> <?php echo $ticket['descricao']; ?></p>
            <p><strong>Status:</strong> <?php echo $ticket['status_name']; ?></p>
            <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['data_criacao'])); ?></p>
            <p><strong>Última Atualização:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['data_atualizacao'])); ?></p>
            
            <h5 class="mt-4">Respostas</h5>
            <?php foreach ($responses as $response): ?>
                <div class="border p-3 mb-3">
                    <p><strong>Usuário:</strong> <?php echo $response['nome_usuario']; ?></p>
                    <p><?php echo nl2br($response['resposta']); ?></p>
                    <p><small><strong>Data:</strong> <?php echo $response['data_resposta']; ?></small></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
