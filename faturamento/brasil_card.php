<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../login/session.php';
include '../includes/header.php';

// Obter filtros da requisição
$mesFiltro = isset($_GET['mes']) ? $_GET['mes'] : null;
$anoFiltro = isset($_GET['ano']) ? $_GET['ano'] : null;
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
$representanteFiltro = isset($_GET['representante']) ? $_GET['representante'] : null;

// Query base para buscar os dados
$queryDados = "
    SELECT 
        MONTH(fbc.date_update) AS mes,
        YEAR(fbc.date_update) AS ano,
        c.nome_fantasia AS cliente_nome,
        c.data_register AS cliente_data_register,
        fbc.modalidade,
        fbc.popular,
        fbc.cdc,
        fbc.aprovadas,
        fbc.negadas,
        fbc.restricoes,
        fbc.total,
        GROUP_CONCAT(DISTINCT r.nome SEPARATOR ', ') AS representante_nome
    FROM 
        fat_brasil_card fbc
    LEFT JOIN 
        cliente c 
    ON 
        fbc.id_cli = c.id
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
    $queryDados .= " AND MONTH(fbc.date_update) = ?";
    $params[] = $mesFiltro;
    $types .= 'i';
}
if ($anoFiltro) {
    $queryDados .= " AND YEAR(fbc.date_update) = ?";
    $params[] = $anoFiltro;
    $types .= 'i';
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

$queryDados .= " GROUP BY mes, ano, cliente_nome, cliente_data_register, fbc.modalidade, fbc.popular, fbc.cdc, fbc.aprovadas, fbc.negadas, fbc.restricoes, fbc.total ";
$queryDados .= " ORDER BY cliente_data_register DESC, ano DESC, mes DESC, cliente_nome ASC";

$stmt = $conn->prepare($queryDados);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result(); // Obtém o resultado da consulta
} else {
    die("Erro ao preparar a consulta: " . $conn->error);
}

// Obter lista de representantes para o filtro
$queryRepresentantes = "SELECT id, nome FROM representante ORDER BY nome ASC";
$representantesResult = $conn->query($queryRepresentantes);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Listagem de Registros - Fat Brasil Card</h5>

            <!-- Filtros -->
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="mes" class="form-label">Mês</label>
                    <select id="mes" name="mes" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($mesFiltro == $i) ? 'selected' : ''; ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="ano" class="form-label">Ano</label>
                    <select id="ano" name="ano" class="form-select">
                        <option value="">Todos</option>
                        <?php for ($i = date('Y'); $i >= 2000; $i--) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($anoFiltro == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($dataInicio); ?>">
                </div>
                <div class="col-md-3">
                    <label for="data_fim" class="form-label">Data de Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($dataFim); ?>">
                </div>
                <div class="col-md-3">
                    <label for="representante" class="form-label">Representante</label>
                    <input type="text" class="form-control" id="representante" name="representante" value="<?php echo htmlspecialchars($representanteFiltro); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>

            <!-- Tabela de Dados -->
            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Competência</th>
                            <th>Cliente</th>
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
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo str_pad($row['mes'], 2, '0', STR_PAD_LEFT); ?>/<?php echo htmlspecialchars($row['ano']); ?></td>
                                <td><?php echo htmlspecialchars($row['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['modalidade']); ?></td>
                                <td><?php echo htmlspecialchars($row['popular']); ?></td>
                                <td><?php echo htmlspecialchars($row['cdc']); ?></td>
                                <td><?php echo htmlspecialchars($row['aprovadas']); ?></td>
                                <td><?php echo htmlspecialchars($row['negadas']); ?></td>
                                <td><?php echo htmlspecialchars($row['restricoes']); ?></td>
                                <td><?php echo htmlspecialchars($row['total']); ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
