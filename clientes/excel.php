<?php
require_once '../config/config.php';

// Função para obter os representantes associados a um cliente
function getRepresentantesExcel($conn, $cliente_id)
{
    $representantes = [];

    $sql = "SELECT nome FROM representante r 
            JOIN cliente_representante cr ON r.id = cr.id_representante 
            WHERE cr.id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $representantes[] = $row['nome'];
    }

    return implode(", ", $representantes);
}

// Função para obter a data do PDV formatada para Excel
function getDataPDVExcel($conn, $cliente_id)
{
    $sql = "SELECT data_liberacao_pdv FROM brasil_card WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && !empty($row['data_liberacao_pdv'])) {
        $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_liberacao_pdv']) ?: DateTime::createFromFormat('Y-m-d', $row['data_liberacao_pdv']);
        return $dataFormatada ? $dataFormatada->format('d/m/Y') : $row['data_liberacao_pdv'];
    }
    
    return 'N/A';
}

// Aplicar os mesmos filtros do index.php
$filtros = [];
$parametros = [];

// Verifica se o filtro padrão está ativo (exibir somente clientes com Brasil Card e sem PDV)
if (empty($_GET['desativar_filtro_padrao'])) {
    $filtros[] = "id IN (SELECT id_cliente FROM brasil_card WHERE pdv IS NULL OR pdv = '')";
}

// Aplicação de filtros adicionais se fornecidos
if (!empty($_GET['cnpj'])) {
    $filtros[] = "cnpj LIKE ?";
    $parametros[] = "%" . $_GET['cnpj'] . "%";
}
if (!empty($_GET['cpf'])) {
    $filtros[] = "cpf LIKE ?";
    $parametros[] = "%" . $_GET['cpf'] . "%";
}
if (!empty($_GET['nome_fantasia'])) {
    $filtros[] = "nome_fantasia LIKE ?";
    $parametros[] = "%" . $_GET['nome_fantasia'] . "%";
}
if (!empty($_GET['razao_social'])) {
    $filtros[] = "razao_social LIKE ?";
    $parametros[] = "%" . $_GET['razao_social'] . "%";
}
if (!empty($_GET['cidade'])) {
    $filtros[] = "cidade LIKE ?";
    $parametros[] = "%" . $_GET['cidade'] . "%";
}
if (!empty($_GET['data_inicial']) && !empty($_GET['data_final'])) {
    $filtros[] = "data_register BETWEEN ? AND ?";
    $parametros[] = $_GET['data_inicial'];
    $parametros[] = $_GET['data_final'];
}
if (!empty($_GET['representante'])) {
    $filtros[] = "id IN (SELECT id_cliente FROM cliente_representante WHERE id_representante IN (SELECT id FROM representante WHERE nome LIKE ?))";
    $parametros[] = "%" . $_GET['representante'] . "%";
}
if (!empty($_GET['pdv'])) {
    $filtros[] = "id IN (SELECT id_cliente FROM brasil_card WHERE pdv LIKE ?)";
    $parametros[] = "%" . $_GET['pdv'] . "%";
}

// Modify the product filter
if (!empty($_GET['produto'])) {
    $produtoTabela = $_GET['produto'];
    $filtros[] = "id IN (SELECT id_cliente FROM $produtoTabela WHERE id_cliente = cliente.id)";
}

// Modify the status filter 
if (!empty($_GET['status']) && !empty($_GET['produto'])) {
    $produtoTabela = "";
    if ($_GET['produto'] === "soufacil") {
        $produtoTabela = "status_processo_soufacil";
    } elseif ($_GET['produto'] === "brasil_card") {
        $produtoTabela = "status_processo_brasilcard";
    }

    if ($produtoTabela) {
        $filtros[] = "id IN (
            SELECT id_cliente 
            FROM $produtoTabela 
            WHERE status_atual = ? 
            AND id = (
                SELECT MAX(id) 
                FROM $produtoTabela t2 
                WHERE t2.id_cliente = $produtoTabela.id_cliente
            )
        )";
        $parametros[] = $_GET['status'];
    }
}

// Query para buscar os dados
$sql = "SELECT * FROM cliente";
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

// Add parameter binding if there are parameters
if (!empty($parametros)) {
    $types = str_repeat("s", count($parametros));
    $stmt->bind_param($types, ...$parametros);
}

$stmt->execute();
$result = $stmt->get_result();

// Configurar headers para download do Excel
$filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Criar arquivo CSV
$output = fopen('php://output', 'w');

// Adicionar BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos do CSV (campos essenciais)
$headers = [
    'CNPJ/CPF',
    'Nome Fantasia',
    'Estado',
    'Cidade',
    'Representante',
    'Data Cadastro',
    'Data PDV'
];

fputcsv($output, $headers, ';');

// Dados dos clientes
while ($row = $result->fetch_assoc()) {
    // Determinar CNPJ/CPF baseado no tipo de pessoa
    $cnpj_cpf = $row['tipo_pessoa'] == 'juridica' ? $row['cnpj'] : $row['adm_cpf'];
    
    $data = [
        $cnpj_cpf,
        $row['nome_fantasia'],
        $row['uf'],
        $row['cidade'],
        getRepresentantesExcel($conn, $row['id']),
        date('d/m/Y H:i:s', strtotime($row['data_register'])), // Data de cadastro formatada
        getDataPDVExcel($conn, $row['id'])
    ];
    
    fputcsv($output, $data, ';');
}

fclose($output);
// NÃO fechar conexão - ela será reutilizada e fechada automaticamente pelo PHP ao final do script
?>