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

    if (!empty($data) && isset($data['id_representante'])) {
        $id_representante = $data['id_representante'];

        // Filtros opcionais
        $filters = [];
        $params = [$id_representante];

        if (!empty($data['cpf_cnpj'])) {
            $filters[] = "(c.cpf LIKE ? OR c.cnpj LIKE ?)";
            $params[] = "%" . $data['cpf_cnpj'] . "%";
            $params[] = "%" . $data['cpf_cnpj'] . "%";
        }

        if (!empty($data['nome_fantasia'])) {
            $filters[] = "c.nome_fantasia LIKE ?";
            $params[] = "%" . $data['nome_fantasia'] . "%";
        }

        if (!empty($data['cidade'])) {
            $filters[] = "c.cidade LIKE ?";
            $params[] = "%" . $data['cidade'] . "%";
        }

        if (!empty($data['data_inicio']) && !empty($data['data_fim'])) {
            $filters[] = "c.data_register BETWEEN ? AND ?";
            $params[] = $data['data_inicio'];
            $params[] = $data['data_fim'];
        }

        // Paginação
        $page = !empty($data['page']) ? (int)$data['page'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Construção do SQL
        $sql = "SELECT c.id, c.nome, c.email, c.cidade, c.private, c.assisfinan, c.nome_fantasia
                FROM cliente c
                JOIN cliente_representante cr ON c.id = cr.id_cliente
                WHERE cr.id_representante = ?";
        if (!empty($filters)) {
            $sql .= " AND " . implode(" AND ", $filters);
        }
        $sql .= " LIMIT ?, ?";

        // Adicionar offset e limite
        $params[] = $offset;
        $params[] = $perPage;

        // Preparar e executar consulta
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $clientes = [];
        while ($cliente = $result->fetch_assoc()) {
            // Obter produtos associados
            $produtos = [];
            if ($conn->query("SELECT id FROM brasil_card WHERE id_cliente = {$cliente['id']}")->num_rows > 0) {
                $produtos[] = "Brasil Card";
            }
            if ($conn->query("SELECT id FROM check_ok WHERE id_cliente = {$cliente['id']}")->num_rows > 0) {
                $produtos[] = "Check OK";
            }
            if ($conn->query("SELECT id FROM fgts WHERE id_cliente = {$cliente['id']}")->num_rows > 0) {
                $produtos[] = "FGTS";
            }
            if ($conn->query("SELECT id FROM ok_antecipa WHERE id_cliente = {$cliente['id']}")->num_rows > 0) {
                $produtos[] = "Ok Antecipa";
            }
            if ($conn->query("SELECT id FROM pagseguro WHERE id_cliente = {$cliente['id']}")->num_rows > 0) {
                $produtos[] = "PagSeguro";
            }
            if ($cliente['private'] == 1) {
                $produtos[] = "Private";
            }
            if ($cliente['assisfinan'] == 1) {
                $produtos[] = "Assistente Financeiro";
            }

            $clientes[] = [
                'id' => $cliente['id'],
                'nome' => $cliente['nome'],
                'email' => $cliente['email'],
                'cidade' => $cliente['cidade'],
                'nome_fantasia' => $cliente['nome_fantasia'],
                'produtos' => $produtos
            ];
        }

        // Contar total de resultados sem limite
        $countSql = "SELECT COUNT(*) AS total
                     FROM cliente c
                     JOIN cliente_representante cr ON c.id = cr.id_cliente
                     WHERE cr.id_representante = ?";
        if (!empty($filters)) {
            $countSql .= " AND " . implode(" AND ", $filters);
        }
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param(str_repeat("s", count($params) - 2), ...array_slice($params, 0, -2));
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalResults = $countResult->fetch_assoc()['total'];

        echo json_encode([
            'status' => 'success',
            'total_results' => $totalResults,
            'page' => $page,
            'per_page' => $perPage,
            'clientes' => $clientes
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID do representante é obrigatório.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método inválido. Use POST.'
    ]);
}
?>
