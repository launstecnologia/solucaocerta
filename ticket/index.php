<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

include '../includes/header.php';

$id_usuario = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

// Buscar notificações não lidas do usuário (verificar se a tabela existe)
$notificacoes_nao_lidas = [];
if ($id_usuario > 0) {
    // Verificar se a tabela ticket_notificacoes existe
    $check_table = $conn->query("SHOW TABLES LIKE 'ticket_notificacoes'");
    if ($check_table && $check_table->num_rows > 0) {
        $sql_notif = "SELECT n.*, t.titulo, t.id as ticket_id 
                      FROM ticket_notificacoes n
                      JOIN tickets t ON n.id_ticket = t.id
                      WHERE n.id_usuario = ? AND n.lida = 0
                      ORDER BY n.data_criacao DESC
                      LIMIT 10";
        $stmt_notif = $conn->prepare($sql_notif);
        if ($stmt_notif) {
            $stmt_notif->bind_param("i", $id_usuario);
            $stmt_notif->execute();
            $result_notif = $stmt_notif->get_result();
            while ($row = $result_notif->fetch_assoc()) {
                $notificacoes_nao_lidas[] = $row;
            }
            $stmt_notif->close();
        }
    }
}

// Construir query de tickets com filtros
$filtros = [];
$parametros = [];
$types = '';

if (isset($_GET['status']) && trim($_GET['status']) !== '') {
    $filtros[] = "t.id_status = ?";
    $parametros[] = (int)$_GET['status'];
    $types .= 'i';
}

if (isset($_GET['cliente']) && trim($_GET['cliente']) !== '') {
    $filtros[] = "c.nome_fantasia LIKE ?";
    $parametros[] = "%" . trim($_GET['cliente']) . "%";
    $types .= 's';
}

if (isset($_GET['data_inicial']) && isset($_GET['data_final']) && trim($_GET['data_inicial']) !== '' && trim($_GET['data_final']) !== '') {
    $filtros[] = "t.data_criacao BETWEEN ? AND ?";
    $parametros[] = trim($_GET['data_inicial']) . ' 00:00:00';
    $parametros[] = trim($_GET['data_final']) . ' 23:59:59';
    $types .= 'ss';
}

// Verificar se a tabela ticket_notificacoes existe para incluir na query
$check_table_notif = $conn->query("SHOW TABLES LIKE 'ticket_notificacoes'");
$tem_tabela_notif = ($check_table_notif && $check_table_notif->num_rows > 0);

// Buscar todos os tickets
if ($tem_tabela_notif && $id_usuario > 0) {
    $sql_tickets = "SELECT t.id, c.nome_fantasia AS nome_fantasia, t.titulo, t.data_criacao, t.data_retorno, 
                           s.status_name, u.nome as nome_usuario,
                           (SELECT COUNT(*) FROM ticket_notificacoes WHERE id_ticket = t.id AND id_usuario = ? AND lida = 0) as tem_notificacao
                    FROM tickets t 
                    JOIN ticket_status s ON t.id_status = s.id
                    JOIN cliente c ON c.id = t.id_cliente
                    JOIN usuario u ON t.id_usuario = u.id";
} else {
    $sql_tickets = "SELECT t.id, c.nome_fantasia AS nome_fantasia, t.titulo, t.data_criacao, t.data_retorno, 
                           s.status_name, u.nome as nome_usuario,
                           0 as tem_notificacao
                    FROM tickets t 
                    JOIN ticket_status s ON t.id_status = s.id
                    JOIN cliente c ON c.id = t.id_cliente
                    JOIN usuario u ON t.id_usuario = u.id";
}
                
if (!empty($filtros)) {
    $sql_tickets .= " WHERE " . implode(" AND ", $filtros);
}

$sql_tickets .= " ORDER BY t.data_criacao DESC";

$stmt_tickets = $conn->prepare($sql_tickets);
if ($stmt_tickets) {
    if ($tem_tabela_notif && $id_usuario > 0 && !empty($types)) {
        $stmt_tickets->bind_param("i" . $types, $id_usuario, ...$parametros);
    } elseif ($tem_tabela_notif && $id_usuario > 0) {
        $stmt_tickets->bind_param("i", $id_usuario);
    } elseif (!empty($parametros)) {
        $stmt_tickets->bind_param($types, ...$parametros);
    }
    $stmt_tickets->execute();
    $result_tickets = $stmt_tickets->get_result();
} else {
    die("Erro na preparação da consulta: " . $conn->error);
}

// Buscar status para filtro
$sql_status = "SELECT * FROM ticket_status";
$result_status = $conn->query($sql_status);
$statuses = [];
if ($result_status) {
    while ($row = $result_status->fetch_assoc()) {
        $statuses[] = $row;
    }
    $result_status->close();
} else {
    // Se não houver status, criar array vazio
    $statuses = [];
}

// Buscar tickets com retorno agendado para hoje
$hoje_inicio = date('Y-m-d') . ' 00:00:00';
$hoje_fim = date('Y-m-d') . ' 23:59:59';
$sql_retorno_hoje = "SELECT t.id, t.titulo, t.data_retorno, c.nome_fantasia, s.status_name,
                     TIMESTAMPDIFF(MINUTE, NOW(), t.data_retorno) as minutos_restantes
                     FROM tickets t
                     JOIN cliente c ON c.id = t.id_cliente
                     JOIN ticket_status s ON t.id_status = s.id
                     WHERE t.data_retorno IS NOT NULL 
                     AND t.data_retorno BETWEEN ? AND ?
                     ORDER BY t.data_retorno ASC";
$stmt_retorno = $conn->prepare($sql_retorno_hoje);
$tickets_retorno_hoje = [];
if ($stmt_retorno) {
    $stmt_retorno->bind_param("ss", $hoje_inicio, $hoje_fim);
    $stmt_retorno->execute();
    $result_retorno = $stmt_retorno->get_result();
    while ($row = $result_retorno->fetch_assoc()) {
        $tickets_retorno_hoje[] = $row;
    }
    $result_retorno->close();
    $stmt_retorno->close();
}
?>

<style>
    .notificacao-badge {
        position: relative;
        display: inline-block;
    }
    .notificacao-badge .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 10px;
    }
    .data-retorno-proximo {
        background: #fff3cd;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    .data-retorno-urgente {
        background: #f8d7da;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
    }
    .notificacoes-panel {
        position: fixed;
        top: 70px;
        right: 20px;
        width: 350px;
        max-height: 500px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
    }
    .notificacoes-panel.show {
        display: block;
    }
    .notificacao-item {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
    }
    .notificacao-item:hover {
        background: #f8f9fa;
    }
    .notificacao-item.lida {
        opacity: 0.6;
    }
    .modal-retorno-hoje .ticket-item {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: background 0.2s;
    }
    .modal-retorno-hoje .ticket-item:hover {
        background: #f8f9fa;
    }
    .modal-retorno-hoje .ticket-item:last-child {
        border-bottom: none;
    }
    .modal-retorno-hoje .badge-urgente {
        background: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    .modal-retorno-hoje .badge-proximo {
        background: #ffc107;
        color: #000;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title fw-semibold mb-0">Lista de Tickets</h5>
                <div>
                    <?php if (count($notificacoes_nao_lidas) > 0): ?>
                        <div class="notificacao-badge">
                            <button class="btn btn-info btn-sm" onclick="toggleNotificacoes()">
                                <i class="fas fa-bell me-1"></i> Notificações
                            </button>
                            <span class="badge"><?php echo count($notificacoes_nao_lidas); ?></span>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo ticket_url('create_ticket.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Criar Ticket
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status['id']; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $status['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="cliente">Cliente</label>
                            <input type="text" name="cliente" id="cliente" class="form-control" value="<?php echo isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : ''; ?>" placeholder="Nome do cliente">
                        </div>
                        <div class="col-md-2">
                            <label for="data_inicial">Data Inicial</label>
                            <input type="date" name="data_inicial" id="data_inicial" class="form-control" value="<?php echo isset($_GET['data_inicial']) ? htmlspecialchars($_GET['data_inicial']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="data_final">Data Final</label>
                            <input type="date" name="data_final" id="data_final" class="form-control" value="<?php echo isset($_GET['data_final']) ? htmlspecialchars($_GET['data_final']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <a href="<?php echo ticket_url('index.php'); ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estabelecimento</th>
                        <th>Título</th>
                        <th>Data de Criação</th>
                        <th>Data de Retorno</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_tickets && $result_tickets->num_rows > 0):
                        while ($ticket = $result_tickets->fetch_assoc()) : 
                        $temNotificacao = isset($ticket['tem_notificacao']) && $ticket['tem_notificacao'] > 0;
                        $dataRetornoClass = '';
                        $dataRetornoTexto = '';
                        if ($ticket['data_retorno']) {
                            $dataRetorno = new DateTime($ticket['data_retorno']);
                            $agora = new DateTime();
                            $minutosRestantes = ($dataRetorno->getTimestamp() - $agora->getTimestamp()) / 60;
                            
                            if ($minutosRestantes < 0) {
                                $dataRetornoClass = 'data-retorno-urgente';
                                $dataRetornoTexto = '⚠️ Passou';
                            } elseif ($minutosRestantes <= 60) {
                                $dataRetornoClass = 'data-retorno-urgente';
                                $dataRetornoTexto = '🔴 ' . round($minutosRestantes) . ' min';
                            } elseif ($minutosRestantes <= 1440) {
                                $dataRetornoClass = 'data-retorno-proximo';
                                $dataRetornoTexto = '⏰ ' . round($minutosRestantes / 60) . 'h';
                            } else {
                                $dataRetornoTexto = date('d/m/Y H:i', strtotime($ticket['data_retorno']));
                            }
                        }
                    ?>
                        <tr>
                            <td>
                                <?php echo $ticket['id']; ?>
                                <?php if ($temNotificacao): ?>
                                    <span class="badge bg-danger">!</span>
                                <?php endif; ?>
                                <?php if ($ticket['data_retorno']): ?>
                                    <?php
                                    $dataRetorno = new DateTime($ticket['data_retorno']);
                                    $agora = new DateTime();
                                    $minutosRestantes = ($dataRetorno->getTimestamp() - $agora->getTimestamp()) / 60;
                                    $badgeClass = '';
                                    $badgeIcon = '';
                                    if ($minutosRestantes < 0) {
                                        $badgeClass = 'bg-danger';
                                        $badgeIcon = '⚠️';
                                    } elseif ($minutosRestantes <= 60) {
                                        $badgeClass = 'bg-danger';
                                        $badgeIcon = '🔴';
                                    } elseif ($minutosRestantes <= 1440) {
                                        $badgeClass = 'bg-warning';
                                        $badgeIcon = '⏰';
                                    } else {
                                        $badgeClass = 'bg-info';
                                        $badgeIcon = '📅';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>" title="Retorno agendado: <?php echo date('d/m/Y H:i', strtotime($ticket['data_retorno'])); ?>">
                                        <?php echo $badgeIcon; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['nome_fantasia']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['data_criacao'])); ?></td>
                            <td>
                                <?php if ($ticket['data_retorno']): ?>
                                    <span class="<?php echo $dataRetornoClass; ?>">
                                        <?php echo $dataRetornoTexto; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['status_name']); ?></td>
                            <td>
                                <a href="<?php echo ticket_url('view_ticket.php?id=' . $ticket['id']); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye me-1"></i> Ver
                                </a>
                                <a href="<?php echo ticket_url('edit_ticket.php?id=' . $ticket['id']); ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                                <a href="<?php echo ticket_url('reply_ticket.php?id=' . $ticket['id']); ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-reply me-1"></i> Responder
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; 
                        $result_tickets->close();
                        $stmt_tickets->close();
                    else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-ticket-alt fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">Nenhum ticket encontrado.</p>
                                <a href="<?php echo ticket_url('create_ticket.php'); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Criar Primeiro Ticket
                                </a>
                            </td>
                        </tr>
                    <?php 
                        if ($result_tickets) {
                            $result_tickets->close();
                        }
                        if ($stmt_tickets) {
                            $stmt_tickets->close();
                        }
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Retornos do Dia -->
<div class="modal fade" id="modalRetornoHoje" tabindex="-1" aria-labelledby="modalRetornoHojeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalRetornoHojeLabel">
                    <i class="fas fa-clock me-2"></i>
                    Solicitações com Retorno Agendado para Hoje
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-retorno-hoje">
                <?php if (empty($tickets_retorno_hoje)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Nenhuma solicitação com retorno agendado para hoje.</p>
                    </div>
                <?php else: ?>
                    <p class="mb-3">
                        <strong><?php echo count($tickets_retorno_hoje); ?></strong> 
                        <?php echo count($tickets_retorno_hoje) == 1 ? 'solicitação precisa' : 'solicitações precisam'; ?> 
                        de retorno hoje.
                    </p>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($tickets_retorno_hoje as $ticket_hoje): 
                            $minutos = (int)$ticket_hoje['minutos_restantes'];
                            $horas = round($minutos / 60, 1);
                            $isUrgente = $minutos <= 60 || $minutos < 0;
                        ?>
                            <div class="ticket-item" onclick="window.location.href='<?php echo ticket_url('view_ticket.php?id=' . $ticket_hoje['id']); ?>'">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <strong class="me-2">Ticket #<?php echo $ticket_hoje['id']; ?></strong>
                                            <?php if ($isUrgente): ?>
                                                <span class="badge-urgente">
                                                    <?php if ($minutos < 0): ?>
                                                        ⚠️ Passou há <?php echo abs(round($minutos / 60, 1)); ?>h
                                                    <?php else: ?>
                                                        🔴 <?php echo $minutos; ?> min restantes
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-proximo">
                                                    ⏰ <?php echo $horas; ?>h restantes
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-1">
                                            <strong><?php echo htmlspecialchars($ticket_hoje['titulo']); ?></strong>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($ticket_hoje['nome_fantasia']); ?>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Status: <?php echo htmlspecialchars($ticket_hoje['status_name']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Retorno: <?php echo date('d/m/Y H:i', strtotime($ticket_hoje['data_retorno'])); ?>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <?php if (!empty($tickets_retorno_hoje)): ?>
                    <a href="<?php echo ticket_url('index.php'); ?>?data_inicial=<?php echo date('Y-m-d'); ?>&data_final=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Ver Todos os Tickets de Hoje
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Painel de Notificações -->
<div id="notificacoesPanel" class="notificacoes-panel">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <strong>Notificações</strong>
        <button class="btn btn-sm btn-light" onclick="toggleNotificacoes()">✕</button>
    </div>
    <div style="max-height: 400px; overflow-y: auto;">
        <?php if (empty($notificacoes_nao_lidas)): ?>
            <div class="p-3 text-center text-muted">Nenhuma notificação</div>
        <?php else: ?>
            <?php foreach ($notificacoes_nao_lidas as $notif): ?>
                <div class="notificacao-item" onclick="marcarLidaEIr(<?php echo $notif['id']; ?>, <?php echo $notif['ticket_id']; ?>)">
                    <div class="d-flex justify-content-between">
                        <strong>Ticket #<?php echo $notif['ticket_id']; ?></strong>
                        <small class="text-muted"><?php echo date('d/m H:i', strtotime($notif['data_criacao'])); ?></small>
                    </div>
                    <div><?php echo htmlspecialchars($notif['mensagem']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleNotificacoes() {
    const panel = document.getElementById('notificacoesPanel');
    panel.classList.toggle('show');
}

function marcarLidaEIr(idNotificacao, idTicket) {
    const ticketBase = window.location.pathname.includes('/ticket/') ? '' : 'ticket/';
    fetch(ticketBase + 'ajax_marcar_notificacao_lida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + idNotificacao
    }).then(() => {
        window.location.href = ticketBase + 'view_ticket.php?id=' + idTicket;
    });
}

// Verificar notificações a cada 30 segundos
setInterval(function() {
    const ticketBase = window.location.pathname.includes('/ticket/') ? '' : 'ticket/';
    fetch(ticketBase + 'ajax_contar_notificacoes.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                location.reload();
            }
        });
}, 30000);

// Abrir modal de retornos do dia automaticamente se houver tickets
<?php if (!empty($tickets_retorno_hoje)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const modalRetornoHoje = new bootstrap.Modal(document.getElementById('modalRetornoHoje'));
    modalRetornoHoje.show();
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
