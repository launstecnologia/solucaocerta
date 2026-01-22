<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_ticket = $_POST['id_ticket'];
    $id_usuario = $_SESSION['id']; // Assumindo que o ID do usuário está na sessão
    $resposta = $_POST['resposta'];
    $data_resposta = date('Y-m-d H:i:s');

    $sql = "INSERT INTO ticket_responses (id_ticket, id_usuario, resposta, data_resposta) 
            VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiss", $id_ticket, $id_usuario, $resposta, $data_resposta);
        if ($stmt->execute()) {
            echo "<script>alert('Resposta enviada com sucesso.'); window.location.href='" . ticket_url("view_ticket.php?id=$id_ticket") . "';</script>";
        } else {
           echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
        $stmt->close();
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}
?>
