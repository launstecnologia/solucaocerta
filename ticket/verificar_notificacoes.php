<?php
/**
 * Script para verificar tickets com data de retorno próxima
 * Deve ser executado via cron a cada minuto ou 5 minutos
 */

require_once '../config/config.php';

// Buscar tickets com data_retorno próxima (próximos 60 minutos) e que ainda não foram notificados
$agora = date('Y-m-d H:i:s');
$limite = date('Y-m-d H:i:s', strtotime('+60 minutes'));

$sql = "SELECT t.id, t.id_usuario, t.data_retorno, t.titulo, t.notificado 
        FROM tickets t 
        WHERE t.data_retorno IS NOT NULL 
        AND t.data_retorno BETWEEN ? AND ?
        AND t.notificado = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $agora, $limite);
$stmt->execute();
$result = $stmt->get_result();

$tickets_notificados = 0;

while ($row = $result->fetch_assoc()) {
    $id_ticket = $row['id'];
    $id_usuario = $row['id_usuario'];
    $data_retorno = $row['data_retorno'];
    $titulo = $row['titulo'];
    
    // Calcular tempo restante
    $dataRetorno = new DateTime($data_retorno);
    $agoraObj = new DateTime();
    $minutosRestantes = ($dataRetorno->getTimestamp() - $agoraObj->getTimestamp()) / 60;
    
    // Criar mensagem baseada no tempo restante
    if ($minutosRestantes < 0) {
        $mensagem = "⚠️ ATENÇÃO: O ticket #{$id_ticket} ({$titulo}) já passou da data de retorno!";
    } elseif ($minutosRestantes <= 15) {
        $mensagem = "🔴 URGENTE: O ticket #{$id_ticket} ({$titulo}) tem retorno em " . round($minutosRestantes) . " minutos!";
    } elseif ($minutosRestantes <= 60) {
        $mensagem = "🟡 ATENÇÃO: O ticket #{$id_ticket} ({$titulo}) tem retorno em " . round($minutosRestantes) . " minutos!";
    } else {
        $mensagem = "⏰ Lembrete: O ticket #{$id_ticket} ({$titulo}) tem retorno em " . round($minutosRestantes / 60) . " horas.";
    }
    
    // Verificar se já existe notificação recente (evitar duplicatas)
    $sql_check = "SELECT id FROM ticket_notificacoes 
                  WHERE id_ticket = ? AND tipo = 'retorno' AND data_criacao > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_ticket);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows == 0) {
        // Criar notificação
        $sql_notif = "INSERT INTO ticket_notificacoes (id_ticket, id_usuario, tipo, mensagem) 
                      VALUES (?, ?, 'retorno', ?)";
        $stmt_notif = $conn->prepare($sql_notif);
        $stmt_notif->bind_param("iis", $id_ticket, $id_usuario, $mensagem);
        $stmt_notif->execute();
        $stmt_notif->close();
        
        $tickets_notificados++;
    }
    
    $stmt_check->close();
    
    // Marcar como notificado se já passou da data
    if ($minutosRestantes < 0) {
        $sql_update = "UPDATE tickets SET notificado = 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $id_ticket);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

$stmt->close();

// Log (opcional)
if ($tickets_notificados > 0) {
    error_log("Verificação de notificações: {$tickets_notificados} tickets notificados em " . date('Y-m-d H:i:s'));
}

?>






