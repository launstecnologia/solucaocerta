<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../login/session.php';
include '../includes/header.php';

// Obter filtros da requisição
$filtroRepresentante = $_GET['representante'] ?? null;
$filtroCliente = $_GET['cliente'] ?? null;
$filtroFaturamento = $_GET['faturamento'] ?? null;
$filtroAprovadas = $_GET['aprovadas'] ?? null;
$filtroIndice = $_GET['indice'] ?? null;
$filtroMes = $_GET['mes'] ?? null;
$filtroAno = $_GET['ano'] ?? null;

// Query base para buscar os dados
$queryDados = "
    SELECT 
        f.id, 
        f.id_cliente, 
        c.nome_fantasia AS cliente_nome, 
        f.faturamento, 
        f.qtd_venda, 
        f.aprovada, 
        f.reprovada, 
        f.indice, 
        f.data_update, 
        f.mes, 
        f.ano, 
        GROUP_CONCAT(DISTINCT r.nome SEPARATOR ', ') AS representante_nome
    FROM 
        fat_sou_facil f
    LEFT JOIN 
        cliente c 
    ON 
        f.id_cliente = c.id
    LEFT JOIN 
        cliente_representante cr 
    ON 
        cr.id_cliente = c.id
    LEFT JOIN 
        representante r 
    ON 
        cr.id_representante = r.id
    WHERE 1=1
";

// Adicionar filtros dinâmicos
$params = [];
if ($filtroRepresentante) {
    $queryDados .= " AND r.nome LIKE ?";
    $params[] = "%$filtroRepresentante%";
}
if ($filtroCliente) {
    $queryDados .= " AND c.nome_fantasia LIKE ?";
    $params[] = "%$filtroCliente%";
}
if ($filtroFaturamento === '1') {
    $queryDados .= " AND f.faturamento > 0";
} elseif ($filtroFaturamento === '0') {
    $queryDados .= " AND (f.faturamento IS NULL OR f.faturamento = 0)";
}
if ($filtroAprovadas === '1') {
    $queryDados .= " AND f.aprovada > 0";
} elseif ($filtroAprovadas === '0') {
    $queryDados .= " AND (f.aprovada IS NULL OR f.aprovada = 0)";
}
if ($filtroIndice) {
    $queryDados .= " AND f.indice LIKE ?";
    $params[] = "%$filtroIndice%";
}
if ($filtroMes) {
    $queryDados .= " AND f.mes = ?";
    $params[] = $filtroMes;
}
if ($filtroAno) {
    $queryDados .= " AND f.ano = ?";
    $params[] = $filtroAno;
}

$queryDados .= "
    GROUP BY 
        f.id, f.id_cliente, c.nome_fantasia, f.faturamento, f.qtd_venda, 
        f.aprovada, f.reprovada, f.indice, f.data_update, f.mes, f.ano
    ORDER BY 
        f.ano DESC, f.mes DESC, f.data_update DESC";

$stmt = $conn->prepare($queryDados);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}

// Obter lista de representantes para o filtro
$queryRepresentantes = "SELECT DISTINCT nome FROM representante ORDER BY nome ASC";
$representantesResult = $conn->query($queryRepresentantes);

if (!$representantesResult) {
    die("Erro ao buscar representantes: " . $conn->error);
}

// Obter lista de clientes para o filtro
$queryClientes = "SELECT DISTINCT id, nome_fantasia FROM cliente ORDER BY nome_fantasia ASC";
$clientesResult = $conn->query($queryClientes);

if (!$clientesResult) {
    die("Erro ao buscar clientes: " . $conn->error);
}

// Função para converter número do mês em nome do mês
function getMesNome($mes) {
    $meses = [
        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
        '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
        '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
    ];
    return $meses[$mes] ?? $mes;
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">CRUD - Faturamento Sou Fácil</h5>

            <!-- Botão para abrir o modal de cadastro -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalCadastro">
                Cadastrar Novo Registro
            </button>

            <!-- Filtros -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="representante" class="form-label">Representante</label>
                    <input type="text" class="form-control" id="representante" name="representante" value="<?php echo htmlspecialchars($filtroRepresentante); ?>">
                </div>
                <div class="col-md-3">
                    <label for="cliente" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo htmlspecialchars($filtroCliente); ?>">
                </div>
                <div class="col-md-2">
                    <label for="faturamento" class="form-label">Faturamento</label>
                    <select class="form-select" id="faturamento" name="faturamento">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $filtroFaturamento === '1' ? 'selected' : ''; ?>>Com Faturamento</option>
                        <option value="0" <?php echo $filtroFaturamento === '0' ? 'selected' : ''; ?>>Sem Faturamento</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="aprovadas" class="form-label">Aprovadas</label>
                    <select class="form-select" id="aprovadas" name="aprovadas">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $filtroAprovadas === '1' ? 'selected' : ''; ?>>Com Aprovadas</option>
                        <option value="0" <?php echo $filtroAprovadas === '0' ? 'selected' : ''; ?>>Sem Aprovadas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="indice" class="form-label">Índice</label>
                    <input type="text" class="form-control" id="indice" name="indice" value="<?php echo htmlspecialchars($filtroIndice); ?>">
                </div>
                <div class="col-md-2">
                    <label for="mes" class="form-label">Mês</label>
                    <select class="form-select" id="mes" name="mes">
                        <option value="">Todos</option>
                        <?php foreach ([
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                        ] as $num => $nome) { ?>
                            <option value="<?php echo $num; ?>" <?php echo $filtroMes === $num ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="ano" class="form-label">Ano</label>
                    <input type="number" class="form-control" id="ano" name="ano" value="<?php echo htmlspecialchars($filtroAno); ?>" min="2000" max="<?php echo date('Y'); ?>">
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>

            <!-- Tabela de Dados -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Representante</th>
                            <th>Fat</th>
                            <th>Venda</th>
                            <th>Apro</th>
                            <th>Repr</th>
                            <th>Índice</th>
                            <th>Competência</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['faturamento']); ?></td>
                                <td><?php echo htmlspecialchars($row['qtd_venda']); ?></td>
                                <td><?php echo htmlspecialchars($row['aprovada']); ?></td>
                                <td><?php echo htmlspecialchars($row['reprovada']); ?></td>
                                <td><?php echo htmlspecialchars($row['indice']); ?></td>
                                <td><?php echo htmlspecialchars(getMesNome($row['mes'])); ?>/<?php echo htmlspecialchars($row['ano']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['data_update']))); ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCadastro" onclick="editarRegistro(<?php echo $row['id']; ?>)">Editar</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="excluirRegistro(<?php echo $row['id']; ?>)">Excluir</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cadastro/Edição -->
<div class="modal fade" id="modalCadastro" tabindex="-1" aria-labelledby="modalCadastroLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCadastroLabel">Cadastrar/Editar Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCadastro">
                    <input type="hidden" id="registroId" name="id">
                    <div class="mb-3">
                        <label for="cliente" class="form-label">Cliente</label>
                        <select id="cliente" name="id_cliente" class="form-select">
                            <?php while ($cliente = $clientesResult->fetch_assoc()) { ?>
                                <option value="<?php echo $cliente['id']; ?>">
                                    <?php echo htmlspecialchars($cliente['nome_fantasia']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="faturamento" class="form-label">Faturamento</label>
                        <input type="text" id="valor" name="faturamento" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="qtdVenda" class="form-label">Qtd Venda</label>
                        <input type="number" id="qtdVenda" name="qtd_venda" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="aprovada" class="form-label">Aprovada</label>
                        <input type="number" id="aprovada" name="aprovada" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="reprovada" class="form-label">Reprovada</label>
                        <input type="number" id="reprovada" name="reprovada" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="indice" class="form-label">Índice</label>
                        <input type="text" id="indice" name="indice" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="mes" class="form-label">Mês</label>
                        <select id="mes" name="mes" class="form-select">
                            <?php foreach ([
                                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                            ] as $num => $nome) { ?>
                                <option value="<?php echo $num; ?>">
                                    <?php echo $nome; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ano" class="form-label">Ano</label>
                        <input type="number" id="ano" name="ano" class="form-control" min="2000" max="<?php echo date('Y'); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para manipulação de registros -->
<script>
// Enviar formulário de cadastro/edição
const formCadastro = document.getElementById('formCadastro');
formCadastro.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    fetch('salvar_faturamento.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erro: ' + data.error);
        }
    })
    .catch(error => console.error('Erro na requisição:', error));
});

// Carregar dados para edição
function editarRegistro(id) {
    fetch(`obter_faturamento.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('registroId').value = data.registro.id;
                document.getElementById('cliente').value = data.registro.id_cliente;
                document.getElementById('valor').value = data.registro.faturamento;
                document.getElementById('qtdVenda').value = data.registro.qtd_venda;
                document.getElementById('aprovada').value = data.registro.aprovada;
                document.getElementById('reprovada').value = data.registro.reprovada;
                document.getElementById('indice').value = data.registro.indice;
                document.getElementById('mes').value = data.registro.mes;
                document.getElementById('ano').value = data.registro.ano;
            } else {
                alert('Erro ao carregar registro: ' + data.error);
            }
        });
}

// Excluir registro
function excluirRegistro(id) {
    if (confirm('Deseja realmente excluir este registro?')) {
        fetch('excluir_faturamento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Registro excluído com sucesso!');
                location.reload();
            } else {
                alert('Erro ao excluir registro: ' + data.error);
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
