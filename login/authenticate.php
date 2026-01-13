<?php
require_once '../config/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Por favor, preencha todos os campos.";
        $_SESSION['login_email'] = $email; // Mantém o email preenchido
        header("Location: index.php");
        exit();
    }

    $sql = "SELECT id, nome, password, nivel FROM usuario WHERE email = ? AND status = 'ativo'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $nome, $hashed_password, $nivel);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['nome'] = $nome;
                $_SESSION['nivel'] = $nivel;
                // Limpa qualquer erro anterior
                unset($_SESSION['login_error']);
                header("Location: ../dashboard.php");
                exit();
            } else {
                $_SESSION['login_error'] = "E-mail ou senha incorretos. Verifique suas credenciais e tente novamente.";
                $_SESSION['login_email'] = $email; // Mantém o email preenchido
            }
        } else {
            // Por segurança, não revela se o email existe ou não
            $_SESSION['login_error'] = "E-mail ou senha incorretos. Verifique suas credenciais e tente novamente.";
            $_SESSION['login_email'] = $email; // Mantém o email preenchido
        }
        $stmt->close();
    } else {
        $_SESSION['login_error'] = "Erro ao processar login. Por favor, tente novamente mais tarde.";
    }

    // Redireciona de volta para o login com mensagem de erro
    header("Location: index.php");
    exit();
} else {
    $_SESSION['login_error'] = "Método de requisição inválido.";
    header("Location: index.php");
    exit();
}
?>
