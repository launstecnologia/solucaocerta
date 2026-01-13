<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload das dependências
require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Inicializa o cliente do Google Sheets
$client = new Client();
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes(Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('solucaocerta-d66249bd42d5.json');
$client->setAccessType('offline');
$service = new Sheets($client);

// ID da planilha
$spreadsheetId = '1J9Lr5k7i52w1frr8OY-0I2cFS8SzXIs-Xd9P2TiijQc';

// Definir o intervalo de datas: dois anos para trás até a data atual
$startPeriod = new DateTime('first day of February 2023');
$endPeriod = new DateTime('last day of February 2025');

// --- Parte 1: Ler os dados dos registros ---
// Considerando que a coluna "R" contém as datas, "Z" contém a modalidade e "AC" e "AB" têm os valores de CDC e Popular.
$dataRange = 'R12:AC';
$responseData = $service->spreadsheets_values->get($spreadsheetId, $dataRange);
$dataValues = $responseData->getValues();

$popularSum = 0;
$cdcSum = 0.0;

foreach ($dataValues as $row) {
    if (isset($row[0]) && !empty($row[0])) {
        // Converte a data (coluna "R", índice 0)
        $rowDate = DateTime::createFromFormat('d/m/Y', $row[0]);

        // Verifica se a data está no intervalo desejado
        if ($rowDate && $rowDate >= $startPeriod && $rowDate <= $endPeriod) {
            // Obtém a modalidade (coluna "Z", índice 8 no intervalo selecionado)
            $modalidade = isset($row[8]) ? trim($row[8]) : '';

            // Verifica e soma os valores conforme a modalidade
            if ($modalidade === 'CDC') {
                $cdcValueRaw = isset($row[11]) ? $row[11] : '0'; // Coluna "AC" (índice 11 no intervalo)
                $cdcValue = (float)str_replace(['R$', '.', ','], ['', '', '.'], $cdcValueRaw);
                $cdcSum += $cdcValue;
            } elseif ($modalidade === 'POPULAR') {
                $popularValue = isset($row[10]) ? (int)$row[10] : 0; // Coluna "AB" (índice 10 no intervalo)
                $popularSum += $popularValue;
            }
        }
    }
}

// --- Parte 2: Calcular as comissões ---
$comissao_popular = $popularSum * 15;
$comissao_cdc = $cdcSum * 0.02; // Exemplo de cálculo de comissão para CDC

// --- Parte 3: Salvar ou atualizar os dados no banco de dados ---
try {
    $pdo = new PDO('mysql:host=localhost;dbname=solucaocerta_platform', 'solucaocerta_lrmonine', '2jMXpX5aBnqXaSDA8D5w');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Definir competência como o primeiro mês do período de análise (Fevereiro de 2023)
    $mesCompetencia = $startPeriod->format('m');
    $anoCompetencia = $startPeriod->format('Y');

    // Verifica se já existe um registro para essa competência
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comissao_brasil_card WHERE mes_competencia = :mes AND ano_competencia = :ano");
    $checkStmt->execute([
        ':mes' => $mesCompetencia,
        ':ano' => $anoCompetencia
    ]);
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        // Atualiza o registro existente
        $updateStmt = $pdo->prepare("
            UPDATE comissao_brasil_card
            SET qtd_popular = :qtd_popular,
                comissao_popular = :comissao_popular,
                fat_cdc = :fat_cdc,
                comissao_cdc = :comissao_cdc,
                data = :data
            WHERE mes_competencia = :mes AND ano_competencia = :ano
        ");

        $updateStmt->execute([
            ':qtd_popular' => $popularSum,
            ':comissao_popular' => number_format($comissao_popular, 2, '.', ''),
            ':fat_cdc' => number_format($cdcSum, 2, '.', ''),
            ':comissao_cdc' => number_format($comissao_cdc, 2, '.', ''),
            ':data' => date('Y-m-d H:i:s'),
            ':mes' => $mesCompetencia,
            ':ano' => $anoCompetencia
        ]);

        echo "Registro atualizado com sucesso!";
    } else {
        // Insere um novo registro
        $insertStmt = $pdo->prepare("
            INSERT INTO comissao_brasil_card (
                mes_competencia,
                ano_competencia,
                qtd_popular,
                comissao_popular,
                fat_cdc,
                comissao_cdc,
                data
            ) VALUES (
                :mes_competencia,
                :ano_competencia,
                :qtd_popular,
                :comissao_popular,
                :fat_cdc,
                :comissao_cdc,
                :data
            )
        ");

        $insertStmt->execute([
            ':mes_competencia' => $mesCompetencia,
            ':ano_competencia' => $anoCompetencia,
            ':qtd_popular' => $popularSum,
            ':comissao_popular' => number_format($comissao_popular, 2, '.', ''),
            ':fat_cdc' => number_format($cdcSum, 2, '.', ''),
            ':comissao_cdc' => number_format($comissao_cdc, 2, '.', ''),
            ':data' => date('Y-m-d H:i:s')
        ]);

        echo "Dados inseridos com sucesso!";
    }
} catch (PDOException $e) {
    echo "Erro ao salvar dados: " . $e->getMessage();
}
?>
