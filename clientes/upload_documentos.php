<?php
require_once '../config/config.php';

// Log de debug
error_log("=== UPLOAD DE DOCUMENTO INICIADO ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar se os dados necessários estão presentes
        if (!isset($_POST['id_cliente']) || empty($_POST['id_cliente'])) {
            throw new Exception("ID do cliente não informado");
        }
        if (!isset($_POST['tipo_documento']) || empty($_POST['tipo_documento'])) {
            throw new Exception("Tipo de documento não informado");
        }
        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Arquivo não foi enviado ou houve erro no upload");
        }
        
        $id_cliente = (int)$_POST['id_cliente'];
        $tipo_documento = trim($_POST['tipo_documento']);
        $produtos = isset($_POST['produtos']) && is_array($_POST['produtos']) ? $_POST['produtos'] : [];
        $arquivo = $_FILES['arquivo'];
        
        error_log("ID Cliente: $id_cliente");
        error_log("Tipo Documento: $tipo_documento");
        error_log("Produtos selecionados: " . json_encode($produtos));
        
        // Validar tamanho do arquivo (máximo 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($arquivo['size'] > $max_size) {
            throw new Exception("Arquivo muito grande. Tamanho máximo permitido: 10MB.");
        }
        
        // Validar extensão do arquivo
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        if (!in_array($extensao, $extensoes_permitidas)) {
            throw new Exception("Tipo de arquivo não permitido. Extensões permitidas: " . implode(', ', $extensoes_permitidas));
        }
        
        // Diretório de upload
        $upload_dir = __DIR__ . "/../uploads/documentos/";
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Erro ao criar diretório de upload.");
            }
        }
        
        // Verificar permissões de escrita
        if (!is_writable($upload_dir)) {
            throw new Exception("Diretório de upload sem permissão de escrita.");
        }
        
        // Verificar se o documento já existe
        $stmt = $conn->prepare("SELECT id, caminho_arquivo FROM documentos_cliente WHERE id_cliente = ? AND tipo_documento = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $conn->error);
        }
        $stmt->bind_param("is", $id_cliente, $tipo_documento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Gerar nome único para o arquivo
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $novo_nome = uniqid() . "_" . time() . ".$ext";
        $caminho_completo = $upload_dir . $novo_nome;
        $caminho_arquivo = "/uploads/documentos/" . $novo_nome; // Caminho absoluto para salvar no banco
        
        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            throw new Exception("Erro ao fazer upload do arquivo. Verifique as permissões do diretório.");
        }
        
        error_log("Arquivo movido para: $caminho_completo");
        
        if ($result->num_rows > 0) {
            // Documento já existe - atualizar
            $documento = $result->fetch_assoc();
            $id_documento = $documento['id'];
            
            // Deletar arquivo antigo se existir - buscar caminho completo do antigo
            if (isset($documento['caminho_arquivo'])) {
                // Converter caminho do banco para caminho físico
                $caminho_banco = $documento['caminho_arquivo'];
                if (strpos($caminho_banco, '/uploads/') === 0) {
                    // Remover a barra inicial e adicionar __DIR__
                    $caminho_antigo_completo = __DIR__ . "/.." . $caminho_banco;
                } elseif (strpos($caminho_banco, '../uploads/') === 0) {
                    $caminho_antigo_completo = __DIR__ . "/" . $caminho_banco;
                } else {
                    $caminho_antigo_completo = __DIR__ . "/../uploads/documentos/" . basename($caminho_banco);
                }
                
                if (file_exists($caminho_antigo_completo)) {
                    @unlink($caminho_antigo_completo);
                }
            }
            
            // Atualizar no banco
            $stmt = $conn->prepare("UPDATE documentos_cliente SET nome_arquivo = ?, caminho_arquivo = ? WHERE id = ?");
            if (!$stmt || !$stmt->bind_param("ssi", $arquivo['name'], $caminho_arquivo, $id_documento) || !$stmt->execute()) {
                @unlink($caminho_completo); // Remover arquivo em caso de erro
                throw new Exception("Erro ao atualizar documento no banco: " . $conn->error);
            }
            
            // Limpar associações antigas
            $stmt = $conn->prepare("DELETE FROM documentos_produto WHERE id_documento = ?");
            $stmt->bind_param("i", $id_documento);
            $stmt->execute();
            
            error_log("Documento atualizado com ID: $id_documento");
        } else {
            // Inserir novo documento
            $stmt = $conn->prepare("INSERT INTO documentos_cliente (id_cliente, tipo_documento, nome_arquivo, caminho_arquivo) VALUES (?, ?, ?, ?)");
            if (!$stmt || !$stmt->bind_param("isss", $id_cliente, $tipo_documento, $arquivo['name'], $caminho_arquivo) || !$stmt->execute()) {
                @unlink($caminho_completo); // Remover arquivo em caso de erro
                throw new Exception("Erro ao salvar documento no banco: " . $conn->error);
            }
            $id_documento = $stmt->insert_id;
            error_log("Novo documento salvo com ID: $id_documento");
        }
        
        // Vincular documento ao(s) produto(s)
        if (!empty($produtos)) {
            foreach ($produtos as $produto) {
                $stmt_prod = $conn->prepare("INSERT INTO documentos_produto (id_documento, produto) VALUES (?, ?) ON DUPLICATE KEY UPDATE produto = VALUES(produto)");
                $stmt_prod->bind_param("is", $id_documento, $produto);
                $stmt_prod->execute();
                $stmt_prod->close();
            }
            error_log("Documento vinculado aos produtos");
        }
        
        error_log("Upload concluído com sucesso!");
        echo "<script>alert('Documento enviado com sucesso!'); window.location.href='detalhes.php?id=$id_cliente';</script>";
        
    } catch (Exception $e) {
        error_log("ERRO: " . $e->getMessage());
        echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); history.back();</script>";
    }
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
