<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

$id_notificacao = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$id_usuario = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($id_notificacao <= 0 || $id_usuario <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

// Verificar se a notificação pertence ao usuário
$sql_check = "SELECT id FROM ticket_notificacoes WHERE id = ? AND id_usuario = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_notificacao, $id_usuario);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Marcar como lida
    $sql = "UPDATE ticket_notificacoes SET lida = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_notificacao);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt_check->close();
?>








