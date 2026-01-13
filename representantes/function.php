<?php
session_start();
require_once '../config/config.php';

// Função para verificar se um campo é único
function isUnique($conn, $field, $value, $id = null) {
    $sql = "SELECT COUNT(*) FROM representante WHERE $field = ?";
    if ($id) {
        $sql .= " AND id != ?";
    }
    if ($stmt = $conn->prepare($sql)) {
        if ($id) {
            $stmt->bind_param("si", $value, $id);
        } else {
            $stmt->bind_param("s", $value);
        }
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count == 0;
    } else {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Excluir registro
        $id = $_POST['id'];

        $sql = "DELETE FROM representante WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Registro excluído com sucesso.'); window.location.href='index.php';</script>";
            } else {
                echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erro: " . $conn->error . "'); history.back();</script>";
        }

    } elseif (isset($_POST['change_password'])) {
        // Trocar senha
        $id = $_POST['id'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $sql = "UPDATE representante SET password = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $password, $id);
            if ($stmt->execute()) {
                echo "<script>alert('Senha alterada com sucesso.'); window.location.href='index.php';</script>";
            } else {
                echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['id'])) {
        // Atualizar registro existente
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $telefone1 = $_POST['telefone1'];
        $telefone2 = $_POST['telefone2'];
        $email = $_POST['email'];
        $logradouro = $_POST['logradouro'];
        $numero = $_POST['numero'];
        $complemento = $_POST['complemento'];
        $bairro = $_POST['bairro'];
        $cidade = $_POST['cidade'];
        $uf = $_POST['uf'];
        $cep = $_POST['cep'];

        if (isUnique($conn, 'cpf', $cpf, $id) && isUnique($conn, 'email', $email, $id)) {
            $sql = "UPDATE representante SET nome=?, cpf=?, telefone1=?, telefone2=?, email=?, logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, uf=?, cep=? WHERE id=?";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssssssssssi", $nome, $cpf, $telefone1, $telefone2, $email, $logradouro, $numero, $complemento, $bairro, $cidade, $uf, $cep, $id);

                if ($stmt->execute()) {
                    echo "<script>alert('Registro atualizado com sucesso.'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
                }

                $stmt->close();
            } else {
                echo "<script>alert('Erro: " . $conn->error . "'); history.back();</script>";
            }
        } else {
            echo "<script>alert('Representante já está cadastrado.'); history.back();</script>";
        }
    } else {
        // Criar novo registro
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $telefone1 = $_POST['telefone1'];
        $telefone2 = $_POST['telefone2'];
        $email = $_POST['email'];
        $logradouro = $_POST['logradouro'];
        $numero = $_POST['numero'];
        $complemento = $_POST['complemento'];
        $bairro = $_POST['bairro'];
        $cidade = $_POST['cidade'];
        $uf = $_POST['uf'];
        $cep = $_POST['cep'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $data_register = date('Y-m-d H:i:s');

        if (isUnique($conn, 'cpf', $cpf) && isUnique($conn, 'email', $email)) {
            $sql = "INSERT INTO representante (nome, cpf, telefone1, telefone2, email, logradouro, numero, complemento, bairro, cidade, uf, cep, password, data_register) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssssssssssss", $nome, $cpf, $telefone1, $telefone2, $email, $logradouro, $numero, $complemento, $bairro, $cidade, $uf, $cep, $password, $data_register);

                if ($stmt->execute()) {
                    echo "<script>alert('Registro realizado com sucesso.'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
                }

                $stmt->close();
            } else {
                echo "<script>alert('Erro: " . $conn->error . "'); history.back();</script>";
            }
        } else {
            echo "<script>alert('Representante já está cadastrado.'); history.back();</script>";
        }
    }
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>
