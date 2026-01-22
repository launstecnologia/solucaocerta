<?php
// Configuração CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400"); // Cache por 1 dia

// Responder a requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Filtros opcionais
    $filters = [];
    $params = [];

    // Filtro por id_rep
    if (!empty($data['id_rep'])) {
        $filters[] = "id_rep = ?";
        $params[] = (int)$data['id_rep']; // Cast para inteiro
    }

    // Filtro por mês e ano (opcionais)
    if (!empty($data['mes'])) {
        $filters[] = "mes = ?";
        $params[] = (int)$data['mes'];
    }

    if (!empty($data['ano'])) {
        $filters[] = "ano = ?";
        $params[] = (int)$data['ano'];
    }

    // Paginação
    $page = !empty($data['page']) ? (int)$data['page'] : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Tabelas para buscar dados
    $tables = [
        'comissao_pag_rep' => ['id', 'id_rep', 'nome', 'documento', 'tpv', 'markup', 'comissao', 'status', 'mes', 'ano'],
        'comissao_bcard_rep' => ['id', 'id_rep', 'mes', 'ano', 'popupar', 'comissao_popular', 'cdc', 'comissao_cdc'],
        'comissao_fgta_rep' => ['id', 'id_rep', 'mes', 'ano', 'faturamento', 'comissao'],
        'comissao_redeok_rep' => ['id', 'id_rep', 'mes', 'ano', 'faturamento', 'comissao']
    ];

    $results = [];
    foreach ($tables as $table => $columns) {
        $sql = "SELECT " . implode(", ", $columns) . " FROM $table";

        if (!empty($filters)) {
            $sql .= " WHERE " . implode(" AND ", $filters);
        }
        $sql .= " LIMIT ?, ?";

        // Adicionar offset e limite aos parâmetros
        $queryParams = $params;
        $queryParams[] = (int)$offset; // Garante que seja inteiro
        $queryParams[] = (int)$perPage; // Garante que seja inteiro

        // Preparar e executar consulta
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => "Erro na preparação da consulta para a tabela $table: " . $conn->error,
                'sql' => $sql
            ]);
            exit;
        }

        $types = str_repeat("i", count($queryParams));
        $stmt->bind_param($types, ...$queryParams);

        if (!$stmt->execute()) {
            echo json_encode([
                'status' => 'error',
                'message' => "Erro na execução da consulta para a tabela $table: " . $stmt->error
            ]);
            exit;
        }

        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->close();
        $stmt->close();

        $results[$table] = $data;
    }

    // Resposta JSON
    echo json_encode([
        'status' => 'success',
        'page' => $page,
        'per_page' => $perPage,
        'data' => $results
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
}
