<?php
require_once '../config/config.php';

$id = $_GET['id'];

$sql = "DELETE FROM comissao_soufacil_rep WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
