<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $id_status = isset($_POST['id_status']) ? (int)$_POST['id_status'] : 1;
    $data_retorno = !empty($_POST['data_retorno']) ? date('Y-m-d H:i:s', strtotime($_POST['data_retorno'])) : null;
    $data_atualizacao = date('Y-m-d H:i:s');

    if ($id <= 0) {
        echo "<script>alert('ID do ticket inválido.'); history.back();</script>";
        exit;
    }

    // Verificar se a data_retorno mudou para criar nova notificação
    $sql_check = "SELECT data_retorno, id_usuario FROM tickets WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $ticket_antigo = $result_check->fetch_assoc();
    $stmt_check->close();

    $sql = "UPDATE tickets SET titulo=?, descricao=?, id_status=?, data_retorno=?, data_atualizacao=?, notificado=0 WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssissi", $titulo, $descricao, $id_status, $data_retorno, $data_atualizacao, $id);
        if ($stmt->execute()) {
            // Se a data_retorno foi alterada ou adicionada, criar/atualizar notificação
            if ($data_retorno && ($ticket_antigo['data_retorno'] != $data_retorno || !$ticket_antigo['data_retorno'])) {
                // Remover notificações antigas deste ticket
                $sql_del_notif = "DELETE FROM ticket_notificacoes WHERE id_ticket = ? AND tipo = 'retorno'";
                $stmt_del_notif = $conn->prepare($sql_del_notif);
                $stmt_del_notif->bind_param("i", $id);
                $stmt_del_notif->execute();
                $stmt_del_notif->close();

                // Criar nova notificação
                $sql_notif = "INSERT INTO ticket_notificacoes (id_ticket, id_usuario, tipo, mensagem) 
                              VALUES (?, ?, 'retorno', ?)";
                $mensagem = "Ticket #{$id} tem retorno agendado para " . date('d/m/Y H:i', strtotime($data_retorno));
                $stmt_notif = $conn->prepare($sql_notif);
                $id_usuario = $ticket_antigo['id_usuario'];
                $stmt_notif->bind_param("iis", $id, $id_usuario, $mensagem);
                $stmt_notif->execute();
                $stmt_notif->close();
            }
            
            echo "<script>alert('Ticket atualizado com sucesso.'); window.location.href='" . ticket_url("view_ticket.php?id=$id") . "';</script>";
        } else {
            echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
        $stmt->close();
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}
?>
