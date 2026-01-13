<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel'];

    if (isset($_POST['id'])) {
        // Atualizar usuário existente
        $id = $_POST['id'];

        $sql = "UPDATE usuario SET nome=?, cpf=?, email=?, nivel=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $nome, $cpf, $email, $nivel, $id);
            if ($stmt->execute()) {
                echo "Usuário atualizado com sucesso.";
            } else {
                echo "Erro: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro na preparação: " . $conn->error;
        }
    } else {
        // Criar novo usuário
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        if ($password !== $password_confirm) {
            echo "As senhas não coincidem.";
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (nome, cpf, email, password, nivel, status) VALUES (?, ?, ?, ?, ?, 'ativo')";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $nome, $cpf, $email, $password_hash, $nivel);
            if ($stmt->execute()) {
                echo "Novo usuário criado com sucesso.";
            } else {
                echo "Erro: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro na preparação: " . $conn->error;
        }
    }
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>
