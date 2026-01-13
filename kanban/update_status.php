<?php
session_start();
require_once '../config/config.php';

// Habilitar logs de erro para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Definir header JSON
header('Content-Type: application/json');

// Log da requisição para debug
error_log("UPDATE STATUS - Início da requisição");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    error_log("UPDATE STATUS - Usuário não autenticado");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);
error_log("UPDATE STATUS - Dados recebidos: " . print_r($input, true));

if (!isset($input['lead_id']) || !isset($input['status_id'])) {
    error_log("UPDATE STATUS - Dados inválidos recebidos");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$leadId = (int)$input['lead_id'];
$statusId = (int)$input['status_id'];
$usuarioId = $_SESSION['id'];
$nivel = $_SESSION['nivel'] ?? 'user';

error_log("UPDATE STATUS - Lead ID: $leadId, Status ID: $statusId, Usuario ID: $usuarioId, Nivel: $nivel");

try {
    // Verificar se o lead existe
    $checkLeadQuery = "SELECT id, status_id, usuario_id FROM lead WHERE id = ?";
    $checkLeadStmt = $conn->prepare($checkLeadQuery);
    $checkLeadStmt->bind_param("i", $leadId);
    $checkLeadStmt->execute();
    $leadResult = $checkLeadStmt->get_result();
    
    if ($leadResult->num_rows === 0) {
        error_log("UPDATE STATUS - Lead não encontrado: $leadId");
        echo json_encode(['success' => false, 'message' => 'Lead não encontrado']);
        exit;
    }
    
    $leadData = $leadResult->fetch_assoc();
    error_log("UPDATE STATUS - Lead encontrado: " . print_r($leadData, true));
    
    // Verificar se o status existe
    $checkStatusQuery = "SELECT id, nome FROM lead_status WHERE id = ?";
    $checkStatusStmt = $conn->prepare($checkStatusQuery);
    $checkStatusStmt->bind_param("i", $statusId);
    $checkStatusStmt->execute();
    $statusResult = $checkStatusStmt->get_result();
    
    if ($statusResult->num_rows === 0) {
        error_log("UPDATE STATUS - Status não encontrado: $statusId");
        echo json_encode(['success' => false, 'message' => 'Status não encontrado']);
        exit;
    }
    
    $statusData = $statusResult->fetch_assoc();
    error_log("UPDATE STATUS - Status encontrado: " . print_r($statusData, true));

    // Check permissions for regular users
    if ($nivel == 'user') {
        // Verificar se o usuário tem permissão para editar este lead
        $currentStatusQuery = "SELECT nome FROM lead_status WHERE id = ?";
        $currentStatusStmt = $conn->prepare($currentStatusQuery);
        $currentStatusStmt->bind_param("i", $leadData['status_id']);
        $currentStatusStmt->execute();
        $currentStatusResult = $currentStatusStmt->get_result();
        $currentStatusData = $currentStatusResult->fetch_assoc();
        
        // Se o lead não é "novo" e não pertence ao usuário, bloquear
        if ($currentStatusData['nome'] !== 'novo' && $leadData['usuario_id'] != $usuarioId) {
            error_log("UPDATE STATUS - Sem permissão. Status atual: " . $currentStatusData['nome'] . ", Usuario do lead: " . $leadData['usuario_id']);
            echo json_encode(['success' => false, 'message' => 'Sem permissão para atualizar este lead']);
            exit;
        }
    }
    
    // Update lead status
    $updateQuery = "UPDATE lead SET status_id = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $statusId, $leadId);
    
    if ($updateStmt->execute()) {
        error_log("UPDATE STATUS - Status atualizado com sucesso");
        
        // Se o lead estava como "novo" e agora não está mais, atribuir ao usuário atual
        if ($statusData['nome'] !== 'novo' && (!$leadData['usuario_id'] || $leadData['usuario_id'] == 0)) {
            $assignQuery = "UPDATE lead SET usuario_id = ? WHERE id = ?";
            $assignStmt = $conn->prepare($assignQuery);
            $assignStmt->bind_param("ii", $usuarioId, $leadId);
            $assignStmt->execute();
            error_log("UPDATE STATUS - Lead atribuído ao usuário: $usuarioId");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Status atualizado com sucesso',
            'debug' => [
                'lead_id' => $leadId,
                'old_status' => $leadData['status_id'],
                'new_status' => $statusId,
                'usuario_id' => $usuarioId
            ]
        ]);
    } else {
        error_log("UPDATE STATUS - Erro ao executar UPDATE: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status no banco de dados']);
    }
    
} catch (Exception $e) {
    error_log("UPDATE STATUS - Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor', 'error' => $e->getMessage()]);
}
?>