<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id_cliente'];
    $confirmation_text = $_POST['confirmation_text'];

    // Verificar se o texto de confirmação está correto
    if ($confirmation_text !== "Excluir Cliente") {
        die("Texto de confirmação incorreto.");
    }

    // Excluir cliente do banco de dados
    $sql = "DELETE FROM cliente WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);

    if ($stmt->execute()) {
        echo "Cliente excluído com sucesso!";
        header("Location: index.php");
    } else {
        echo "Erro ao excluir cliente: " . $conn->error;
    }
}
