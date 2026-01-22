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
$sql_ticket = "SELECT t.*, s.status_name, u.nome as nome_usuario, c.nome_fantasia AS nome_fantasia, c.cidade, c.uf 
               FROM tickets t 
               JOIN ticket_status s ON t.id_status = s.id
               JOIN usuario u ON t.id_usuario = u.id
               JOIN cliente c ON c.id = t.id_cliente
               WHERE t.id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $id_ticket);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();
$ticket = $result_ticket->fetch_assoc();

if (!$ticket) {
    header('Location: ' . ticket_url('index.php'));
    exit;
}

// Buscar anexos do ticket
$sql_anexos = "SELECT * FROM ticket_anexos WHERE id_ticket = ? ORDER BY data_upload ASC";
$stmt_anexos = $conn->prepare($sql_anexos);
$stmt_anexos->bind_param("i", $id_ticket);
$stmt_anexos->execute();
$result_anexos = $stmt_anexos->get_result();
$anexos = [];
while ($row = $result_anexos->fetch_assoc()) {
    $anexos[] = $row;
}
$stmt_anexos->close();

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
$stmt_responses->close();
?>

<style>
    .ticket-header {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .ticket-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    .info-item {
        padding: 10px;
        background: white;
        border-radius: 4px;
    }
    .info-item strong {
        display: block;
        color: #666;
        font-size: 12px;
        margin-bottom: 5px;
    }
    .info-item span {
        color: #333;
        font-size: 14px;
    }
    .data-retorno-alerta {
        background: #fff3cd;
        border: 1px solid #ffc107;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .data-retorno-urgente {
        background: #f8d7da;
        border: 1px solid #dc3545;
    }
    .anexos-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .anexo-card {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        background: white;
        min-width: 200px;
    }
    .anexo-card a {
        text-decoration: none;
        color: #007bff;
    }
    .anexo-card a:hover {
        text-decoration: underline;
    }
    .resposta-item {
        border-left: 3px solid #007bff;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 4px;
    }
</style>

<div class="container-fluid">
    <div class="ticket-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Ticket #<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['titulo']); ?></h4>
            <div>
                <a href="<?php echo ticket_url('edit_ticket.php?id=' . $id_ticket); ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>
                <a href="<?php echo ticket_url('reply_ticket.php?id=' . $id_ticket); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-reply me-1"></i> Responder
                </a>
                <a href="<?php echo ticket_url('index.php'); ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
        
        <div class="ticket-info">
            <div class="info-item">
                <strong>Cliente</strong>
                <span><?php echo htmlspecialchars($ticket['nome_fantasia']); ?></span>
                <?php if ($ticket['cidade']): ?>
                    <small class="text-muted"><?php echo htmlspecialchars($ticket['cidade']); ?><?php echo $ticket['uf'] ? '/' . $ticket['uf'] : ''; ?></small>
                <?php endif; ?>
            </div>
            <div class="info-item">
                <strong>Status</strong>
                <span><?php echo htmlspecialchars($ticket['status_name']); ?></span>
            </div>
            <div class="info-item">
                <strong>Criado por</strong>
                <span><?php echo htmlspecialchars($ticket['nome_usuario']); ?></span>
            </div>
            <div class="info-item">
                <strong>Data de Criação</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($ticket['data_criacao'])); ?></span>
            </div>
            <div class="info-item">
                <strong>Última Atualização</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($ticket['data_atualizacao'])); ?></span>
            </div>
            <?php if ($ticket['data_retorno']): ?>
                <div class="info-item">
                    <strong>Data de Retorno</strong>
                    <span><?php echo date('d/m/Y H:i', strtotime($ticket['data_retorno'])); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($ticket['data_retorno']): 
        $dataRetorno = new DateTime($ticket['data_retorno']);
        $agora = new DateTime();
        $diferenca = $agora->diff($dataRetorno);
        $minutosRestantes = ($dataRetorno->getTimestamp() - $agora->getTimestamp()) / 60;
        
        $classeAlerta = '';
        $mensagem = '';
        if ($minutosRestantes < 0) {
            $classeAlerta = 'data-retorno-urgente';
            $mensagem = '⚠️ ATENÇÃO: A data de retorno deste ticket já passou!';
        } elseif ($minutosRestantes <= 60) {
            $classeAlerta = 'data-retorno-urgente';
            $mensagem = '⚠️ URGENTE: O retorno deste ticket é em menos de 1 hora!';
        } elseif ($minutosRestantes <= 1440) {
            $classeAlerta = 'data-retorno-alerta';
            $mensagem = '⏰ Lembrete: O retorno deste ticket é em ' . round($minutosRestantes / 60) . ' horas.';
        }
        
        if ($mensagem):
    ?>
        <div class="<?php echo $classeAlerta; ?>">
            <strong><?php echo $mensagem; ?></strong>
            <br>
            <small>Data/Hora: <?php echo date('d/m/Y H:i', strtotime($ticket['data_retorno'])); ?></small>
        </div>
    <?php 
        endif;
    endif; 
    ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Descrição</h5>
            <p><?php echo nl2br(htmlspecialchars($ticket['descricao'])); ?></p>
        </div>
    </div>

    <?php if (!empty($anexos)): ?>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Anexos (<?php echo count($anexos); ?>)</h5>
            <div class="anexos-list">
                <?php foreach ($anexos as $anexo): 
                    $caminho_completo = '../' . $anexo['caminho_arquivo'];
                    $tamanho_kb = round($anexo['tamanho'] / 1024, 2);
                ?>
                    <div class="anexo-card">
                        <strong><?php echo htmlspecialchars($anexo['nome_arquivo']); ?></strong><br>
                        <small class="text-muted"><?php echo $tamanho_kb; ?> KB</small><br>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($anexo['data_upload'])); ?></small><br>
                        <?php if (file_exists($caminho_completo)): ?>
                            <a href="<?php echo htmlspecialchars($anexo['caminho_arquivo']); ?>" target="_blank" class="btn btn-sm btn-primary mt-2">Download</a>
                        <?php else: ?>
                            <span class="text-danger">Arquivo não encontrado</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Respostas (<?php echo count($responses); ?>)</h5>
            <?php if (empty($responses)): ?>
                <p class="text-muted">Nenhuma resposta ainda.</p>
            <?php else: ?>
                <?php foreach ($responses as $response): ?>
                    <div class="resposta-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong><?php echo htmlspecialchars($response['nome_usuario']); ?></strong>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($response['data_resposta'])); ?></small>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($response['resposta'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
