<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "As senhas não coincidem.";
        exit();
    }

    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $sql = "UPDATE usuario SET password=? WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $password_hash, $id);
        if ($stmt->execute()) {
            echo "Senha atualizada com sucesso.";
        } else {
            echo "Erro: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação: " . $conn->error;
    }

    $conn->close();
    header("Location: index.php");
} else {
    echo "Método de requisição inválido.";
}
?>
