<?php
require_once '../config/config.php';
require_once '../login/session.php';
protectPage();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_parcela_facil'])) {
    $id_cliente = $_POST['id_cliente'];
    $plano = $_POST['plano'];
    $obs = $_POST['obs'];

    $sql = "UPDATE parcela_facil SET plano=?, obs=? WHERE id_cliente=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $plano, $obs, $id_cliente);

    if ($stmt->execute()) {
        echo "<script>alert('Dados do Parcela Fácil atualizados com sucesso!'); window.location.href='detalhes.php?id=$id_cliente';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar dados: " . $stmt->error . "'); history.back();</script>";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_boltcard'])) {
    $id_cliente = $_POST['id_cliente'];
    $plano = $_POST['plano'];
    $modelo_maquininha = $_POST['modelo_maquininha'];
    $chip = $_POST['chip'];
    $valor_maquina = $_POST['valor_maquina'];
    $obs = $_POST['obs'];

    $sql = "UPDATE boltcard SET plano=?, modelo_maquininha=?, chip=?, valor_maquina=?, obs=? WHERE id_cliente=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsi", $plano, $modelo_maquininha, $chip, $valor_maquina, $obs, $id_cliente);

    if ($stmt->execute()) {
        echo "<script>alert('Dados do BoltCard atualizados com sucesso!'); window.location.href='detalhes.php?id=$id_cliente';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar dados: " . $stmt->error . "'); history.back();</script>";
    }
    $stmt->close();
}

$conn->close();
?>
