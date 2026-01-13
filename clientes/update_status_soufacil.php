<?php
require_once '../config/config.php';

// Captura os dados do formulário
$id_cliente = $_POST['id_cliente'];
$status_atual = $_POST['status_atual'];

// Insere ou atualiza o status no banco
$sql = "INSERT INTO status_processo_soufacil (id_cliente, status_atual)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE data_alteracao = CURRENT_TIMESTAMP";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_cliente, $status_atual);

if ($stmt->execute()) {
    header("Location: detalhes.php?id=$id_cliente&status=success");
    exit;
} else {
    header("Location: detalhes.php?id=$id_cliente&status=error");
    exit;
}
?>
