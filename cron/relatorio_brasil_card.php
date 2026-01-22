<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use GuzzleHttp\Client as GuzzleClient;

// Inicializar o cliente do Google para ler os dados do Google Sheets
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

$spreadsheetId = '1J9Lr5k7i52w1frr8OY-0I2cFS8SzXIs-Xd9P2TiijQc';

try {
    // 1. Captura a data na célula A6
    $dateRange = 'A6';
    $responseDate = $service->spreadsheets_values->get($spreadsheetId, $dateRange);
    $dateValues = $responseDate->getValues();

    if (!empty($dateValues) && !empty($dateValues[0][0])) {
        $updatedDate = DateTime::createFromFormat('d/m/Y', $dateValues[0][0]);
        if ($updatedDate) {
            $formattedDate = $updatedDate->format('Y-m-d'); // Formato ISO 8601
            echo "Data 'Atualizada Até': " . $formattedDate . "<br>";
        } else {
            throw new Exception("Erro ao formatar a data.");
        }
    } else {
        throw new Exception("Erro: Não foi possível obter a data de 'Atualizado Até' do Google Sheets.");
    }

    // 2. Captura os dados do intervalo R12:AC
    $dataRange = 'R12:AC';
    $responseData = $service->spreadsheets_values->get($spreadsheetId, $dataRange);
    $dataValues = $responseData->getValues();

    if (!empty($dataValues)) {
        echo "Dados do intervalo R12:AC:<br>";
        foreach ($dataValues as $row) {
            // Processa cada linha de dados
            echo "<pre>";
            print_r($row); // Substitua por lógica para salvar/processar os dados
            echo "</pre>";
        }
    } else {
        echo "Nenhum dado encontrado no intervalo especificado.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
