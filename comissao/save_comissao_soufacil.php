<?php
require_once '../config/config.php';

$id = $_POST['id'] ?? null;
$id_representante = $_POST['id_representante'];
$faturamento = $_POST['faturamento'];
$comissao = $_POST['comissao'];
$mes = $_POST['mes'];
$ano = $_POST['ano'];
$status = $_POST['status'];
$obs = $_POST['obs'];

if ($id) {
    // Atualizar registro existente
    $sql = "UPDATE comissao_soufacil_rep SET id_representante = ?, faturamento = ?, comissao = ?, mes = ?, ano = ?, status = ?, obs = ?, data_alt = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iddiissi', $id_representante, $faturamento, $comissao, $mes, $ano, $status, $obs, $id);
} else {
    // Inserir novo registro
    $sql = "INSERT INTO comissao_soufacil_rep (id_representante, faturamento, comissao, mes, ano, status, obs, data_alt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iddiidd', $id_representante, $faturamento, $comissao, $mes, $ano, $status, $obs);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
