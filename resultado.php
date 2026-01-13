<?php
require __DIR__ . '/vendor/autoload.php';

// Inicializar o cliente do Google
$client = new Google_Client();
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('solucaocerta-d66249bd42d5.json');
$client->setAccessType('offline');

// Instanciar o serviço Google Sheets
$service = new Google_Service_Sheets($client);

// ID da planilha e intervalo dos dados que deseja acessar
$spreadsheetId = '1J9Lr5k7i52w1frr8OY-0I2cFS8SzXIs-Xd9P2TiijQc'; // Substitua pelo ID da sua planilha
$range = 'A11:B11'; // Ajuste o intervalo para onde estão os valores "Popular" e "CDC"

// Ler dados do intervalo
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

if (empty($values)) {
    echo "Nenhum dado encontrado.";
} else {
    // Extrair os valores específicos
    $popular = $values[0][0]; // "Popular" está na coluna A11
    $cdc = $values[0][1];     // "CDC" está na coluna B11

    echo "Produção Carteira - Popular: $popular <br>";
    echo "Produção Carteira - CDC: $cdc";
}
