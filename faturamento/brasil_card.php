<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar se há erros antes de continuar
if (!file_exists('../config/config.php')) {
    die("Erro: Arquivo config.php não encontrado!");
}

require_once '../config/config.php';

if (!file_exists('../login/session.php')) {
    die("Erro: Arquivo session.php não encontrado!");
}

require_once '../login/session.php';

if (!file_exists('../includes/header.php')) {
    die("Erro: Arquivo header.php não encontrado!");
}

include '../includes/header.php';

// Obter filtros da requisição
$mesFiltro = isset($_GET['mes']) && trim($_GET['mes']) !== '' ? (int)$_GET['mes'] : null;
$anoFiltro = isset($_GET['ano']) && trim($_GET['ano']) !== '' ? (int)$_GET['ano'] : null;
$clienteFiltro = isset($_GET['cliente']) && trim($_GET['cliente']) !== '' ? trim($_GET['cliente']) : null;
$pdvFiltro = isset($_GET['pdv']) && trim($_GET['pdv']) !== '' ? trim($_GET['pdv']) : null;
$dataInicio = isset($_GET['data_inicio']) && trim($_GET['data_inicio']) !== '' ? trim($_GET['data_inicio']) : null;
$dataFim = isset($_GET['data_fim']) && trim($_GET['data_fim']) !== '' ? trim($_GET['data_fim']) : null;
$representanteFiltro = isset($_GET['representante']) && trim($_GET['representante']) !== '' ? trim($_GET['representante']) : null;

// Paginação
$registros_por_pagina = 50;
$pagina_atual = isset($_GET['pagina']) && (int)$_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Query base para buscar os dados - usando campos mes e ano da tabela fat_brasil_card
$queryDados = "
    SELECT 
        fbc.mes,
        fbc.ano,
        c.nome_fantasia AS cliente_nome,
        c.data_register AS cliente_data_register,
        fbc.modalidade,
        fbc.popular,
        fbc.cdc,
        fbc.aprovadas,
        fbc.negadas,
        fbc.restricoes,
        fbc.total,
        bc.pdv,
        GROUP_CONCAT(DISTINCT r.nome SEPARATOR ', ') AS representante_nome
    FROM 
        fat_brasil_card fbc
    LEFT JOIN 
        cliente c 
    ON 
        fbc.id_cli = c.id
    LEFT JOIN 
        brasil_card bc
    ON 
        bc.id_cliente = c.id
    LEFT JOIN 
        cliente_representante cr 
    ON 
        cr.id_cliente = c.id
    LEFT JOIN 
        representante r 
    ON 
        cr.id_representante = r.id
    WHERE 1 = 1
";

// Adicionar filtros dinâmicos
$params = [];
$types = '';
if ($mesFiltro) {
    $queryDados .= " AND fbc.mes = ?";
    $params[] = $mesFiltro;
    $types .= 'i';
}
if ($anoFiltro) {
    $queryDados .= " AND fbc.ano = ?";
    $params[] = $anoFiltro;
    $types .= 'i';
}
if ($clienteFiltro) {
    $queryDados .= " AND c.nome_fantasia LIKE ?";
    $params[] = "%$clienteFiltro%";
    $types .= 's';
}
if ($pdvFiltro) {
    $queryDados .= " AND (bc.pdv LIKE ? OR TRIM(LEADING '0' FROM bc.pdv) LIKE ?)";
    $params[] = "%$pdvFiltro%";
    $params[] = "%$pdvFiltro%";
    $types .= 'ss';
}
if ($dataInicio) {
    $queryDados .= " AND c.data_register >= ?";
    $params[] = $dataInicio;
    $types .= 's';
}
if ($dataFim) {
    $queryDados .= " AND c.data_register <= ?";
    $params[] = $dataFim;
    $types .= 's';
}
if ($representanteFiltro) {
    $queryDados .= " AND r.nome LIKE ?";
    $params[] = "%$representanteFiltro%";
    $types .= 's';
}

$queryDados .= " GROUP BY fbc.mes, fbc.ano, cliente_nome, cliente_data_register, fbc.modalidade, fbc.popular, fbc.cdc, fbc.aprovadas, fbc.negadas, fbc.restricoes, fbc.total, bc.pdv ";
$queryDados .= " ORDER BY fbc.ano DESC, fbc.mes DESC, cliente_nome ASC";

// Salvar parâmetros originais antes de adicionar LIMIT/OFFSET
$params_originais = $params;
$types_originais = $types;

// Query para contar total de registros (usando DISTINCT para contar registros únicos)
$queryCount = "SELECT COUNT(DISTINCT CONCAT(fbc.id, '-', fbc.mes, '-', fbc.ano, '-', COALESCE(fbc.id_cli, 0))) as total
    FROM fat_brasil_card fbc
    LEFT JOIN cliente c ON fbc.id_cli = c.id
    LEFT JOIN brasil_card bc ON bc.id_cliente = c.id
    LEFT JOIN cliente_representante cr ON cr.id_cliente = c.id
    LEFT JOIN representante r ON cr.id_representante = r.id
    WHERE 1 = 1";
    
// Adicionar os mesmos filtros
if ($mesFiltro) {
    $queryCount .= " AND fbc.mes = ?";
}
if ($anoFiltro) {
    $queryCount .= " AND fbc.ano = ?";
}
if ($clienteFiltro) {
    $queryCount .= " AND c.nome_fantasia LIKE ?";
}
if ($pdvFiltro) {
    $queryCount .= " AND (bc.pdv LIKE ? OR TRIM(LEADING '0' FROM bc.pdv) LIKE ?)";
}
if ($dataInicio) {
    $queryCount .= " AND c.data_register >= ?";
}
if ($dataFim) {
    $queryCount .= " AND c.data_register <= ?";
}
if ($representanteFiltro) {
    $queryCount .= " AND r.nome LIKE ?";
}

$stmtCount = $conn->prepare($queryCount);
if (!$stmtCount) {
    die("Erro ao preparar query de contagem: " . $conn->error);
}

if (!empty($params_originais) && !empty($types_originais)) {
    if (!$stmtCount->bind_param($types_originais, ...$params_originais)) {
        die("Erro ao vincular parâmetros na contagem: " . $stmtCount->error);
    }
}

if (!$stmtCount->execute()) {
    die("Erro ao executar query de contagem: " . $stmtCount->error);
}

$resultCount = $stmtCount->get_result();
if ($resultCount && $resultCount->num_rows > 0) {
    $rowCount = $resultCount->fetch_assoc();
    $total_registros = (int)($rowCount['total'] ?? 0);
} else {
    $total_registros = 0;
}
if ($resultCount) {
    $resultCount->close();
}
$stmtCount->close();

// Query para calcular soma do CDC (usando subquery para evitar duplicatas do GROUP BY)
$querySoma = "SELECT COALESCE(SUM(subquery.cdc), 0) as soma_cdc FROM (
    SELECT DISTINCT fbc.id, fbc.cdc
    FROM fat_brasil_card fbc
    LEFT JOIN cliente c ON fbc.id_cli = c.id
    LEFT JOIN brasil_card bc ON bc.id_cliente = c.id
    LEFT JOIN cliente_representante cr ON cr.id_cliente = c.id
    LEFT JOIN representante r ON cr.id_representante = r.id
    WHERE 1 = 1";
    
// Adicionar os mesmos filtros
if ($mesFiltro) {
    $querySoma .= " AND fbc.mes = ?";
}
if ($anoFiltro) {
    $querySoma .= " AND fbc.ano = ?";
}
if ($clienteFiltro) {
    $querySoma .= " AND c.nome_fantasia LIKE ?";
}
if ($pdvFiltro) {
    $querySoma .= " AND (bc.pdv LIKE ? OR TRIM(LEADING '0' FROM bc.pdv) LIKE ?)";
}
if ($dataInicio) {
    $querySoma .= " AND c.data_register >= ?";
}
if ($dataFim) {
    $querySoma .= " AND c.data_register <= ?";
}
if ($representanteFiltro) {
    $querySoma .= " AND r.nome LIKE ?";
}

$querySoma .= ") as subquery";

$stmtSoma = $conn->prepare($querySoma);
$soma_cdc_total = 0;
if (!$stmtSoma) {
    die("Erro ao preparar query de soma: " . $conn->error);
}

if (!empty($params_originais) && !empty($types_originais)) {
    if (!$stmtSoma->bind_param($types_originais, ...$params_originais)) {
        die("Erro ao vincular parâmetros na soma: " . $stmtSoma->error);
    }
}

if (!$stmtSoma->execute()) {
    die("Erro ao executar query de soma: " . $stmtSoma->error);
}

$resultSoma = $stmtSoma->get_result();
if ($resultSoma && $resultSoma->num_rows > 0) {
    $rowSoma = $resultSoma->fetch_assoc();
    $soma_cdc_total = (float)($rowSoma['soma_cdc'] ?? 0);
}
if ($resultSoma) {
    $resultSoma->close();
}
$stmtSoma->close();

// Adicionar LIMIT e OFFSET à query principal
$queryDados .= " LIMIT ? OFFSET ?";
$params = $params_originais;
$params[] = $registros_por_pagina;
$params[] = $offset;
$types = $types_originais . 'ii';

$stmt = $conn->prepare($queryDados);

if (!$stmt) {
    die("Erro ao preparar a consulta: " . $conn->error);
}

// Sempre haverá pelo menos 2 parâmetros (LIMIT e OFFSET)
if (!$stmt->bind_param($types, ...$params)) {
    die("Erro ao vincular parâmetros: " . $stmt->error . " | Types: " . $types . " | Params count: " . count($params));
}

if (!$stmt->execute()) {
    die("Erro ao executar a consulta: " . $stmt->error);
}

$result = $stmt->get_result();

if (!$result) {
    die("Erro ao obter resultado: " . $conn->error);
}

// Calcular total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obter lista de representantes para o filtro
$queryRepresentantes = "SELECT id, nome FROM representante ORDER BY nome ASC";
$representantesResult = $conn->query($queryRepresentantes);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Listagem de Registros - Fat Brasil Card</h5>

            <!-- Botão de Importar CSV -->
            <button class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                <i class="fas fa-upload"></i> Importar CSV
            </button>

            <!-- Modal de Importação de CSV -->
            <div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importCsvModalLabel">Importar CSV - Faturamento Brasil Card</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="import_csv.php" method="POST" enctype="multipart/form-data" id="importCsvForm">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Formato do CSV:</strong><br>
                                    • Separador: ponto e vírgula (;)<br>
                                    • Colunas: DATA;PDV;FANTASIA;MODALIDADE;POPULAR;CDC;APROVADAS;NEGADAS;RESTRICOES;PENDENTE;CANCELADAS;PR CADASTRO;TOTAL<br>
                                    • Tamanho máximo: 10MB<br>
                                    • Data no formato: dd/mm/yyyy
                                </div>
                                
                                <div class="mb-3">
                                    <label for="competencia_mes" class="form-label">Competência - Mês</label>
                                    <select class="form-control" id="competencia_mes" name="competencia_mes" required>
                                        <option value="">Selecione o mês</option>
                                        <option value="1">Janeiro</option>
                                        <option value="2">Fevereiro</option>
                                        <option value="3">Março</option>
                                        <option value="4">Abril</option>
                                        <option value="5">Maio</option>
                                        <option value="6">Junho</option>
                                        <option value="7">Julho</option>
                                        <option value="8">Agosto</option>
                                        <option value="9">Setembro</option>
                                        <option value="10">Outubro</option>
                                        <option value="11">Novembro</option>
                                        <option value="12">Dezembro</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="competencia_ano" class="form-label">Competência - Ano</label>
                                    <select class="form-control" id="competencia_ano" name="competencia_ano" required>
                                        <option value="">Selecione o ano</option>
                                        <?php 
                                        $ano_atual = date('Y');
                                        for ($i = $ano_atual; $i >= 2020; $i--) { 
                                            echo "<option value=\"$i\">$i</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="arquivo_csv" class="form-label">Selecionar Arquivo CSV</label>
                                    <input type="file" class="form-control" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                                    <div class="form-text">Selecione um arquivo CSV para importar</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Importar CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label for="mes" class="form-label">Mês (Competência)</label>
                    <select id="mes" name="mes" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($mesFiltro == $i) ? 'selected' : ''; ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="ano" class="form-label">Ano (Competência)</label>
                    <select id="ano" name="ano" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = date('Y'); $i >= 2020; $i--) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($anoFiltro == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Nome do cliente" value="<?php echo htmlspecialchars($clienteFiltro ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="pdv" class="form-label">PDV</label>
                    <input type="text" class="form-control" id="pdv" name="pdv" placeholder="Número do PDV" value="<?php echo htmlspecialchars($pdvFiltro ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="representante" class="form-label">Representante</label>
                    <input type="text" class="form-control" id="representante" name="representante" placeholder="Nome do representante" value="<?php echo htmlspecialchars($representanteFiltro ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($dataInicio ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label for="data_fim" class="form-label">Data de Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($dataFim ?? ''); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="brasil_card.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpar Filtros
                    </a>
                </div>
            </form>

            <!-- Informações de Total e Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <strong>Total de Registros:</strong> <?php echo $total_registros; ?> | 
                    <strong>Faturamento Total (CDC):</strong> <span class="text-success fw-bold">R$ <?php echo number_format($soma_cdc_total, 2, ',', '.'); ?></span>
                </div>
                <div>
                    <small class="text-muted">
                        Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas > 0 ? $total_paginas : 1; ?>
                    </small>
                </div>
            </div>

            <!-- Tabela de Dados -->
            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Competência</th>
                            <th>Cliente</th>
                            <th>PDV</th>
                            <th>Modalidade</th>
                            <th>Popular</th>
                            <th>CDC</th>
                            <th>Apro</th>
                            <th>Neg</th>
                            <th>Rest</th>
                            <th>Total</th>
                            <th>Representante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $registros_exibidos = 0;
                        while ($row = $result->fetch_assoc()) { 
                            $registros_exibidos++;
                        ?>
                            <tr>
                                <td><?php echo str_pad($row['mes'], 2, '0', STR_PAD_LEFT); ?>/<?php echo htmlspecialchars($row['ano']); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['pdv'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['modalidade']); ?></td>
                                <td><?php echo htmlspecialchars($row['popular']); ?></td>
                                <td><?php echo 'R$ ' . number_format($row['cdc'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['aprovadas']); ?></td>
                                <td><?php echo htmlspecialchars($row['negadas']); ?></td>
                                <td><?php echo htmlspecialchars($row['restricoes']); ?></td>
                                <td><?php echo htmlspecialchars($row['total']); ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($registros_exibidos == 0) { ?>
                            <tr>
                                <td colspan="11" class="text-center">Nenhum registro encontrado com os filtros aplicados.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
            <nav aria-label="Navegação de páginas" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    // Construir URL com filtros
                    $url_params = $_GET;
                    unset($url_params['pagina']);
                    $url_base = '?' . http_build_query($url_params) . '&pagina=';
                    
                    // Primeira página
                    if ($pagina_atual > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . '1">Primeira</a></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . ($pagina_atual - 1) . '">Anterior</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Primeira</span></li>';
                        echo '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
                    }
                    
                    // Páginas numeradas
                    $inicio = max(1, $pagina_atual - 2);
                    $fim = min($total_paginas, $pagina_atual + 2);
                    
                    if ($inicio > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . '1">1</a></li>';
                        if ($inicio > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $inicio; $i <= $fim; $i++) {
                        if ($i == $pagina_atual) {
                            echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="' . $url_base . $i . '">' . $i . '</a></li>';
                        }
                    }
                    
                    if ($fim < $total_paginas) {
                        if ($fim < $total_paginas - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . $total_paginas . '">' . $total_paginas . '</a></li>';
                    }
                    
                    // Última página
                    if ($pagina_atual < $total_paginas) {
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . ($pagina_atual + 1) . '">Próxima</a></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $url_base . $total_paginas . '">Última</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Próxima</span></li>';
                        echo '<li class="page-item disabled"><span class="page-link">Última</span></li>';
                    }
                    ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Fechar resultados e statements
if (isset($result)) {
    $result->close();
}
if (isset($stmt)) {
    $stmt->close();
}
if (isset($representantesResult)) {
    $representantesResult->close();
}
include '../includes/footer.php'; 
?>
