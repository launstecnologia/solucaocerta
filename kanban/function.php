<?php
require_once '../config/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) && $_POST['id'] != '' ? intval($_POST['id']) : null;
    
    // Dados básicos
    $nome = $_POST['nome'];
    $whatsapp = $_POST['whatsapp'];
    $email = $_POST['email'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $obs = $_POST['obs'] ?? '';
    $status_id = $_POST['status_id'];
    $usuario_id = $_POST['usuario_id'];
    $produto_id = $_POST['produto_id'] ?? '';
    
    // Dados adicionais
    $razao_social = $_POST['razao_social'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $origem = $_POST['origem'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $segmento = $_POST['segmento'] ?? '';
    
    // Interações
    $primeiro_contato = $_POST['primeiro_contato'] ?? null;
    $ultimo_contato = $_POST['ultimo_contato'] ?? null;
    $canal_comunicacao = $_POST['canal_comunicacao'] ?? '';
    $agendamento = $_POST['agendamento'] ?? null;
    
    // Oportunidades
    $etapa_funil = $_POST['etapa_funil'] ?? '';
    $valor_estimado = $_POST['valor_estimado'] ?? 0;
    $probabilidade = $_POST['probabilidade'] ?? 0;
    $data_fechamento = $_POST['data_fechamento'] ?? null;
    $condicoes_comerciais = $_POST['condicoes_comerciais'] ?? '';
    $motivo_perda = $_POST['motivo_perda'] ?? '';

    if ($id) {
        // Atualiza o lead existente
        $sql = "UPDATE lead 
                SET nome=?, whatsapp=?, email=?, cpf=?, obs=?, status_id=?, usuario_id=?, produto_id=?,
                    razao_social=?, endereco=?, numero=?, complemento=?, bairro=?, cidade=?, estado=?, cep=?,
                    origem=?, cargo=?, segmento=?, primeiro_contato=?, ultimo_contato=?, canal_comunicacao=?,
                    agendamento=?, etapa_funil=?, valor_estimado=?, probabilidade=?, data_fechamento=?,
                    condicoes_comerciais=?, motivo_perda=?
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiissssssssssssssssdsssi", 
            $nome, $whatsapp, $email, $cpf, $obs, $status_id, $usuario_id, $produto_id,
            $razao_social, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep,
            $origem, $cargo, $segmento, $primeiro_contato, $ultimo_contato, $canal_comunicacao,
            $agendamento, $etapa_funil, $valor_estimado, $probabilidade, $data_fechamento,
            $condicoes_comerciais, $motivo_perda, $id);
    } else {
        // Insere novo lead
        $sql = "INSERT INTO lead 
                (nome, whatsapp, email, cpf, obs, status_id, usuario_id, produto_id,
                 razao_social, endereco, numero, complemento, bairro, cidade, estado, cep,
                 origem, cargo, segmento, primeiro_contato, ultimo_contato, canal_comunicacao,
                 agendamento, etapa_funil, valor_estimado, probabilidade, data_fechamento,
                 condicoes_comerciais, motivo_perda)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssiissssssssssssssssdsss", 
            $nome, $whatsapp, $email, $cpf, $obs, $status_id, $usuario_id, $produto_id,
            $razao_social, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep,
            $origem, $cargo, $segmento, $primeiro_contato, $ultimo_contato, $canal_comunicacao,
            $agendamento, $etapa_funil, $valor_estimado, $probabilidade, $data_fechamento,
            $condicoes_comerciais, $motivo_perda);
    }

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Erro ao salvar lead: " . $stmt->error;
    }
}
?>
