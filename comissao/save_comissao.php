<?php
require_once '../config/config.php';

$id = $_POST['id'] ?? null;
$id_rep = $_POST['id_rep'];
$mes = $_POST['mes'];
$ano = $_POST['ano'];
$popupar = $_POST['popupar'];
$comissao_popular = $_POST['comissao_popular'];
$cdc = $_POST['cdc'];
$comissao_cdc = $_POST['comissao_cdc'];
$comissao_total = $_POST['comissao_total'];
$status = $_POST['status'];
$obs = $_POST['obs'];

if ($id) {
    // Atualizar registro existente
    $sql = "UPDATE comissao_bcard_rep SET id_rep = ?, mes = ?, ano = ?, popupar = ?, comissao_popular = ?, cdc = ?, comissao_cdc = ?, comissao_total = ?, status = ?, obs = ?, data_alt = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iidsssssssi', $id_rep, $mes, $ano, $popupar, $comissao_popular, $cdc, $comissao_cdc, $comissao_total, $status, $obs, $id);
} else {
    // Inserir novo registro
    $sql = "INSERT INTO comissao_bcard_rep (id_rep, mes, ano, popupar, comissao_popular, cdc, comissao_cdc, comissao_total, status, obs, data_alt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iidsssssss', $id_rep, $mes, $ano, $popupar, $comissao_popular, $cdc, $comissao_cdc, $comissao_total, $status, $obs);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
