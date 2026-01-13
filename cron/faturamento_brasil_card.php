<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Inicializar o cliente do Google para ler os dados do Google Sheets
$client = new Client();
$client->setApplicationName('Google Sheets API PHP');
$client->setScopes(Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('solucaocerta-d66249bd42d5.json');
$client->setAccessType('offline');
$service = new Sheets($client);

$spreadsheetId = '1J9Lr5k7i52w1frr8OY-0I2cFS8SzXIs-Xd9P2TiijQc';

// Lê a data do mês e ano na célula específica (A6)
$dateRange = 'A6';
$responseDate = $service->spreadsheets_values->get($spreadsheetId, $dateRange);
$dateValues = $responseDate->getValues();

if (!empty($dateValues)) {
    $updatedDate = DateTime::createFromFormat('d/m/Y', $dateValues[0][0]);
    $mes = $updatedDate->format('m');
    $ano = $updatedDate->format('Y');
} else {
    die("Erro: Não foi possível obter a data do mês e ano (A6) do Google Sheets.");
}

// Lê os dados do Google Sheets
try {
    $range = 'A11:AJ1000'; // Ajuste o intervalo conforme necessário
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $rows = $response->getValues();

    if (empty($rows)) {
        die("Nenhum dado encontrado no Google Sheets.");
    }
} catch (Exception $e) {
    die("Erro ao acessar o Google Sheets: " . $e->getMessage());
}

// Conexão com o banco de dados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=asolucaocerta_platform', 'asolucaocerta_platform', '117910Campi!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Processar as linhas do Google Sheets (ignorar o cabeçalho)
foreach ($rows as $index => $row) {
    if ($index === 0) {
        continue; // Ignorar cabeçalho
    }

    // Ajustar os índices conforme a ordem das colunas no Google Sheets
    $pdvExcel = isset($row[18]) ? str_pad($row[18], 4, '0', STR_PAD_LEFT) : null; // Coluna S
    $modalidade = isset($row[25]) ? $row[25] : null; // Coluna Z
    $popular = isset($row[27]) ? (int)$row[27] : 0; // Coluna AB
    $cdc = isset($row[28]) ? trim(str_replace(['R$', '.', ','], ['', '', '.'], $row[28])) : '0';
    $aprovadas = isset($row[30]) ? (int)$row[30] : 0; // Coluna AE
    $negadas = isset($row[31]) ? (int)$row[31] : 0; // Coluna AF
    $restricoes = isset($row[32]) ? (int)$row[32] : 0; // Coluna AG
    $pendente = isset($row[33]) ? (int)$row[33] : 0; // Coluna AH
    $cancelado = isset($row[34]) ? (int)$row[34] : 0; // Coluna AI
    $total = isset($row[35]) ? (int)$row[35] : 0; // Coluna AJ

    // Obter o id_cliente correspondente ao PDV (considerando ambas as possibilidades)
    $stmt = $pdo->prepare("
    SELECT id_cliente 
    FROM brasil_card 
    WHERE pdv = :pdvOriginal OR TRIM(LEADING '0' FROM pdv) = :pdvSemZeros
    ");

    // Normalizar o PDV do Excel, removendo zeros à esquerda
    $pdvSemZeros = ltrim($pdvExcel, '0');

    // Executar a consulta com ambas as condições
    $stmt->execute([
    ':pdvOriginal' => $pdvExcel,  // PDV exatamente como veio do Excel
    ':pdvSemZeros' => $pdvSemZeros // PDV sem zeros à esquerda
    ]);

    $idCliente = $stmt->fetchColumn();


    if ($idCliente) {
        // Verificar se já existe registro para o mesmo PDV, mês e ano
        $checkStmt = $pdo->prepare("SELECT id FROM fat_brasil_card WHERE id_cli = :id_cliente AND mes = :mes AND ano = :ano");
        $checkStmt->execute([
            ':id_cliente' => $idCliente,
            ':mes' => $mes,
            ':ano' => $ano
        ]);
        $existingId = $checkStmt->fetchColumn();

        if ($existingId) {





            // Atualizar registro existente
           $updateStmt = $pdo->prepare("UPDATE fat_brasil_card SET
                modalidade = :modalidade,
                popular = :popular,
                cdc = :cdc,
                aprovadas = :aprovadas,
                negadas = :negadas,
                restricoes = :restricoes,
                pendente = :pendente,
                cancelado = :cancelado,
                total = :total,
                date_update = NOW()
            WHERE id = :id");

            $updateStmt->execute([
                ':modalidade' => $modalidade,
                ':popular' => $popular,
                ':cdc' => $cdc,
                ':aprovadas' => $aprovadas,
                ':negadas' => $negadas,
                ':restricoes' => $restricoes,
                ':pendente' => $pendente,
                ':cancelado' => $cancelado,
                ':total' => $total,
                ':id' => $existingId
            ]);

            echo "Registro atualizado para o PDV: $pdvExcel\n<br>";
            echo "Valor bruto do CDC: " . $row['cdc'] . "<br>";
        } else {
            // Inserir novo registro
            $insertStmt = $pdo->prepare("INSERT INTO fat_brasil_card (
                id_cli, modalidade, popular, cdc, aprovadas, negadas, restricoes, pendente, cancelado, total, mes, ano, date_update
            ) VALUES (
                :id_cliente, :modalidade, :popular, :cdc, :aprovadas, :negadas, :restricoes, :pendente, :cancelado, :total, :mes, :ano, NOW()
            )");

            $insertStmt->execute([
                ':id_cliente' => $idCliente,
                ':modalidade' => $modalidade,
                ':popular' => $popular,
                ':cdc' => $cdc,
                ':aprovadas' => $aprovadas,
                ':negadas' => $negadas,
                ':restricoes' => $restricoes,
                ':pendente' => $pendente,
                ':cancelado' => $cancelado,
                ':total' => $total,
                ':mes' => $mes,
                ':ano' => $ano
            ]);

            echo "Novo registro inserido para o PDV: $pdvExcel\n<br>";
        }
    } else {
        echo "PDV não encontrado no banco: $pdvExcel\n<br>";
    }
}

?>
