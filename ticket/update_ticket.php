<?php
session_start();

require_once '../config/config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $id_status = $_POST['id_status'];
    $data_atualizacao = date('Y-m-d H:i:s');

    $sql = "UPDATE tickets SET titulo=?, descricao=?, id_status=?, data_atualizacao=? WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssisi", $titulo, $descricao, $id_status, $data_atualizacao, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Ticket atualizado com sucesso.'); window.location.href='view_ticket.php?id=$id';</script>";
        } else {
            echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
        $stmt->close();
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}
?>
