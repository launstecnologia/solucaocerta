<?php
require_once '../config/config.php';
require_once '../login/session.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Verificar se o arquivo foi enviado
if (!isset($_FILES['arquivo_excel']) || $_FILES['arquivo_excel']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('Erro: Nenhum arquivo foi enviado ou houve erro no upload.'); window.location.href='index.php';</script>";
    exit;
}

$arquivo = $_FILES['arquivo_excel'];
$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

// Validar extensão
$extensoes_permitidas = ['xlsx', 'xls', 'csv'];
if (!in_array($extensao, $extensoes_permitidas)) {
    echo "<script>alert('Erro: Tipo de arquivo não permitido. Use arquivos .xlsx, .xls ou .csv'); window.location.href='index.php';</script>";
    exit;
}

// Validar tamanho (máximo 10MB)
$max_size = 10 * 1024 * 1024; // 10MB
if ($arquivo['size'] > $max_size) {
    echo "<script>alert('Erro: Arquivo muito grande. Tamanho máximo permitido: 10MB.'); window.location.href='index.php';</script>";
    exit;
}

// Diretório temporário para upload
$upload_dir = __DIR__ . "/../uploads/temp/";
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo "<script>alert('Erro: Não foi possível criar diretório de upload.'); window.location.href='index.php';</script>";
        exit;
    }
}

// Mover arquivo para diretório temporário
$nome_arquivo = uniqid() . "_" . time() . "." . $extensao;
$caminho_completo = $upload_dir . $nome_arquivo;

if (!move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
    echo "<script>alert('Erro: Não foi possível fazer upload do arquivo.'); window.location.href='index.php';</script>";
    exit;
}

try {
    $linhas_processadas = 0;
    $linhas_erro = 0;
    $erros = [];

    // Processar arquivo CSV
    if ($extensao === 'csv') {
        $handle = fopen($caminho_completo, 'r');
        if ($handle === false) {
            throw new Exception("Não foi possível abrir o arquivo CSV.");
        }

        // Detectar encoding e BOM
        $primeiros_bytes = fread($handle, 3);
        if ($primeiros_bytes === chr(0xEF).chr(0xBB).chr(0xBF)) {
            // UTF-8 com BOM - já está na posição correta
        } else {
            // Sem BOM, voltar ao início
            rewind($handle);
        }

        $linha_numero = 0;
        $cabecalho = null;
        
        while (($dados = fgetcsv($handle, 1000, ';')) !== false) {
            $linha_numero++;
            
            // Capturar cabeçalho na primeira linha
            if ($linha_numero === 1) {
                $cabecalho = $dados;
                continue;
            }

            // Ignorar linhas vazias
            if (empty(array_filter($dados))) {
                continue;
            }

            // Processar linha (ajustar índices conforme formato do Excel exportado)
            // Formato esperado: CNPJ/CPF, Nome Fantasia, Estado, Cidade, Representante, Data Cadastro, Data PDV
            if (count($dados) >= 2) {
                $cnpj_cpf = isset($dados[0]) ? trim($dados[0]) : '';
                $nome_fantasia = isset($dados[1]) ? trim($dados[1]) : '';
                
                // Limpar CNPJ/CPF (remover caracteres especiais)
                $cnpj_cpf = preg_replace('/[^0-9]/', '', $cnpj_cpf);
                
                if (empty($cnpj_cpf) || empty($nome_fantasia)) {
                    $linhas_erro++;
                    $erros[] = "Linha $linha_numero: CNPJ/CPF ou Nome Fantasia vazio";
                    continue;
                }

                // Buscar cliente por CNPJ ou CPF
                $stmt = $conn->prepare("SELECT id, nome_fantasia FROM cliente WHERE cnpj = ? OR adm_cpf = ? LIMIT 1");
                $stmt->bind_param("ss", $cnpj_cpf, $cnpj_cpf);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $cliente = $result->fetch_assoc();
                    $linhas_processadas++;
                    // Aqui você pode adicionar lógica para atualizar dados do cliente se necessário
                } else {
                    $linhas_erro++;
                    $erros[] = "Linha $linha_numero: Cliente não encontrado (CNPJ/CPF: $cnpj_cpf, Nome: $nome_fantasia)";
                }
                $result->close();
                $stmt->close();
            } else {
                $linhas_erro++;
                $erros[] = "Linha $linha_numero: Formato inválido (colunas insuficientes)";
            }
        }
        fclose($handle);
    } else {
        // Para arquivos Excel (.xlsx, .xls), seria necessário usar uma biblioteca como PhpSpreadsheet
        // Por enquanto, vamos apenas informar que precisa ser CSV
        throw new Exception("Arquivos Excel (.xlsx, .xls) ainda não são suportados. Por favor, converta para CSV antes de fazer upload.");
    }

    // NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script

    // Remover arquivo temporário
    @unlink($caminho_completo);

    // Mensagem de sucesso
    $mensagem = "Upload processado com sucesso!\n";
    $mensagem .= "Linhas processadas: $linhas_processadas\n";
    if ($linhas_erro > 0) {
        $mensagem .= "Linhas com erro: $linhas_erro\n";
        if (count($erros) > 0) {
            $mensagem .= "\nErros:\n" . implode("\n", array_slice($erros, 0, 10));
            if (count($erros) > 10) {
                $mensagem .= "\n... e mais " . (count($erros) - 10) . " erros";
            }
        }
    }

    echo "<script>alert('" . addslashes($mensagem) . "'); window.location.href='index.php';</script>";

} catch (Exception $e) {
    // NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script
    
    // Remover arquivo temporário em caso de erro
    @unlink($caminho_completo);
    
    echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}
?>

