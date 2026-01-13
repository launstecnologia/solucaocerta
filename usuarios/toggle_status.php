<?php
require_once '../config/config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT status FROM usuario WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $new_status = ($row['status'] == 'ativo') ? 'inativo' : 'ativo';

            $sql_update = "UPDATE usuario SET status=? WHERE id=?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("si", $new_status, $id);
                if ($stmt_update->execute()) {
                    header("Location: index.php");
                    exit();
                } else {
                    echo "Erro ao atualizar status: " . $stmt_update->error;
                }
            } else {
                echo "Erro na preparação: " . $conn->error;
            }
        } else {
            echo "Usuário não encontrado.";
        }
    } else {
        echo "Erro na preparação: " . $conn->error;
    }
} else {
    echo "ID inválido.";
}
?>
