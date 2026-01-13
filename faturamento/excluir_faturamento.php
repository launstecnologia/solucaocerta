<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if ($id) {
        try {
            $sql = "DELETE FROM fat_sou_facil WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao excluir registro: ' . $stmt->error);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
    }
}
