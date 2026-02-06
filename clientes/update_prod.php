<?php
require_once '../config/config.php';

// Função para atualizar produto
function updateProduct($conn, $sql, $params, $types) {
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
        $stmt->close();
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}

// Atualizar Brasil Card
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_brasil_card'])) {
    $id_cliente = $_POST['id_cliente'];
    $referencia1 = $_POST['referencia1'];
    $telefone1 = $_POST['telefone1'];
    $referencia2 = $_POST['referencia2'];
    $telefone2 = $_POST['telefone2'];
    $private = $_POST['private'];
    $porcentagem = $_POST['porcentagem'];
    $pdv = $_POST['pdv'];
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'];
    $cadastro_financeira = $_POST['cadastro_financeira'] ?? '';
    $status = $_POST['status'];
    $obs = $_POST['obs'];

    $sql = "UPDATE brasil_card SET referencia1=?, telefone1=?, referencia2=?, telefone2=?, private=?, porcentagem=?, pdv=?, data_liberacao_pdv=?, cadastro_financeira=?, status=?, obs=? WHERE id_cliente=?";
    $params = [$referencia1, $telefone1, $referencia2, $telefone2, $private, $porcentagem, $pdv, $data_liberacao_pdv, $cadastro_financeira, $status, $obs, $id_cliente];
    $types = "sssssssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Brasil Card atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar Check OK
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_check_ok'])) {
    $id_cliente = $_POST['id_cliente'];
    $plano = $_POST['plano'];
    $status = $_POST['status'];
    $obs = $_POST['obs'];

    $sql = "UPDATE check_ok SET plano=?, status=?, obs=? WHERE id_cliente=?";
    $params = [$plano, $status, $obs, $id_cliente];
    $types = "sssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Check OK atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar FGTS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_fgts'])) {
    $id_cliente = $_POST['id_cliente'];
    $link = $_POST['link'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $cadastro_financeira = $_POST['cadastro_financeira'] ?? '';
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'] ?? '';
    $status = $_POST['status'];
    $obs = $_POST['obs'];

    $sql = "UPDATE fgts SET link=?, email=?, senha=?, cadastro_financeira=?, data_liberacao_pdv=?, status=?, obs=? WHERE id_cliente=?";
    $params = [$link, $email, $senha, $cadastro_financeira, $data_liberacao_pdv, $status, $obs, $id_cliente];
    $types = "sssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do FGTS atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar Ok Antecipa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_ok_antecipa'])) {
    $id_cliente = $_POST['id_cliente'];
    $status = $_POST['status'];
    $obs = $_POST['obs'];

    $sql = "UPDATE ok_antecipa SET status=?, obs=? WHERE id_cliente=?";
    $params = [$status, $obs, $id_cliente];
    $types = "ssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Ok Antecipa atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar PagSeguro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_pagseguro'])) {
    $id_cliente = $_POST['id_cliente'];
    $email = $_POST['email'];
    $plano = $_POST['plano'];
    $senha = $_POST['senha'] ?? '';
    $cadastro_financeira = $_POST['cadastro_financeira'] ?? '';
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'] ?? '';
    $status = $_POST['status'];
    $obs = $_POST['obs'];

    $sql = "UPDATE pagseguro SET email=?, plano=?, senha=?, cadastro_financeira=?, data_liberacao_pdv=?, status=?, obs=? WHERE id_cliente=?";
    $params = [$email, $plano, $senha, $cadastro_financeira, $data_liberacao_pdv, $status, $obs, $id_cliente];
    $types = "sssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do PagSeguro atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar SouFacil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_soufacil'])) {
    $id_cliente = $_POST['id_cliente'];
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    $taxa_adm = isset($_POST['taxa_adm']) ? $_POST['taxa_adm'] : null;
    $condicoes = isset($_POST['condicoes']) ? $_POST['condicoes'] : null;
    $tipo_taxa = isset($_POST['tipo_taxa']) ? $_POST['tipo_taxa'] : null;
    $mensalidade = isset($_POST['mensalidade']) ? $_POST['mensalidade'] : null;
    $taxa_antecipado = isset($_POST['taxa_antecipado']) ? $_POST['taxa_antecipado'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $senha = isset($_POST['senha']) ? $_POST['senha'] : null;
    $cadastro_financeira = isset($_POST['cadastro_financeira']) ? $_POST['cadastro_financeira'] : null;
    $data_liberacao_pdv = isset($_POST['data_liberacao_pdv']) ? $_POST['data_liberacao_pdv'] : null;

    // Construir a query dinamicamente com apenas campos válidos
    $set_parts = [];
    $params = [];
    $types = "";
    
    if ($status !== null) {
        $set_parts[] = "status=?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($taxa_adm !== null) {
        $set_parts[] = "taxa_adm=?";
        $params[] = $taxa_adm;
        $types .= "s";
    }
    
    if ($condicoes !== null) {
        $set_parts[] = "condicoes=?";
        $params[] = $condicoes;
        $types .= "s";
    }
    
    if ($tipo_taxa !== null) {
        $set_parts[] = "tipo_taxa=?";
        $params[] = $tipo_taxa;
        $types .= "s";
    }
    
    if ($mensalidade !== null) {
        $set_parts[] = "mensalidade=?";
        $params[] = $mensalidade;
        $types .= "s";
    }
    
    if ($taxa_antecipado !== null) {
        $set_parts[] = "taxa_antecipado=?";
        $params[] = $taxa_antecipado;
        $types .= "s";
    }
    
    if ($email !== null) {
        $set_parts[] = "email=?";
        $params[] = $email;
        $types .= "s";
    }
    
    if ($senha !== null) {
        $set_parts[] = "senha=?";
        $params[] = $senha;
        $types .= "s";
    }
    
    if ($cadastro_financeira !== null) {
        $set_parts[] = "cadastro_financeira=?";
        $params[] = $cadastro_financeira;
        $types .= "s";
    }
    
    if ($data_liberacao_pdv !== null) {
        $set_parts[] = "data_liberacao_pdv=?";
        $params[] = $data_liberacao_pdv;
        $types .= "s";
    }
    
    if (!empty($set_parts)) {
        $params[] = $id_cliente;
        $types .= "i";
        
        $sql = "UPDATE soufacil SET " . implode(", ", $set_parts) . " WHERE id_cliente=?";
        updateProduct($conn, $sql, $params, $types);
        echo "<script>alert('Dados do Sou Fácil atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
    } else {
        echo "<script>alert('Nenhum campo para atualizar.'); history.back();</script>";
    }
}

// Atualizar Fliper
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_fliper'])) {
    $id_cliente = $_POST['id_cliente'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $status = $_POST['status'];
    $taxa_adm = $_POST['taxa_adm'];
    $condicoes = $_POST['condicoes'];
    $tipo_taxa = $_POST['tipo_taxa'];
    $mensalidade = $_POST['mensalidade'];
    $taxa_antecipado = $_POST['taxa_antecipado'];
    $cadastro_financeira = $_POST['cadastro_financeira'] ?? '';
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'] ?? '';

    $sql = "UPDATE fliper SET email=?, senha=?, status=?, taxa_adm=?, condicoes=?, tipo_taxa=?, mensalidade=?, taxa_antecipado=?, cadastro_financeira=?, data_liberacao_pdv=? WHERE id_cliente=?";
    $params = [$email, $senha, $status, $taxa_adm, $condicoes, $tipo_taxa, $mensalidade, $taxa_antecipado, $cadastro_financeira, $data_liberacao_pdv, $id_cliente];
    $types = "ssssssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Fliper atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar Parcela Fácil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_parcela_facil'])) {
    $id_cliente = $_POST['id_cliente'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $status = $_POST['status'];
    $plano = $_POST['plano'];
    $obs = $_POST['obs'];
    $cadastro_financeira = $_POST['cadastro_financeira'] ?? '';
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'] ?? '';

    $sql = "UPDATE parcela_facil SET email=?, senha=?, status=?, plano=?, obs=?, cadastro_financeira=?, data_liberacao_pdv=? WHERE id_cliente=?";
    $params = [$email, $senha, $status, $plano, $obs, $cadastro_financeira, $data_liberacao_pdv, $id_cliente];
    $types = "sssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Parcela Fácil atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar BoltCard
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_boltcard'])) {
    $id_cliente = $_POST['id_cliente'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $status = $_POST['status'];
    $plano = $_POST['plano'];
    $modelo_maquininha = $_POST['modelo_maquininha'];
    $chip = $_POST['chip'];
    $valor_maquina = $_POST['valor_maquina'];
    $obs = $_POST['obs'];

    $sql = "UPDATE boltcard SET email=?, senha=?, status=?, plano=?, modelo_maquininha=?, chip=?, valor_maquina=?, obs=? WHERE id_cliente=?";
    $params = [$email, $senha, $status, $plano, $modelo_maquininha, $chip, $valor_maquina, $obs, $id_cliente];
    $types = "ssssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do BoltCard atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}

// Atualizar Parcelex (apenas: taxa, data, status, observação, adesão, valor)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_parcelex'])) {
    $id_cliente = (int)($_POST['id_cliente'] ?? 0);
    $porcentagem = $_POST['porcentagem'] ?? '';
    $data_liberacao_pdv = $_POST['data_liberacao_pdv'] ?? '';
    $status = $_POST['status'] ?? 'Pendente';
    $obs = $_POST['obs'] ?? '';
    $adesao = $_POST['adesao'] ?? '';
    $valor = $_POST['valor'] ?? '';

    $sql = "UPDATE parcelex SET porcentagem=?, data_liberacao_pdv=?, status=?, obs=?, adesao=?, valor=? WHERE id_cliente=?";
    $params = [$porcentagem, $data_liberacao_pdv, $status, $obs, $adesao, $valor, $id_cliente];
    $types = "ssssssi";

    updateProduct($conn, $sql, $params, $types);
    echo "<script>alert('Dados do Parcelex atualizados com sucesso.'); window.location.href='detalhes.php?id=$id_cliente';</script>";
}
?>
