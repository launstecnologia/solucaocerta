<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
include '../includes/header.php';


// Função para obter os produtos associados a um cliente com suas datas
function getProdutos($conn, $cliente_id)
{
    $produtos = [];
    
    // Array com produtos, suas tabelas e ícones
    $produtos_config = [
        ['tabela' => 'brasil_card', 'nome' => 'Brasil Card', 'icone' => '../assets/images/logos/icone_brasilcard.png'],
        ['tabela' => 'parcelex', 'nome' => 'Parcelex', 'icone' => '../assets/images/logos/parcelex.svg'],
        ['tabela' => 'fgts', 'nome' => 'FGTS', 'icone' => '../assets/images/logos/icone_fgts.png'],
        ['tabela' => 'pagseguro', 'nome' => 'PagSeguro', 'icone' => '../assets/images/logos/icone_pagseguro.png'],
        ['tabela' => 'soufacil', 'nome' => 'Sou Fácil', 'icone' => '../assets/images/logos/icon_soufacil.png'],
        ['tabela' => 'fliper', 'nome' => 'Fliper', 'icone' => '../assets/images/logos/icon_flip.png'],
        ['tabela' => 'parcela_facil', 'nome' => 'Parcela Fácil', 'icone' => '../assets/images/logos/icon_parcele.png'],
        ['tabela' => 'boltcard', 'nome' => 'BoltCard', 'icone' => '../assets/images/logos/icon_bolt.png']
    ];
    
    foreach ($produtos_config as $produto) {
        try {
            $sql = "SELECT data_liberacao_pdv FROM {$produto['tabela']} WHERE id_cliente = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $cliente_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $dataFormatada = '';
                    
                    // Tenta formatar a data se existir
                    if (!empty($row['data_liberacao_pdv'])) {
                        $dataObj = DateTime::createFromFormat('Y-m-d', $row['data_liberacao_pdv']);
                        if (!$dataObj) {
                            $dataObj = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_liberacao_pdv']);
                        }
                        if ($dataObj) {
                            $dataFormatada = $dataObj->format('d/m/Y');
                        } else {
                            $dataFormatada = $row['data_liberacao_pdv'];
                        }
                    }
                    
                    // Monta o HTML do produto com ícone
                    $produtoHtml = '<div style="margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">';
                    $produtoHtml .= '<img src="' . $produto['icone'] . '" alt="' . $produto['nome'] . '" width="30" height="30" style="flex-shrink: 0;">';
                    if ($dataFormatada) {
                        $produtoHtml .= '<span>' . $dataFormatada . '</span>';
                    }
                    $produtoHtml .= '</div>';
                    
                    $produtos[] = $produtoHtml;
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            // Ignora erros de tabela não existente
            continue;
        }
    }
    
    return !empty($produtos) ? implode('', $produtos) : 'N/A';
}

// Função para obter os representantes associados a um cliente
function getRepresentantes($conn, $cliente_id)
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
        // Extrair apenas o primeiro nome do representante
        $primeiroNome = explode(' ', $row['nome'])[0];
        $representantes[] = $primeiroNome;
    }

    return implode(", ", $representantes);
}

// Função para obter a data de exibição formatada (agora mostra data de cadastro)
function getDataExibicao($conn, $cliente_id, $data_register)
{
    // Sempre retorna a data de cadastro formatada
    if ($data_register) {
        $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $data_register) ?: DateTime::createFromFormat('Y-m-d', $data_register);
        return $dataFormatada ? $dataFormatada->format('d/m/Y') : $data_register;
    }
    return $data_register;
}

// Função para obter o PDV associado a um cliente
function getPDV($conn, $cliente_id)
{
    $sql = "SELECT pdv FROM brasil_card WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row ? $row['pdv'] : 'N/A'; // Retorna 'N/A' se o PDV não for encontrado
}

// Função para obter a data do PDV formatada
function getDataPDV($conn, $cliente_id)
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
    
    return 'N/A'; // Retorna 'N/A' se não houver data de PDV
}

// Função para obter as datas de cadastro nas financeiras de todos os produtos
function getDatasCadastroFinanceira($conn, $cliente_id)
{
    $datas = [];
    
    // Array com produtos e seus nomes
    $produtos = [
        ['tabela' => 'brasil_card', 'nome' => 'Brasil Card'],
        ['tabela' => 'parcelex', 'nome' => 'Parcelex'],
        ['tabela' => 'fgts', 'nome' => 'FGTS'],
        ['tabela' => 'pagseguro', 'nome' => 'PagSeguro'],
        ['tabela' => 'soufacil', 'nome' => 'Sou Fácil'],
        ['tabela' => 'fliper', 'nome' => 'Fliper'],
        ['tabela' => 'parcela_facil', 'nome' => 'Parcela Fácil'],
        ['tabela' => 'boltcard', 'nome' => 'BoltCard']
    ];
    
    foreach ($produtos as $produto) {
        try {
            $sql = "SELECT data_liberacao_pdv FROM {$produto['tabela']} WHERE id_cliente = ? AND data_liberacao_pdv IS NOT NULL AND data_liberacao_pdv != '' LIMIT 1";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $cliente_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc() && !empty($row['data_liberacao_pdv'])) {
                    // Tenta formatar a data
                    $dataFormatada = DateTime::createFromFormat('Y-m-d', $row['data_liberacao_pdv']);
                    if (!$dataFormatada) {
                        $dataFormatada = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_liberacao_pdv']);
                    }
                    if ($dataFormatada) {
                        $datas[] = $produto['nome'] . " - " . $dataFormatada->format('d/m/Y');
                    } else {
                        // Se não conseguir formatar, exibe como está
                        $datas[] = $produto['nome'] . " - " . $row['data_liberacao_pdv'];
                    }
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            // Ignora erros de tabela não existente
            continue;
        }
    }
    
    return !empty($datas) ? implode('<br>', $datas) : 'N/A';
}

// Configuração de paginação
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Aplicação de filtros
$filtros = [];
$parametros = [];

// Filtro padrão: exibir somente clientes que não foram gerado PDV
// Verifica se o filtro padrão não foi desativado
if (empty($_GET['desativar_filtro_padrao'])) {
    // Busca clientes que têm Brasil Card mas não têm PDV preenchido
    $filtros[] = "c.id IN (SELECT id_cliente FROM brasil_card WHERE (pdv IS NULL OR pdv = ''))";
}

// Aplicação de filtros adicionais se fornecidos
if (isset($_GET['cnpj']) && trim($_GET['cnpj']) !== '') {
    $filtros[] = "c.cnpj LIKE ?";
    $parametros[] = "%" . trim($_GET['cnpj']) . "%";
}
if (isset($_GET['cpf']) && trim($_GET['cpf']) !== '') {
    $filtros[] = "c.adm_cpf LIKE ?";
    $parametros[] = "%" . trim($_GET['cpf']) . "%";
}
if (isset($_GET['nome_fantasia']) && trim($_GET['nome_fantasia']) !== '') {
    $filtros[] = "c.nome_fantasia LIKE ?";
    $parametros[] = "%" . trim($_GET['nome_fantasia']) . "%";
}
if (isset($_GET['razao_social']) && trim($_GET['razao_social']) !== '') {
    $filtros[] = "c.razao_social LIKE ?";
    $parametros[] = "%" . trim($_GET['razao_social']) . "%";
}
if (isset($_GET['cidade']) && trim($_GET['cidade']) !== '') {
    $filtros[] = "c.cidade LIKE ?";
    $parametros[] = "%" . trim($_GET['cidade']) . "%";
}
// Filtro por data de cadastro no sistema
if (isset($_GET['data_inicial']) && isset($_GET['data_final']) && trim($_GET['data_inicial']) !== '' && trim($_GET['data_final']) !== '') {
    // Adiciona hora 23:59:59 na data final para incluir o dia inteiro
    $data_inicial = trim($_GET['data_inicial']) . ' 00:00:00';
    $data_final = trim($_GET['data_final']) . ' 23:59:59';
    $filtros[] = "c.data_register BETWEEN ? AND ?";
    $parametros[] = $data_inicial;
    $parametros[] = $data_final;
}

// Filtro por data de cadastro do produto (data_liberacao_pdv)
if (isset($_GET['produto_data']) && trim($_GET['produto_data']) !== '' && 
    isset($_GET['data_produto_inicial']) && isset($_GET['data_produto_final']) && 
    trim($_GET['data_produto_inicial']) !== '' && trim($_GET['data_produto_final']) !== '') {
    $produtoTabela = trim($_GET['produto_data']);
    // Sanitiza o nome da tabela para evitar SQL injection
    $tabelasPermitidas = ['brasil_card', 'parcelex', 'fgts', 'pagseguro', 'soufacil', 'fliper', 'parcela_facil', 'boltcard'];
    if (in_array($produtoTabela, $tabelasPermitidas)) {
        $data_produto_inicial = trim($_GET['data_produto_inicial']);
        $data_produto_final = trim($_GET['data_produto_final']);
        $filtros[] = "c.id IN (SELECT id_cliente FROM $produtoTabela WHERE data_liberacao_pdv BETWEEN ? AND ?)";
        $parametros[] = $data_produto_inicial;
        $parametros[] = $data_produto_final;
    }
}
if (isset($_GET['representante']) && trim($_GET['representante']) !== '') {
    $filtros[] = "c.id IN (SELECT id_cliente FROM cliente_representante WHERE id_representante IN (SELECT id FROM representante WHERE nome LIKE ?))";
    $parametros[] = "%" . trim($_GET['representante']) . "%";
}
if (isset($_GET['pdv']) && trim($_GET['pdv']) !== '') {
    $filtros[] = "c.id IN (SELECT id_cliente FROM brasil_card WHERE pdv LIKE ?)";
    $parametros[] = "%" . trim($_GET['pdv']) . "%";
}



// Modify the product filter
if (isset($_GET['produto']) && trim($_GET['produto']) !== '') {
    $produtoTabela = trim($_GET['produto']);
    // Sanitiza o nome da tabela para evitar SQL injection
    $tabelasPermitidas = ['brasil_card', 'parcelex', 'fgts', 'pagseguro', 'soufacil', 'fliper', 'parcela_facil', 'boltcard'];
    if (in_array($produtoTabela, $tabelasPermitidas)) {
        $filtros[] = "c.id IN (SELECT id_cliente FROM $produtoTabela)";
    }
}

// Modify the status filter 
if (isset($_GET['status']) && trim($_GET['status']) !== '' && isset($_GET['produto']) && trim($_GET['produto']) !== '') {
    $produtoTabela = "";
    if ($_GET['produto'] === "soufacil") {
        $produtoTabela = "status_processo_soufacil";
    } elseif ($_GET['produto'] === "brasil_card") {
        $produtoTabela = "status_processo_brasilcard";
    } elseif ($_GET['produto'] === "parcelex") {
        $produtoTabela = "status_processo_parcelex";
    }

    if ($produtoTabela) {
        $filtros[] = "c.id IN (
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


/*
$sql = "SELECT * FROM cliente";
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}

$sql .= " ORDER BY id DESC LIMIT $limite OFFSET $offset";



$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}*/


$sql = "SELECT c.*, u.nome as usuario_cadastro 
        FROM cliente c 
        LEFT JOIN usuario u ON c.id_user = u.id";
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}
$sql .= " ORDER BY c.id DESC LIMIT $limite OFFSET $offset";

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

//$stmt->execute();
$result = $stmt->get_result();

$totalRegistrosQuery = "SELECT COUNT(*) as total FROM cliente c";
if (!empty($filtros)) {
    $totalRegistrosQuery .= " WHERE " . implode(" AND ", $filtros);
}
$stmtCount = $conn->prepare($totalRegistrosQuery);
if ($parametros) {
    $stmtCount->bind_param(str_repeat("s", count($parametros)), ...$parametros);
}
$stmtCount->execute();
$totalRegistrosResult = $stmtCount->get_result();
$totalRegistros = $totalRegistrosResult->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $limite);

// Função para exibir a paginação customizada com preservação dos filtros
function exibirPaginacao($pagina, $totalPaginas)
{
    // Obter os parâmetros de filtro da URL
    $queryParams = $_GET;
    $primeira = 1;
    $ultima = $totalPaginas;

    $anterior = max($pagina - 1, 1);
    $proxima = min($pagina + 1, $totalPaginas);

    $inicio = max($pagina - 1, 1);
    $fim = min($pagina + 1, $totalPaginas);

    echo '<nav class="d-flex justify-content-center">';
    echo '<ul class="pagination">';

    // Adicionar << para a primeira página
    $queryParams['pagina'] = $primeira;
    echo '<li class="page-item ' . ($pagina == $primeira ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">&laquo;&laquo;</a></li>';

    // Adicionar < para a página anterior
    $queryParams['pagina'] = $anterior;
    echo '<li class="page-item ' . ($pagina == $primeira ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">&laquo;</a></li>';

    // Links para as páginas atuais (1, 2, 3, etc.)
    for ($i = $inicio; $i <= $fim; $i++) {
        $queryParams['pagina'] = $i;
        $ativo = $i == $pagina ? 'active' : '';
        echo '<li class="page-item ' . $ativo . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">' . $i . '</a></li>';
    }

    // Adicionar > para a próxima página
    $queryParams['pagina'] = $proxima;
    echo '<li class="page-item ' . ($pagina == $ultima ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">&raquo;</a></li>';

    // Adicionar >> para a última página
    $queryParams['pagina'] = $ultima;
    echo '<li class="page-item ' . ($pagina == $ultima ? 'disabled' : '') . '"><a class="page-link" href="?' . http_build_query($queryParams) . '">&raquo;&raquo;</a></li>';

    echo '</ul>';
    echo '</nav>';
}


// Lista de status possíveis para Sou Fácil
$status_list = [
    "Cadastrado no Sistema",
    "Enviado Doc Sou Fácil",
    "Gerado Contrato",
    "Aguardando Assinatura",
    "Contrato Assinado",
    "Acessos Criados",
    "Aguardando Treinamento",
    "Treinamento Realizado"
];

// Lista de status possíveis para Brasil Card
$status_list_brasilcard = [
    "Cadastro no Sistema",
    "Enviado CRM",
    "Contrato Enviado",
    "PDV Gerado",
    "Aguardando Treinamento",
    "Treinamento Realizado"
];


?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Clientes</h5>

            <!-- Botões de ação -->
            <a href="create.php" class="btn btn-primary mb-3">Registrar Novo Cliente</a>
            <button class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#filtroModal">Filtrar</button>
            <a href="excel.php<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-success mb-3">
                <i class="fas fa-download"></i> Exportar Excel
            </a>
            <button class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="fas fa-upload"></i> Importar Excel
            </button>

            <!-- Modal de Filtros -->
            <div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filtroModalLabel">Filtros</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="desativarFiltroPadrao" name="desativar_filtro_padrao" value="1" <?php echo isset($_GET['desativar_filtro_padrao']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="desativarFiltroPadrao">Desativar filtro padrão (exibir todos os clientes)</label>
                                </div>
                                
                                <h6 class="mb-3 fw-semibold text-primary">Informações Básicas</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">CNPJ</label>
                                        <input type="text" name="cnpj" class="form-control" placeholder="CNPJ" value="<?php echo $_GET['cnpj'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CPF</label>
                                        <input type="text" name="cpf" class="form-control" placeholder="CPF" value="<?php echo $_GET['cpf'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nome Fantasia</label>
                                        <input type="text" name="nome_fantasia" class="form-control" placeholder="Nome Fantasia" value="<?php echo $_GET['nome_fantasia'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Razão Social</label>
                                        <input type="text" name="razao_social" class="form-control" placeholder="Razão Social" value="<?php echo $_GET['razao_social'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Cidade</label>
                                        <input type="text" name="cidade" class="form-control" placeholder="Cidade" value="<?php echo $_GET['cidade'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Representante</label>
                                        <input type="text" name="representante" class="form-control" placeholder="Representante" value="<?php echo $_GET['representante'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">PDV</label>
                                    <input type="text" name="pdv" class="form-control" placeholder="PDV" value="<?php echo $_GET['pdv'] ?? ''; ?>">
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3 fw-semibold text-primary">Filtros por Data</h6>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Data de Cadastro no Sistema</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Data Inicial</label>
                                            <input type="date" name="data_inicial" class="form-control" value="<?php echo $_GET['data_inicial'] ?? ''; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted">Data Final</label>
                                            <input type="date" name="data_final" class="form-control" value="<?php echo $_GET['data_final'] ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Data de Cadastro do Produto</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Produto</label>
                                            <select name="produto_data" class="form-control">
                                                <option value="">-- Selecione o Produto --</option>
                                                <option value="brasil_card" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'brasil_card') ? 'selected' : ''; ?>>Brasil Card</option>
                                                <option value="parcelex" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'parcelex') ? 'selected' : ''; ?>>Parcelex</option>
                                                <option value="fgts" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'fgts') ? 'selected' : ''; ?>>FGTS</option>
                                                <option value="pagseguro" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'pagseguro') ? 'selected' : ''; ?>>PagSeguro</option>
                                                <option value="soufacil" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'soufacil') ? 'selected' : ''; ?>>Sou Fácil</option>
                                                <option value="fliper" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'fliper') ? 'selected' : ''; ?>>Fliper</option>
                                                <option value="parcela_facil" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'parcela_facil') ? 'selected' : ''; ?>>Parcela Fácil</option>
                                                <option value="boltcard" <?php echo (isset($_GET['produto_data']) && $_GET['produto_data'] == 'boltcard') ? 'selected' : ''; ?>>BoltCard</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Data Inicial</label>
                                            <input type="date" name="data_produto_inicial" class="form-control" value="<?php echo $_GET['data_produto_inicial'] ?? ''; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Data Final</label>
                                            <input type="date" name="data_produto_final" class="form-control" value="<?php echo $_GET['data_produto_final'] ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <select name="produto" class="form-control">
                                        <option value="">-- Selecione um Produto --</option>
                                        <option value="brasil_card" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'brasil_card') ? 'selected' : ''; ?>>Brasil Card</option>
                                        <option value="pagseguro" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'pagseguro') ? 'selected' : ''; ?>>PagSeguro</option>
                                        <option value="fgts" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'fgts') ? 'selected' : ''; ?>>FGTS</option>
                                        <option value="soufacil" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'soufacil') ? 'selected' : ''; ?>>Sou Fácil</option>
                                        <option value="fliper" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'fliper') ? 'selected' : ''; ?>>Fliper</option>
                                        <option value="parcela_facil" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'parcela_facil') ? 'selected' : ''; ?>>Parcela Fácil</option>
                                        <option value="boltcard" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'boltcard') ? 'selected' : ''; ?>>BoltCard</option>
                                        <option value="parcelex" <?php echo (isset($_GET['produto']) && $_GET['produto'] == 'parcelex') ? 'selected' : ''; ?>>Parcelex</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <select name="status" id="status" class="form-control">
                                        <option value="">-- Selecione o Status --</option>
                                        <?php if (!empty($_GET['produto']) && $_GET['produto'] == 'soufacil'): ?>
                                            <?php foreach ($status_list as $status): ?>
                                                <option value="<?= $status ?>" <?= (!empty($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '' ?>>
                                                    <?= $status ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php elseif (!empty($_GET['produto']) && $_GET['produto'] == 'brasil_card'): ?>
                                            <?php foreach ($status_list_brasilcard as $status): ?>
                                                <option value="<?= $status ?>" <?= (!empty($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '' ?>>
                                                    <?= $status ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>




                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal de Importação de Excel -->
            <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importExcelModalLabel">Importar Excel/CSV</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="import_excel.php" method="POST" enctype="multipart/form-data" id="importExcelForm">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Instruções:</strong><br>
                                    • Formatos aceitos: .xlsx, .xls, .csv<br>
                                    • Tamanho máximo: 10MB<br>
                                    • O arquivo deve seguir o formato do Excel exportado
                                </div>
                                
                                <div class="mb-3">
                                    <label for="arquivo_excel" class="form-label">Selecionar Arquivo</label>
                                    <input type="file" class="form-control" id="arquivo_excel" name="arquivo_excel" accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text">Selecione um arquivo Excel ou CSV para importar</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Importar Arquivo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <h6 class="fw-semibold mb-3">Total de resultados encontrados: <?php echo $totalRegistros; ?></h6>


            <!-- Tabela de Clientes -->
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Nome Fantasia</th>
                        <th>Rep.</th>
                        <th>PDV</th>
                        <th>Produtos</th>
                        <th>Data Plataforma</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td style="white-space: normal; line-height: 1.6;">
                                <strong><?php echo $row['nome_fantasia']; ?></strong>
                                <br><small style="color: #666;"><?php echo $row['cidade']; ?><?php echo !empty($row['uf']) ? '/' . $row['uf'] : ''; ?></small>
                            </td>
                            <td><?php echo getRepresentantes($conn, $row['id']); ?></td>
                            <td><?php echo getPDV($conn, $row['id']); ?></td>
                            <td style="white-space: normal; line-height: 1.6; font-size: 0.9em;"><?php echo getProdutos($conn, $row['id']); ?></td>
                            <td style="white-space: normal; line-height: 1.6;">
                                <?php echo getDataExibicao($conn, $row['id'], $row['data_register']); ?>
                                <br><small style="color: #666;">Cad por: <?php 
                                    $nomeCompleto = $row['usuario_cadastro'] ?? 'N/A';
                                    if ($nomeCompleto != 'N/A') {
                                        $primeiroNome = explode(' ', $nomeCompleto)[0];
                                        echo $primeiroNome;
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></small>
                            </td>
                            <td>
                                <a href="detalhes.php?id=<?php echo $row['id']; ?>" class="btn btn-info">Detalhes</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <?php exibirPaginacao($pagina, $totalPaginas); ?>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
    const produtoSelect = document.querySelector("select[name='produto']");
    const statusSelect = document.querySelector("select[name='status']");

    produtoSelect.addEventListener("change", function () {
        const produto = this.value;

        // Atualizar os status disponíveis
        statusSelect.innerHTML = "<option value=''>-- Selecione o Status --</option>";
        if (produto === "soufacil") {
            <?php foreach ($status_list as $status): ?>
            statusSelect.innerHTML += `<option value="<?= $status ?>"><?= $status ?></option>`;
            <?php endforeach; ?>
        } else if (produto === "brasil_card") {
            <?php foreach ($status_list_brasilcard as $status): ?>
            statusSelect.innerHTML += `<option value="<?= $status ?>"><?= $status ?></option>`;
            <?php endforeach; ?>
        }
    });

    // Simular a troca para carregar os status caso um produto já esteja selecionado
    if (produtoSelect.value) {
        produtoSelect.dispatchEvent(new Event('change'));
    }
});

</script>

<?php include '../includes/footer.php'; ?>