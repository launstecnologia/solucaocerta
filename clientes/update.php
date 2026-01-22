<?php
session_start();
require_once '../config/config.php';

function isUnique($conn, $field, $value, $id) {
    $sql = "SELECT COUNT(*) FROM cliente WHERE $field = ? AND id != ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count == 0;
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}

function checkProduct($conn, $table, $id_cliente) {
    $sql = "SELECT COUNT(*) FROM $table WHERE id_cliente = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
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

    // Representantes selecionados
    $representantes = isset($_POST['representantes']) ? $_POST['representantes'] : [];

    // Atualizar registro existente
        $sql = "UPDATE cliente SET tipo_pessoa=?, cnpj=?, razao_social=?, nome_fantasia=?, insc_est=?, 
            adm_cpf=?, adm_rg=?, adm_nome=?, adm_nacionalidade=?, adm_est_civil=?, adm_data_nasc=?, 
            adm_end=?, adm_numero=?, adm_complemento=?, adm_bairro=?, adm_cidade=?, adm_uf=?, adm_cep=?, 
            ramo_atividade=?, telefone1=?, telefone2=?, email=?, logradouro=?, numero=?, complemento=?, bairro=?, 
            cidade=?, uf=?, cep=?, ger_nome=?, ger_telefone1=?, ger_email=?, fin_nome=?, fin_telefone1=?, fin_email=?, 
            valor=?, valor_extenso=?, banco=?, agencia=?, conta=?, pix=?, favorecido=?, brasil_card=?, 
            fgts=?, pagseguro=?, soufacil=?, fliper=?, parcela_facil=?, boltcard=?, parcelex=? WHERE id=?";

        if ($stmt = $conn->prepare($sql)) {

            // 42 strings (s) + 9 inteiros (i) = 51 parâmetros
            $types = str_repeat('s', 42) . str_repeat('i', 9);
            $stmt->bind_param($types, 
                $tipo_pessoa, $cnpj, $razao_social, $nome_fantasia, $insc_est,
                $adm_cpf, $adm_rg, $adm_nome, $adm_nacionalidade, $adm_est_civil, $adm_data_nasc,
                $adm_end, $adm_numero, $adm_complemento, $adm_bairro, $adm_cidade, $adm_uf, $adm_cep,
                $ramo_atividade, $telefone1, $telefone2, $email, $logradouro, $numero, $complemento, $bairro,
                $cidade, $uf, $cep, $ger_nome, $ger_telefone1, $ger_email, $fin_nome, $fin_telefone1, $fin_email,
                $valor, $valor_extenso, $banco, $agencia, $conta, $pix, $favorecido, $brasil_card,
                $fgts, $pagseguro, $soufacil, $fliper, $parcela_facil, $boltcard, $parcelex, $id);

            if ($stmt->execute()) {
                // Atualizar ou inserir dados nos produtos correspondentes
                if ($brasil_card) {
                    if (!checkProduct($conn, 'brasil_card', $id)) {
                        $conn->query("INSERT INTO brasil_card (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM brasil_card WHERE id_cliente = $id");
                }
                if ($fgts) {
                    if (!checkProduct($conn, 'fgts', $id)) {
                        $conn->query("INSERT INTO fgts (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM fgts WHERE id_cliente = $id");
                }
                if ($pagseguro) {
                    if (!checkProduct($conn, 'pagseguro', $id)) {
                        $conn->query("INSERT INTO pagseguro (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM pagseguro WHERE id_cliente = $id");
                }

                if ($soufacil) {
                    if (!checkProduct($conn, 'soufacil', $id)) {
                        $conn->query("INSERT INTO soufacil (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM soufacil WHERE id_cliente = $id");
                }

                if ($fliper) {
                    if (!checkProduct($conn, 'fliper', $id)) {
                        $conn->query("INSERT INTO fliper (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM fliper WHERE id_cliente = $id");
                }

                if ($parcela_facil) {
                    if (!checkProduct($conn, 'parcela_facil', $id)) {
                        $conn->query("INSERT INTO parcela_facil (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM parcela_facil WHERE id_cliente = $id");
                }

                if ($boltcard) {
                    if (!checkProduct($conn, 'boltcard', $id)) {
                        $conn->query("INSERT INTO boltcard (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM boltcard WHERE id_cliente = $id");
                }

                if ($parcelex) {
                    if (!checkProduct($conn, 'parcelex', $id)) {
                        $conn->query("INSERT INTO parcelex (id_cliente) VALUES ($id)");
                    }
                } else {
                    $conn->query("DELETE FROM parcelex WHERE id_cliente = $id");
                }

                // Atualizar representantes
                $conn->query("DELETE FROM cliente_representante WHERE id_cliente = $id");
                foreach ($representantes as $id_representante) {
                    $conn->query("INSERT INTO cliente_representante (id_cliente, id_representante) VALUES ($id, $id_representante)");
                }

                echo "<script>alert('Registro atualizado com sucesso.'); window.location.href='detalhes.php?id=$id';</script>";
            } else {
                echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
            }

            $stmt->close();
        } else {
            die("Erro na preparação: " . $conn->error);
        }
        // Conexão será fechada automaticamente pelo shutdown handler
} else {
    echo "Método de requisição inválido.";
}
?>
