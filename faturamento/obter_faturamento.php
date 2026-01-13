<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "
                SELECT id, id_cliente, faturamento, qtd_venda, aprovada, reprovada, indice, mes, ano
                FROM fat_sou_facil
                WHERE id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $registro = $result->fetch_assoc();

            if ($registro) {
                echo json_encode(['success' => true, 'registro' => $registro]);
            } else {
                throw new Exception('Registro não encontrado.');
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
    }
}
