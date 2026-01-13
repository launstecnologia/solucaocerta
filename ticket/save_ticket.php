<?php
session_start();
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'];
    $id_usuario = $_SESSION['id']; // Assumindo que o ID do usuário está na sessão
    $id_status = $_POST['id_status'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_criacao = date('Y-m-d H:i:s');
    $data_atualizacao = date('Y-m-d H:i:s');

    $sql = "INSERT INTO tickets (id_cliente, id_usuario, id_status, titulo, descricao, data_criacao, data_atualizacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiissss", $id_cliente, $id_usuario, $id_status, $titulo, $descricao, $data_criacao, $data_atualizacao);
        if ($stmt->execute()) {
            echo "<script>alert('Ticket criado com sucesso.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
        $stmt->close();
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}
?>
