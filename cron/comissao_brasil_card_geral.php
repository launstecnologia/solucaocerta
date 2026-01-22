<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Habilitar output buffering para exibir em tempo real
ob_start();

// Autoload das dependências
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Google\Client;
use Google\Service\Sheets;
use GuzzleHttp\Client as GuzzleClient;

// Inicializa o cliente do Google Sheets
$client = new Client();

// Configurar HTTP client com SSL desabilitado para resolver problemas de certificado
$httpClient = new GuzzleClient([
    'verify' => false, // Desabilita verificação SSL
    'timeout' => 30
]);

$client->setHttpClient($httpClient);
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes(Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('solucaocerta-d66249bd42d5.json');
$client->setAccessType('offline');
$service = new Sheets($client);

// ID da planilha
$spreadsheetId = '1J9Lr5k7i52w1frr8OY-0I2cFS8SzXIs-Xd9P2TiijQc';

// Definir o intervalo de datas: últimos dois anos a partir do mês atual
$dataAtual = new DateTime();
$mesAtual = (int)$dataAtual->format('m');
$anoAtual = (int)$dataAtual->format('Y');

// Calcular data inicial: primeiro dia do mesmo mês, dois anos atrás
// Exemplo: Se estamos em Janeiro/2026, processa de Janeiro/2024 até Janeiro/2026
$startPeriod = new DateTime();
$startPeriod->setDate($anoAtual - 2, $mesAtual, 1);
$startPeriod->setTime(0, 0, 0);

// Data final: último dia do mês atual
$endPeriod = new DateTime();
$endPeriod->setDate($anoAtual, $mesAtual, $dataAtual->format('t')); // 't' retorna o último dia do mês
$endPeriod->setTime(23, 59, 59);

echo "<h3>Processamento de Dados - Brasil Card (Comissão Agregada)</h3>";
echo "<p><strong>Período:</strong> " . $startPeriod->format('01/m/Y') . " até " . $endPeriod->format('d/m/Y') . "</p>";
echo "<p><strong>Mês atual do banco:</strong> " . str_pad($mesAtual, 2, '0', STR_PAD_LEFT) . "/" . $anoAtual . "</p><br>";

// Lê os dados do Google Sheets (colunas A até AJ)
$range = 'A11:AJ1000';
try {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $rows = $response->getValues();

    if (empty($rows)) {
        die("Nenhum dado encontrado no Google Sheets.");
    }
} catch (Exception $e) {
    die("Erro ao acessar o Google Sheets: " . $e->getMessage());
}

// Conexão com o banco de dados usando config.php
$conn = getConnection();

echo "<p><strong>Total de linhas lidas da planilha:</strong> " . count($rows) . "</p>";
flush();
ob_flush();

// Array para agregar dados por mês/ano
// Estrutura: ['mes/ano' => ['popular' => soma, 'cdc' => soma]]
$dadosAgregados = [];

// Contadores
$totalProcessados = 0;
$linhasPuladas = 0;
$linhasComData = 0;
$linhasForaPeriodo = 0;
$linhasDataInvalida = 0;

// Processar as linhas do Google Sheets
foreach ($rows as $index => $row) {
    // Pular primeira linha (cabeçalho)
    if ($index === 0) {
        $linhasPuladas++;
        continue;
    }
    
    // Ignorar linhas vazias ou sem dados essenciais
    if (empty($row) || !isset($row[17]) || empty($row[17])) {
        $linhasPuladas++;
        continue; // Pula se não tiver data (coluna R, índice 17)
    }

    // Extrair dados da linha
    // Coluna R (índice 17): Data
    $dataStr = trim($row[17]);
    
    // Pular se for cabeçalho ou vazio
    if (empty($dataStr) || strtoupper($dataStr) === 'DATA') {
        $linhasPuladas++;
        continue;
    }
    
    $linhasComData++;

    // Tentar diferentes formatos de data
    $rowDate = null;
    $formatos = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
    foreach ($formatos as $formato) {
        $rowDate = DateTime::createFromFormat($formato, $dataStr);
        if ($rowDate !== false) {
            break;
        }
    }

    // Se não conseguiu parsear, tenta como timestamp do Excel
    if (!$rowDate && is_numeric($dataStr)) {
        $rowDate = DateTime::createFromFormat('U', ($dataStr - 25569) * 86400);
    }

    if (!$rowDate) {
        $linhasDataInvalida++;
        continue; // Pula se não conseguir parsear a data
    }

    // Verifica se a data está no intervalo desejado
    if ($rowDate < $startPeriod || $rowDate > $endPeriod) {
        $linhasForaPeriodo++;
        continue; // Fora do período, pula
    }

    // Extrair mês e ano da data
    $mes = (int)$rowDate->format('m');
    $ano = (int)$rowDate->format('Y');
    $chaveMesAno = str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . $ano;

    // Coluna AB (índice 27): Popular - quantidade de cartões
    $popular = isset($row[27]) ? (int)$row[27] : 0;
    
    // Coluna AC (índice 28): IPCV - faturamento CDC
    $ipcvRaw = isset($row[28]) ? trim($row[28]) : '0';
    $ipcv = (float)str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $ipcvRaw);

    // Inicializar array para este mês/ano se não existir
    if (!isset($dadosAgregados[$chaveMesAno])) {
        $dadosAgregados[$chaveMesAno] = [
            'mes' => $mes,
            'ano' => $ano,
            'popular' => 0,
            'ipcv' => 0.0
        ];
    }

    // Somar TODOS os valores, independente da modalidade
    $dadosAgregados[$chaveMesAno]['popular'] += $popular;
    $dadosAgregados[$chaveMesAno]['ipcv'] += $ipcv;

    $totalProcessados++;
}

echo "<p><strong>Linhas puladas (cabeçalho/vazias):</strong> $linhasPuladas</p>";
echo "<p><strong>Linhas com data encontrada:</strong> $linhasComData</p>";
echo "<p><strong>Linhas com data inválida:</strong> $linhasDataInvalida</p>";
echo "<p><strong>Linhas fora do período:</strong> $linhasForaPeriodo</p>";
echo "<p><strong>Total de linhas processadas:</strong> $totalProcessados</p>";
echo "<p><strong>Meses/Anos encontrados:</strong> " . count($dadosAgregados) . "</p><br>";

// Processar e salvar dados agregados por mês/ano
$totalSalvos = 0;
$totalAtualizados = 0;
$erros = [];

foreach ($dadosAgregados as $chaveMesAno => $dados) {
    $mes = $dados['mes'];
    $ano = $dados['ano'];
    $popular = $dados['popular'];
    $ipcv = $dados['ipcv']; // Faturamento CDC (IPCV)
    
    // Calcular comissões
    $comissaoPopular = $popular * 15; // R$ 15,00 por cartão popular
    $comissaoCdc = $ipcv * 0.02; // 2% do faturamento CDC (IPCV)
    
    // Formatar valores para string (a tabela usa VARCHAR)
    $mesStr = str_pad($mes, 2, '0', STR_PAD_LEFT);
    $anoStr = (string)$ano;
    $popularStr = (string)$popular;
    $comissaoPopularStr = number_format($comissaoPopular, 2, '.', '');
    $ipcvStr = number_format($ipcv, 2, '.', ''); // Faturamento CDC (IPCV)
    $comissaoCdcStr = number_format($comissaoCdc, 2, '.', '');
    $dataAtualStr = date('Y-m-d H:i:s');
    
    // Verificar se já existe registro para este mês/ano
    $checkStmt = $conn->prepare("SELECT id FROM comissao_brasil_card WHERE mes_competencia = ? AND ano_competencia = ?");
    $checkStmt->bind_param("ss", $mesStr, $anoStr);
    $checkStmt->execute();
    $resultCheck = $checkStmt->get_result();
    $existingId = null;
    
    if ($resultCheck && $resultCheck->num_rows > 0) {
        $rowExisting = $resultCheck->fetch_assoc();
        $existingId = $rowExisting['id'];
    }
    $checkStmt->close();

    if ($existingId) {
        // Atualizar registro existente
        $updateStmt = $conn->prepare("
            UPDATE comissao_brasil_card SET
                qtd_popular = ?,
                comissao_popular = ?,
                fat_cdc = ?,
                comissao_cdc = ?,
                data = ?
            WHERE id = ?
        ");
        
        $updateStmt->bind_param("sssssi", 
            $popularStr,
            $comissaoPopularStr,
            $ipcvStr,
            $comissaoCdcStr,
            $dataAtualStr,
            $existingId
        );
        
        if ($updateStmt->execute()) {
            $totalAtualizados++;
            echo "<p style='color: blue; font-size: 12px;'>↻ Atualizado: Mês $mesStr/$anoStr - Popular: $popular cartões, IPCV: R$ " . number_format($ipcv, 2, ',', '.') . ", Comissão Popular: R$ " . number_format($comissaoPopular, 2, ',', '.') . ", Comissão CDC: R$ " . number_format($comissaoCdc, 2, ',', '.') . "</p>\n";
        } else {
            $erroMsg = "Erro ao atualizar mês $mesStr/$anoStr: " . $updateStmt->error;
            $erros[] = $erroMsg;
            echo "<p style='color: red; font-size: 12px;'>✗ $erroMsg</p>\n";
        }
        $updateStmt->close();
    } else {
        // Inserir novo registro
        $insertStmt = $conn->prepare("
            INSERT INTO comissao_brasil_card (
                mes_competencia, ano_competencia, qtd_popular, comissao_popular, 
                fat_cdc, comissao_cdc, data
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertStmt->bind_param("sssssss", 
            $mesStr,
            $anoStr,
            $popularStr,
            $comissaoPopularStr,
            $ipcvStr,
            $comissaoCdcStr,
            $dataAtualStr
        );
        
        if ($insertStmt->execute()) {
            $totalSalvos++;
            echo "<p style='color: green; font-size: 12px;'>✓ Inserido: Mês $mesStr/$anoStr - Popular: $popular cartões, IPCV: R$ " . number_format($ipcv, 2, ',', '.') . ", Comissão Popular: R$ " . number_format($comissaoPopular, 2, ',', '.') . ", Comissão CDC: R$ " . number_format($comissaoCdc, 2, ',', '.') . "</p>\n";
        } else {
            $erroMsg = "Erro ao inserir mês $mesStr/$anoStr: " . $insertStmt->error;
            $erros[] = $erroMsg;
            echo "<p style='color: red; font-size: 12px;'>✗ $erroMsg</p>\n";
        }
        $insertStmt->close();
    }
    
    flush();
    ob_flush();
}

// NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script

// Exibir resultados
echo "<hr>";
echo "<h3>Processamento Concluído!</h3>";
echo "<p><strong>Total de linhas processadas:</strong> $totalProcessados</p>";
echo "<p><strong>Meses/Anos processados:</strong> " . count($dadosAgregados) . "</p>";
echo "<p><strong>Novos registros salvos:</strong> $totalSalvos</p>";
echo "<p><strong>Registros atualizados:</strong> $totalAtualizados</p>";
echo "<p><strong>Total de registros processados com sucesso:</strong> " . ($totalSalvos + $totalAtualizados) . "</p>";

if (!empty($erros)) {
    echo "<h4>Erros encontrados:</h4>";
    echo "<ul>";
    foreach (array_slice($erros, 0, 20) as $erro) {
        echo "<li>" . htmlspecialchars($erro) . "</li>";
    }
    if (count($erros) > 20) {
        echo "<li>... e mais " . (count($erros) - 20) . " erros</li>";
    }
    echo "</ul>";
}

?>
