<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lead_id = $_POST['lead_id'];
    $descricao = $_POST['descricao'];
    $tipo = $_POST['tipo'];
    $data_prevista = $_POST['data_prevista'];
    $usuario_id = $_POST['usuario_id'];

    $sql = "INSERT INTO lead_tarefas (lead_id, descricao, tipo, data_prevista, usuario_id)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $lead_id, $descricao, $tipo, $data_prevista, $usuario_id);

    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Erro: " . $stmt->error;
    }
}
