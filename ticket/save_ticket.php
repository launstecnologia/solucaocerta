<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
    $id_usuario = $_SESSION['id'];
    $id_status = isset($_POST['id_status']) ? (int)$_POST['id_status'] : 1;
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $data_retorno = !empty($_POST['data_retorno']) ? date('Y-m-d H:i:s', strtotime($_POST['data_retorno'])) : null;
    $data_criacao = date('Y-m-d H:i:s');
    $data_atualizacao = date('Y-m-d H:i:s');

    if ($id_cliente <= 0) {
        echo "<script>alert('Selecione um cliente.'); history.back();</script>";
        exit;
    }

    // Inserir ticket
    $sql = "INSERT INTO tickets (id_cliente, id_usuario, id_status, titulo, descricao, data_retorno, data_criacao, data_atualizacao, notificado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiisssss", $id_cliente, $id_usuario, $id_status, $titulo, $descricao, $data_retorno, $data_criacao, $data_atualizacao);
        if ($stmt->execute()) {
            $id_ticket = $stmt->insert_id;
            $stmt->close();

            // Processar anexos
            if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                $diretorio_anexos = '../uploads/tickets/' . $id_ticket . '/';
                if (!file_exists($diretorio_anexos)) {
                    mkdir($diretorio_anexos, 0755, true);
                }

                $arquivos = $_FILES['anexos'];
                $total_arquivos = count($arquivos['name']);

                for ($i = 0; $i < $total_arquivos; $i++) {
                    if ($arquivos['error'][$i] === UPLOAD_ERR_OK) {
                        $nome_arquivo = $arquivos['name'][$i];
                        $tamanho = $arquivos['size'][$i];
                        $tipo_mime = $arquivos['type'][$i];
                        $tmp_name = $arquivos['tmp_name'][$i];

                        // Validar tamanho (máx 10MB)
                        if ($tamanho > 10 * 1024 * 1024) {
                            continue; // Pula arquivos muito grandes
                        }

                        // Sanitizar nome do arquivo
                        $nome_arquivo_safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nome_arquivo);
                        $caminho_arquivo = $diretorio_anexos . time() . '_' . $nome_arquivo_safe;

                        if (move_uploaded_file($tmp_name, $caminho_arquivo)) {
                            // Salvar no banco
                            $sql_anexo = "INSERT INTO ticket_anexos (id_ticket, nome_arquivo, caminho_arquivo, tamanho, tipo_mime) 
                                         VALUES (?, ?, ?, ?, ?)";
                            $stmt_anexo = $conn->prepare($sql_anexo);
                            $caminho_relativo = 'uploads/tickets/' . $id_ticket . '/' . basename($caminho_arquivo);
                            $stmt_anexo->bind_param("issis", $id_ticket, $nome_arquivo, $caminho_relativo, $tamanho, $tipo_mime);
                            $stmt_anexo->execute();
                            $stmt_anexo->close();
                        }
                    }
                }
            }

            // Criar notificação se houver data de retorno
            if ($data_retorno) {
                // Criar notificação para o usuário que criou o ticket
                $sql_notif = "INSERT INTO ticket_notificacoes (id_ticket, id_usuario, tipo, mensagem) 
                              VALUES (?, ?, 'retorno', ?)";
                $mensagem = "Ticket #{$id_ticket} tem retorno agendado para " . date('d/m/Y H:i', strtotime($data_retorno));
                $stmt_notif = $conn->prepare($sql_notif);
                $stmt_notif->bind_param("iis", $id_ticket, $id_usuario, $mensagem);
                $stmt_notif->execute();
                $stmt_notif->close();
            }

            echo "<script>alert('Ticket criado com sucesso.'); window.location.href='" . ticket_url('index.php') . "';</script>";
        } else {
            echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
        }
    } else {
        die("Erro na preparação: " . $conn->error);
    }
}
?>
