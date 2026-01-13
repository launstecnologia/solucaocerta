<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $idCliente = $_POST['id_cliente'] ?? null;
    $faturamento = $_POST['faturamento'] ?? null;
    $qtdVenda = $_POST['qtd_venda'] ?? null;
    $aprovada = $_POST['aprovada'] ?? null;
    $reprovada = $_POST['reprovada'] ?? null;
    $indice = $_POST['indice'] ?? null;
    $mes = $_POST['mes'] ?? null;
    $ano = $_POST['ano'] ?? null;

    try {
        if ($id) {
            // Atualizar registro
            $sql = "
                UPDATE fat_sou_facil 
                SET id_cliente = ?, faturamento = ?, qtd_venda = ?, aprovada = ?, reprovada = ?, indice = ?, mes = ?, ano = ?, data_update = NOW()
                WHERE id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isiiisssi', $idCliente, $faturamento, $qtdVenda, $aprovada, $reprovada, $indice, $mes, $ano, $id);
        } else {
            // Inserir registro
            $sql = "
                INSERT INTO fat_sou_facil (id_cliente, faturamento, qtd_venda, aprovada, reprovada, indice, mes, ano, data_update)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isiiisss', $idCliente, $faturamento, $qtdVenda, $aprovada, $reprovada, $indice, $mes, $ano);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registro salvo com sucesso!']);
        } else {
            throw new Exception('Erro ao salvar registro: ' . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
