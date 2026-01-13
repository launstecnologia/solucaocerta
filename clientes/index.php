<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
include '../includes/header.php';


// Função para obter os produtos associados a um cliente
function getProdutos($conn, $cliente_id)
{
    $produtos = [];

    $sql_brasil_card = "SELECT * FROM brasil_card WHERE id_cliente = $cliente_id";
    $result_brasil_card = $conn->query($sql_brasil_card);
    if ($result_brasil_card->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icone_brasilcard.png' alt='Brasil Card' width='30'>";
    }

    $sql_pagseguro = "SELECT * FROM pagseguro WHERE id_cliente = $cliente_id";
    $result_pagseguro = $conn->query($sql_pagseguro);
    if ($result_pagseguro->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icone_pagseguro.png' alt='PagSeguro' width='30'>";
    }

    $sql_fgts = "SELECT * FROM fgts WHERE id_cliente = $cliente_id";
    $result_fgts = $conn->query($sql_fgts);
    if ($result_fgts->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icone_fgts.png' alt='FGTS' width='30'>";
    }

    $sql_soufacil = "SELECT * FROM soufacil WHERE id_cliente = $cliente_id";
    $result_soufacil = $conn->query($sql_soufacil);
    if ($result_soufacil->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icon_soufacil.png' alt='Sou Fácil' width='30'>";
    }

    $sql_fliper = "SELECT * FROM fliper WHERE id_cliente = $cliente_id";
    $result_fliper = $conn->query($sql_fliper);
    if ($result_fliper->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icon_flip.png' alt='Fliper' width='30'>";
    }

    $sql_parcela_facil = "SELECT * FROM parcela_facil WHERE id_cliente = $cliente_id";
    $result_parcela_facil = $conn->query($sql_parcela_facil);
    if ($result_parcela_facil->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icon_parcele.png' alt='Parcela Fácil' width='30'>";
    }

    $sql_boltcard = "SELECT * FROM boltcard WHERE id_cliente = $cliente_id";
    $result_boltcard = $conn->query($sql_boltcard);
    if ($result_boltcard->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/icon_bolt.png' alt='BoltCard' width='30'>";
    }

    $sql_parcelex = "SELECT * FROM parcelex WHERE id_cliente = $cliente_id";
    $result_parcelex = $conn->query($sql_parcelex);
    if ($result_parcelex->num_rows > 0) {
        $produtos[] = "<img src='../assets/images/logos/parcelex.svg' alt='Parcelex' width='30'>";
    }

    return implode(" ", $produtos);
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

// Configuração de paginação
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Aplicação de filtros
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
    } elseif ($_GET['produto'] === "parcelex") {
        $produtoTabela = "status_processo_parcelex";
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


$sql = "SELECT * FROM cliente";
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}
$sql .= " ORDER BY id DESC LIMIT $limite OFFSET $offset";

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

$totalRegistrosQuery = "SELECT COUNT(*) as total FROM cliente";
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

            <!-- Modal de Filtros -->
            <div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filtroModalLabel">Filtros</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="desativarFiltroPadrao" name="desativar_filtro_padrao" value="1" <?php echo isset($_GET['desativar_filtro_padrao']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="desativarFiltroPadrao">Desativar filtro padrão (exibir todos os clientes)</label>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="cnpj" class="form-control" placeholder="CNPJ" value="<?php echo $_GET['cnpj'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="cpf" class="form-control" placeholder="CPF" value="<?php echo $_GET['cpf'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="nome_fantasia" class="form-control" placeholder="Nome Fantasia" value="<?php echo $_GET['nome_fantasia'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="razao_social" class="form-control" placeholder="Razão Social" value="<?php echo $_GET['razao_social'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="cidade" class="form-control" placeholder="Cidade" value="<?php echo $_GET['cidade'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="representante" class="form-control" placeholder="Representante" value="<?php echo $_GET['representante'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="pdv" class="form-control" placeholder="PDV" value="<?php echo $_GET['pdv'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="date" name="data_inicial" class="form-control" value="<?php echo $_GET['data_inicial'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="date" name="data_final" class="form-control" value="<?php echo $_GET['data_final'] ?? ''; ?>">
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

            <h6 class="fw-semibold mb-3">Total de resultados encontrados: <?php echo $totalRegistros; ?></h6>


            <!-- Tabela de Clientes -->
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Fantasia</th>
                        <th>Cidade</th>
                        <th>Representante</th>
                        <th>PDV</th>
                        <th>Produtos</th>
                        <th>Data Cadastro</th>
                        <th>Data PDV</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nome_fantasia']; ?></td>
                            <td><?php echo $row['cidade']; ?></td>
                            <td><?php echo getRepresentantes($conn, $row['id']); ?></td>
                            <td><?php echo getPDV($conn, $row['id']); ?></td>
                            <td><?php echo getProdutos($conn, $row['id']); ?></td>
                            <td><?php echo getDataExibicao($conn, $row['id'], $row['data_register']); ?></td>
                            <td><?php echo getDataPDV($conn, $row['id']); ?></td>
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