<?php
// Limpar qualquer output buffer anterior
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Aumentar tempo de execução para arquivos grandes
set_time_limit(300); // 5 minutos
ini_set('max_execution_time', 300);

require_once '../config/config.php';
require_once '../login/session.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: brasil_card.php");
    exit;
}

// Verificar se o arquivo foi enviado
if (!isset($_FILES['arquivo_csv']) || $_FILES['arquivo_csv']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('Erro: Nenhum arquivo foi enviado ou houve erro no upload.'); window.location.href='brasil_card.php';</script>";
    exit;
}

// Verificar se competência foi informada
if (!isset($_POST['competencia_mes']) || !isset($_POST['competencia_ano']) || 
    empty($_POST['competencia_mes']) || empty($_POST['competencia_ano'])) {
    echo "<script>alert('Erro: Por favor, informe a competência (mês e ano).'); window.location.href='brasil_card.php';</script>";
    exit;
}

$competencia_mes = (int)$_POST['competencia_mes'];
$competencia_ano = (int)$_POST['competencia_ano'];

// Validar competência
if ($competencia_mes < 1 || $competencia_mes > 12) {
    echo "<script>alert('Erro: Mês inválido.'); window.location.href='brasil_card.php';</script>";
    exit;
}

if ($competencia_ano < 2020 || $competencia_ano > date('Y') + 1) {
    echo "<script>alert('Erro: Ano inválido.'); window.location.href='brasil_card.php';</script>";
    exit;
}

$arquivo = $_FILES['arquivo_csv'];
$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

// Validar extensão
if ($extensao !== 'csv') {
    echo "<script>alert('Erro: Tipo de arquivo não permitido. Use arquivos .csv'); window.location.href='brasil_card.php';</script>";
    exit;
}

// Validar tamanho (máximo 10MB)
$max_size = 10 * 1024 * 1024; // 10MB
if ($arquivo['size'] > $max_size) {
    echo "<script>alert('Erro: Arquivo muito grande. Tamanho máximo permitido: 10MB.'); window.location.href='brasil_card.php';</script>";
    exit;
}

// Diretório temporário para upload
$upload_dir = __DIR__ . "/../uploads/temp/";
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo "<script>alert('Erro: Não foi possível criar diretório de upload.'); window.location.href='brasil_card.php';</script>";
        exit;
    }
}

// Mover arquivo para diretório temporário
$nome_arquivo = uniqid() . "_" . time() . ".csv";
$caminho_completo = $upload_dir . $nome_arquivo;

if (!move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
    echo "<script>alert('Erro: Não foi possível fazer upload do arquivo.'); window.location.href='brasil_card.php';</script>";
    exit;
}

try {
    $linhas_processadas = 0;
    $linhas_atualizadas = 0;
    $linhas_inseridas = 0;
    $linhas_erro = 0;
    $erros = [];

    // Iniciar transação para melhorar performance em arquivos grandes
    $conn->autocommit(false);
    $conn->begin_transaction();

    // Abrir arquivo CSV
    $handle = fopen($caminho_completo, 'r');
    if ($handle === false) {
        throw new Exception("Não foi possível abrir o arquivo CSV.");
    }

    // Ler BOM se existir (UTF-8)
    $bom = fread($handle, 3);
    if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
        rewind($handle);
    }

    $linha_numero = 0;
    $linhas_processadas_no_loop = 0;
    
    while (($dados = fgetcsv($handle, 1000, ';')) !== false) {
        $linha_numero++;
        
        // Verificar timeout a cada 100 linhas (sem output para não interferir)
        // O flush será feito apenas no final
        
        // Pular cabeçalho
        if ($linha_numero === 1) {
            continue;
        }

        // Ignorar linhas vazias
        if (empty(array_filter($dados))) {
            continue;
        }

        // Formato esperado: DATA;PDV;FANTASIA;MODALIDADE;POPULAR;CDC;APROVADAS;NEGADAS;RESTRICOES;PENDENTE;CANCELADAS;PR CADASTRO;TOTAL
        if (count($dados) < 13) {
            $linhas_erro++;
            $erros[] = "Linha $linha_numero: Formato inválido (colunas insuficientes)";
            continue;
        }

        // Extrair dados
        $data_str = isset($dados[0]) ? trim($dados[0]) : '';
        $pdv = isset($dados[1]) ? trim($dados[1]) : '';
        $fantasia = isset($dados[2]) ? trim($dados[2]) : '';
        $modalidade = isset($dados[3]) ? trim($dados[3]) : '';
        $popular = isset($dados[4]) ? (int)trim($dados[4]) : 0;
        $cdc_str = isset($dados[5]) ? trim($dados[5]) : '0';
        $aprovadas = isset($dados[6]) ? (int)trim($dados[6]) : 0;
        $negadas = isset($dados[7]) ? (int)trim($dados[7]) : 0;
        $restricoes = isset($dados[8]) ? (int)trim($dados[8]) : 0;
        $pendente = isset($dados[9]) ? (int)trim($dados[9]) : 0;
        $canceladas = isset($dados[10]) ? (int)trim($dados[10]) : 0;
        $pr_cadastro = isset($dados[11]) ? (int)trim($dados[11]) : 0;
        $total = isset($dados[12]) ? (int)trim($dados[12]) : 0;

        // Validar campos obrigatórios
        if (empty($data_str) || empty($pdv)) {
            $linhas_erro++;
            $erros[] = "Linha $linha_numero: DATA ou PDV vazio";
            continue;
        }

        // Validar data (opcional - apenas para validação)
        $data_obj = DateTime::createFromFormat('d/m/Y', $data_str);
        if (!$data_obj) {
            $linhas_erro++;
            $erros[] = "Linha $linha_numero: Data inválida ($data_str). Use formato dd/mm/yyyy";
            continue;
        }

        // Usar competência informada pelo usuário (não extrair da data do CSV)
        $mes = $competencia_mes;
        $ano = $competencia_ano;

        // Converter CDC de R$ X.XXX,XX para número
        $cdc = 0;
        if (!empty($cdc_str) && $cdc_str !== 'R$ 0,00') {
            $cdc_str = str_replace(['R$', ' ', '.'], '', $cdc_str);
            $cdc_str = str_replace(',', '.', $cdc_str);
            $cdc = (float)$cdc_str;
        }

        // Normalizar PDV (remover zeros à esquerda para busca)
        $pdv_sem_zeros = ltrim($pdv, '0');
        if (empty($pdv_sem_zeros)) {
            $pdv_sem_zeros = '0';
        }

        // Buscar cliente pelo PDV
        $stmt = $conn->prepare("SELECT id_cliente FROM brasil_card WHERE pdv = ? OR TRIM(LEADING '0' FROM pdv) = ? LIMIT 1");
        $stmt->bind_param("ss", $pdv, $pdv_sem_zeros);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $result->close();
        $stmt->close();

        if (!$cliente) {
            $linhas_erro++;
            $erros[] = "Linha $linha_numero: PDV não encontrado ($pdv)";
            continue;
        }

        $id_cliente = $cliente['id_cliente'];

        // Verificar se já existe registro para o mesmo cliente, mês e ano
        $check_stmt = $conn->prepare("SELECT id FROM fat_brasil_card WHERE id_cli = ? AND mes = ? AND ano = ? LIMIT 1");
        $check_stmt->bind_param("iii", $id_cliente, $mes, $ano);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing = $check_result->fetch_assoc();
        $check_result->close();
        $check_stmt->close();

        if ($existing) {
            // Atualizar registro existente
            $update_stmt = $conn->prepare("UPDATE fat_brasil_card SET 
                modalidade = ?, 
                popular = ?, 
                cdc = ?, 
                aprovadas = ?, 
                negadas = ?, 
                restricoes = ?, 
                pendente = ?, 
                cancelado = ?, 
                total = ?, 
                date_update = NOW() 
                WHERE id = ?");
            
            $update_stmt->bind_param("sddiiiiiii", 
                $modalidade, 
                $popular, 
                $cdc, 
                $aprovadas, 
                $negadas, 
                $restricoes, 
                $pendente, 
                $canceladas, 
                $total, 
                $existing['id']
            );
            
            if ($update_stmt->execute()) {
                $linhas_atualizadas++;
                $linhas_processadas++;
            } else {
                $linhas_erro++;
                $erros[] = "Linha $linha_numero: Erro ao atualizar - " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // Inserir novo registro
            $insert_stmt = $conn->prepare("INSERT INTO fat_brasil_card (
                id_cli, modalidade, popular, cdc, aprovadas, negadas, restricoes, pendente, cancelado, total, mes, ano, date_update
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            // Tipos: i (id_cliente), s (modalidade), i (popular), d (cdc), i (aprovadas), i (negadas), i (restricoes), i (pendente), i (canceladas), i (total), i (mes), i (ano) = 12 parâmetros
            $insert_stmt->bind_param("isidiiiiiiii", 
                $id_cliente,      // i
                $modalidade,      // s
                $popular,         // i (int)
                $cdc,             // d (double)
                $aprovadas,       // i
                $negadas,         // i
                $restricoes,      // i
                $pendente,        // i
                $canceladas,      // i
                $total,           // i
                $mes,             // i
                $ano              // i
            );
            
            if ($insert_stmt->execute()) {
                $linhas_inseridas++;
                $linhas_processadas++;
            } else {
                $linhas_erro++;
                $erros[] = "Linha $linha_numero: Erro ao inserir - " . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
    }
    
    fclose($handle);

    // Commit da transação se tudo correu bem
    try {
        if ($linhas_erro == 0 || $linhas_processadas > 0) {
            if (!$conn->commit()) {
                throw new Exception("Erro ao fazer commit: " . $conn->error);
            }
        } else {
            $conn->rollback();
        }
    } catch (Exception $commit_error) {
        $conn->rollback();
        throw new Exception("Erro na transação: " . $commit_error->getMessage());
    } finally {
        $conn->autocommit(true);
    }

    // NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script

    // Remover arquivo temporário
    @unlink($caminho_completo);

    // Mensagem de sucesso
    $mensagem = "Importação concluída!\n\n";
    $mensagem .= "Linhas processadas: $linhas_processadas\n";
    $mensagem .= "• Inseridas: $linhas_inseridas\n";
    $mensagem .= "• Atualizadas: $linhas_atualizadas\n";
    
    if ($linhas_erro > 0) {
        $mensagem .= "\nLinhas com erro: $linhas_erro\n";
        if (count($erros) > 0) {
            $mensagem .= "\nPrimeiros erros:\n" . implode("\n", array_slice($erros, 0, 10));
            if (count($erros) > 10) {
                $mensagem .= "\n... e mais " . (count($erros) - 10) . " erros";
            }
        }
    }

    // Limpar qualquer buffer existente
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Garantir que sempre há output - verificar se headers já foram enviados
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }
    
    // Escapar mensagem para JavaScript de forma segura
    $mensagem_js = json_encode($mensagem, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    
    // Output direto sem problemas de encoding
    echo '<!DOCTYPE html>';
    echo '<html><head><meta charset="UTF-8"><title>Importação CSV</title></head><body>';
    echo '<script>';
    echo 'var msg = ' . $mensagem_js . ';';
    echo 'if (msg && msg.trim() !== "") { alert(msg); }';
    echo 'window.location.href = "brasil_card.php";';
    echo '</script>';
    echo '<noscript><p>Importação concluída. <a href="brasil_card.php">Clique aqui para voltar</a></p></noscript>';
    echo '</body></html>';
    
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    exit;

} catch (Exception $e) {
    // Limpar qualquer buffer existente
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Rollback em caso de erro
    if (isset($conn) && $conn) {
        try {
            $conn->rollback();
            $conn->autocommit(true);
        } catch (Exception $rollback_error) {
            // Ignora erro no rollback
        }
    }
    
    // NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script
    
    // Remover arquivo temporário em caso de erro
    if (isset($caminho_completo)) {
        @unlink($caminho_completo);
    }
    
    // Garantir output mesmo em caso de erro
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Erro na Importação</title>
</head>
<body>
    <script>
        alert('Erro: <?php echo addslashes($e->getMessage()); ?>');
        window.location.href = 'brasil_card.php';
    </script>
    <noscript>
        <p>Erro: <?php echo htmlspecialchars($e->getMessage()); ?>. <a href='brasil_card.php'>Clique aqui para voltar</a></p>
    </noscript>
</body>
</html>
<?php
    exit;
} catch (Error $e) {
    // Limpar qualquer buffer existente
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Rollback em caso de erro fatal
    if (isset($conn) && $conn) {
        try {
            $conn->rollback();
            $conn->autocommit(true);
        } catch (Exception $rollback_error) {
            // Ignora erro no rollback
        }
    }
    
    // Remover arquivo temporário em caso de erro
    if (isset($caminho_completo)) {
        @unlink($caminho_completo);
    }
    
    // Garantir output mesmo em caso de erro fatal
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Erro Fatal</title>
</head>
<body>
    <script>
        alert('Erro fatal: <?php echo addslashes($e->getMessage()); ?>\nLinha: <?php echo $e->getLine(); ?>');
        window.location.href = 'brasil_card.php';
    </script>
    <noscript>
        <p>Erro fatal: <?php echo htmlspecialchars($e->getMessage()); ?> (Linha: <?php echo $e->getLine(); ?>). <a href='brasil_card.php'>Clique aqui para voltar</a></p>
    </noscript>
</body>
</html>
<?php
    exit;
}
?>

