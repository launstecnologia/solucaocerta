<?php
session_start();
require_once '../config/config.php';

function isUnique($conn, $field, $value, $id = null)
{
    $sql = "SELECT COUNT(*) FROM cliente WHERE $field = ?";
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
        die("Erro na preparação: " . $conn->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $id_user = $_SESSION['id'];

    // Campos do formulário
    $tipo_pessoa = $_POST['tipo_pessoa'];
    $cnpj = $tipo_pessoa == 'juridica' ? $_POST['cnpj'] : null;
    $razao_social = $tipo_pessoa == 'juridica' ? $_POST['razao_social'] : null;
    $nome_fantasia = $tipo_pessoa == 'juridica' ? $_POST['nome_fantasia'] : null;
    $insc_est = $_POST['insc_est'];
    $adm_nome = $_POST['adm_nome'];
    $adm_cpf = $_POST['adm_cpf'];
    $adm_rg = $_POST['adm_rg'];
    $adm_nacionalidade = $_POST['adm_nacionalidade'];
    $adm_est_civil = $_POST['adm_est_civil'];
    $adm_data_nasc = $_POST['adm_data_nasc'];
    $adm_end = $_POST['adm_end'];
    $adm_numero = $_POST['adm_numero'];
    $adm_complemento = $_POST['adm_complemento'];
    $adm_bairro = $_POST['adm_bairro'];
    $adm_cidade = $_POST['adm_cidade'];
    $adm_uf = $_POST['adm_uf'];
    $adm_cep = $_POST['adm_cep'];
    $ramo_atividade = $_POST['ramo_atividade'];
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
    $ger_nome = $_POST['ger_nome'];
    $ger_telefone1 = $_POST['ger_telefone1'];
    $ger_email = $_POST['ger_email'];
    $fin_nome = $_POST['fin_nome'];
    $fin_telefone1 = $_POST['fin_telefone1'];
    $fin_email = $_POST['fin_email'];
    $valor = $_POST['valor'];
    $valor_extenso = $_POST['valor_extenso'];
    $banco = $_POST['banco'];
    $agencia = $_POST['agencia'];
    $conta = $_POST['conta'];
    $pix = $_POST['pix'];
    $favorecido = $_POST['favorecido'];
    $data_register = date('Y-m-d H:i:s');

    // Verificação de produtos selecionados
    $brasil_card = isset($_POST['brasil_card']) ? 1 : 0;
    $fgts = isset($_POST['fgts']) ? 1 : 0;
    $pagseguro = isset($_POST['pagseguro']) ? 1 : 0;
    $soufacil = isset($_POST['soufacil']) ? 1 : 0;
    $fliper = isset($_POST['fliper']) ? 1 : 0;
    $parcela_facil = isset($_POST['parcela_facil']) ? 1 : 0;
    $boltcard = isset($_POST['boltcard']) ? 1 : 0;
    $parcelex = isset($_POST['parcelex']) ? 1 : 0;

    // Obter representantes selecionados
    $representantes = isset($_POST['representantes']) ? $_POST['representantes'] : [];

    // Criar novo registro
        $sql = "INSERT INTO cliente (
            id_user, tipo_pessoa, cnpj, razao_social, nome_fantasia, insc_est,
            adm_cpf, adm_rg, adm_nome, adm_nacionalidade, adm_est_civil, adm_data_nasc, 
            adm_end, adm_numero, adm_complemento, adm_bairro, adm_cidade, adm_uf, adm_cep, 
            ramo_atividade, telefone1, telefone2, email, logradouro, numero, complemento, bairro, 
            cidade, uf, cep, ger_nome, ger_telefone1, ger_email, fin_nome, fin_telefone1, fin_email, 
            valor, valor_extenso, data_register, banco, agencia, conta, pix, favorecido, brasil_card, 
            fgts, pagseguro, soufacil, fliper, parcela_facil, boltcard, parcelex
        ) VALUES (" . implode(', ', array_fill(0, 52, '?')) . ")";
        
        

        if ($stmt = $conn->prepare($sql)) {
            $types = str_repeat('s', 51) . 'i'; // 51 strings + 1 int (parcelex)
            $stmt->bind_param($types, 
            $id_user, $tipo_pessoa, $cnpj, $razao_social, $nome_fantasia, $insc_est,
            $adm_cpf, $adm_rg, $adm_nome, $adm_nacionalidade, $adm_est_civil, $adm_data_nasc,
            $adm_end, $adm_numero, $adm_complemento, $adm_bairro, $adm_cidade, $adm_uf, $adm_cep,
            $ramo_atividade, $telefone1, $telefone2, $email, $logradouro, $numero, $complemento, $bairro,
            $cidade, $uf, $cep, $ger_nome, $ger_telefone1, $ger_email, $fin_nome, $fin_telefone1, $fin_email,
            $valor, $valor_extenso, $data_register, $banco, $agencia, $conta, $pix, $favorecido, $brasil_card,
            $fgts, $pagseguro, $soufacil, $fliper, $parcela_facil, $boltcard, $parcelex
        );

            if ($stmt->execute()) {
                $id_cliente = $stmt->insert_id;

                // Inserir representantes vinculados
                foreach ($representantes as $id_representante) {
                    $stmt_rep = $conn->prepare("INSERT INTO cliente_representante (id_cliente, id_representante) VALUES (?, ?)");
                    $stmt_rep->bind_param("ii", $id_cliente, $id_representante);
                    $stmt_rep->execute();
                    $stmt_rep->close();
                }

                // Inserir produtos nas tabelas específicas com status "Pendente"
                if ($brasil_card) {
                    $stmt_prod = $conn->prepare("INSERT INTO brasil_card (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($fgts) {
                    $stmt_prod = $conn->prepare("INSERT INTO fgts (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($pagseguro) {
                    $stmt_prod = $conn->prepare("INSERT INTO pagseguro (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($soufacil) {
                    $stmt_prod = $conn->prepare("INSERT INTO soufacil (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($fliper) {
                    $stmt_prod = $conn->prepare("INSERT INTO fliper (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($parcela_facil) {
                    $stmt_prod = $conn->prepare("INSERT INTO parcela_facil (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($boltcard) {
                    $stmt_prod = $conn->prepare("INSERT INTO boltcard (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }
                
                if ($parcelex) {
                    $stmt_prod = $conn->prepare("INSERT INTO parcelex (id_cliente, status) VALUES (?, 'Pendente')");
                    $stmt_prod->bind_param("i", $id_cliente);
                    $stmt_prod->execute();
                    $stmt_prod->close();
                }

                echo "<script>alert('Novo registro criado com sucesso.'); window.location.href='detalhes.php?id=" . $id_cliente . "';</script>";
            } else {
                echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
            }

            $stmt->close();
        } else {
            die("Erro na preparação: " . $conn->error);
        }
}

$conn->close();
