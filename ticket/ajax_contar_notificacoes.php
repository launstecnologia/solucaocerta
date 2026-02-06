<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

$id_usuario = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($id_usuario <= 0) {
    echo json_encode(['count' => 0]);
    exit;
}

$sql = "SELECT COUNT(*) as total FROM ticket_notificacoes WHERE id_usuario = ? AND lida = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

echo json_encode(['count' => (int)$row['total']]);
?>









