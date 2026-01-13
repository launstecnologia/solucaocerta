<?php
require_once '../config/config.php';
$id_cliente = $_GET['id'];

// Buscar informações do cliente
$sql_cliente = "SELECT * FROM cliente WHERE id = ?";
$stmt_cliente = $conn->prepare($sql_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();
$cliente = $result_cliente->fetch_assoc();

// Buscar informações dos produtos
function getProductDetails($conn, $sql, $id_cliente)
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$brasil_card = getProductDetails($conn, "SELECT * FROM brasil_card WHERE id_cliente = ?", $id_cliente);
$check_ok = getProductDetails($conn, "SELECT * FROM check_ok WHERE id_cliente = ?", $id_cliente);
$fgts = getProductDetails($conn, "SELECT * FROM fgts WHERE id_cliente = ?", $id_cliente);
$ok_antecipa = getProductDetails($conn, "SELECT * FROM ok_antecipa WHERE id_cliente = ?", $id_cliente);
$pagseguro = getProductDetails($conn, "SELECT * FROM pagseguro WHERE id_cliente = ?", $id_cliente);
$soufacil = getProductDetails($conn, "SELECT * FROM soufacil WHERE id_cliente = ?", $id_cliente);
$fliper = getProductDetails($conn, "SELECT * FROM fliper WHERE id_cliente = ?", $id_cliente);
$emprestimo = getProductDetails($conn, "SELECT * FROM emprestimo WHERE id_cliente = ?", $id_cliente);
$parcela_facil = getProductDetails($conn, "SELECT * FROM parcela_facil WHERE id_cliente = ?", $id_cliente);
$boltcard = getProductDetails($conn, "SELECT * FROM boltcard WHERE id_cliente = ?", $id_cliente);
$parcelex = getProductDetails($conn, "SELECT * FROM parcelex WHERE id_cliente = ?", $id_cliente);
$parcelex = getProductDetails($conn, "SELECT * FROM parcelex WHERE id_cliente = ?", $id_cliente);

// Buscar representantes do cliente
$sql_representantes = "SELECT r.nome FROM representante r 
JOIN cliente_representante cr ON r.id = cr.id_representante 
WHERE cr.id_cliente = ?";
$stmt_representantes = $conn->prepare($sql_representantes);
$stmt_representantes->bind_param("i", $id_cliente);
$stmt_representantes->execute();
$result_representantes = $stmt_representantes->get_result();
$representantes = [];
while ($row = $result_representantes->fetch_assoc()) {
    $representantes[] = $row['nome'];
}


//Exibir documentos
// Consulta para buscar todos os documentos associados ao cliente
$stmt = $conn->prepare("
    SELECT d.tipo_documento, d.nome_arquivo, d.caminho_arquivo 
    FROM documentos_cliente d
    WHERE d.id_cliente = ?
    ORDER BY d.tipo_documento
");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$documentos = [];
while ($row = $result->fetch_assoc()) {
    $documentos[] = $row;
}



// Lista de documentos obrigatórios para Sou Fácil
$documentos_obrigatorios = [
    'Foto Interna',
    'Foto Externa',
    'Cartão CNPJ',
    'Contrato Social',
    'CNH ou RG'
];

// Consulta para verificar quais documentos do cliente foram enviados
$stmt = $conn->prepare("
    SELECT tipo_documento 
    FROM documentos_cliente 
    WHERE id_cliente = ? AND tipo_documento IN ('" . implode("','", $documentos_obrigatorios) . "')
");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$documentos_enviados = [];
while ($row = $result->fetch_assoc()) {
    $documentos_enviados[] = $row['tipo_documento'];
}

// Verificar se todos os documentos obrigatórios foram enviados
$documentos_completos = empty(array_diff($documentos_obrigatorios, $documentos_enviados));


// Lista de status possíveis
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

// Buscar os status do cliente
$sql_status = "SELECT status_atual FROM status_processo_soufacil WHERE id_cliente = ? ORDER BY data_alteracao ASC";
$stmt_status = $conn->prepare($sql_status);
$stmt_status->bind_param("i", $id_cliente);
$stmt_status->execute();
$result_status = $stmt_status->get_result();

// Variável para guardar o status atual
$status_atual = "Nenhum status registrado"; // Valor padrão
$current_index = 0; // Inicializa o índice do progresso

while ($row = $result_status->fetch_assoc()) {
    // Atualiza o índice do progresso com base nos status já alcançados
    $index = array_search($row['status_atual'], $status_list);
    if ($index !== false) {
        $current_index = max($current_index, $index + 1);
        $status_atual = $row['status_atual']; // Atualiza o status atual
    }
}

// Calcula o progresso como porcentagem
$total_status = count($status_list); // Total de etapas
$progress = ($current_index / $total_status) * 100; // Progresso em porcentagem


// Lista de status possíveis para Brasil Card
$status_list_brasilcard = [
    "Cadastro no Sistema",
    "Enviado CRM",
    "Contrato Enviado",
    "PDV Gerado",
    "Aguardando Treinamento",
    "Treinamento Realizado"
];

// Buscar os status do cliente para Brasil Card
$sql_status_brasilcard = "SELECT status_atual FROM status_processo_brasilcard WHERE id_cliente = ? ORDER BY data_alteracao ASC";
$stmt_status_brasilcard = $conn->prepare($sql_status_brasilcard);
$stmt_status_brasilcard->bind_param("i", $id_cliente);
$stmt_status_brasilcard->execute();
$result_status_brasilcard = $stmt_status_brasilcard->get_result();

// Variável para guardar o status atual
$status_atual_brasilcard = "Nenhum status registrado"; // Valor padrão
$current_index_brasilcard = 0; // Inicializa o índice do progresso

while ($row = $result_status_brasilcard->fetch_assoc()) {
    // Atualiza o índice do progresso com base nos status já alcançados
    $index = array_search($row['status_atual'], $status_list_brasilcard);
    if ($index !== false) {
        $current_index_brasilcard = max($current_index_brasilcard, $index + 1);
        $status_atual_brasilcard = $row['status_atual']; // Atualiza o status atual
    }
}

// Calcula o progresso como porcentagem
$total_status_brasilcard = count($status_list_brasilcard); // Total de etapas
$progress_brasilcard = ($current_index_brasilcard / $total_status_brasilcard) * 100; // Progresso em porcentagem




/* Listagem do FGTS */

// Lista de status possíveis para FGTS
$status_list_fgts = [ 
    "Cadastrado no Sistema",
    "Enviado para FGTS", 
    "Gerado Contrato",
    "Aguardando Assinatura",
    "Contrato Assinado",
    "Acessos Criados",
    "Aguardando Treinamento",
    "Treinamento Realizado"
];

// Buscar os status do cliente para FGTS
$sql_status_fgts = "SELECT status_atual FROM status_processo_fgts WHERE id_cliente = ? ORDER BY data_alteracao ASC";
$stmt_status_fgts = $conn->prepare($sql_status_fgts);
$stmt_status_fgts->bind_param("i", $id_cliente);
$stmt_status_fgts->execute();
$result_status_fgts = $stmt_status_fgts->get_result();

// Variável para guardar o status atual
$status_atual_fgts = "Nenhum status registrado"; // Valor padrão
$current_index_fgts = 0; // Inicializa o índice do progresso

while ($row = $result_status_fgts->fetch_assoc()) {
    // Atualiza o índice do progresso com base nos status já alcançados
    $index = array_search($row['status_atual'], $status_list_fgts);
    if ($index !== false) {
        $current_index_fgts = max($current_index_fgts, $index + 1);
        $status_atual_fgts = $row['status_atual']; // Atualiza o status atual
    }
}

// Calcula o progresso como porcentagem
$total_status_fgts = count($status_list_fgts); // Total de etapas
$progress_fgts = ($current_index_fgts / $total_status_fgts) * 100; // Progresso em porcentagem

/* Listagem do Parcelex */

// Lista de status possíveis para Parcelex (similar ao Brasil Card)
$status_list_parcelex = [
    "Cadastro no Sistema",
    "Enviado CRM",
    "Contrato Enviado",
    "PDV Gerado",
    "Aguardando Treinamento",
    "Treinamento Realizado"
];

// Buscar os status do cliente para Parcelex
$sql_status_parcelex = "SELECT status_atual FROM status_processo_parcelex WHERE id_cliente = ? ORDER BY data_alteracao ASC";
$stmt_status_parcelex = $conn->prepare($sql_status_parcelex);
$stmt_status_parcelex->bind_param("i", $id_cliente);
$stmt_status_parcelex->execute();
$result_status_parcelex = $stmt_status_parcelex->get_result();

// Variável para guardar o status atual
$status_atual_parcelex = "Nenhum status registrado"; // Valor padrão
$current_index_parcelex = 0; // Inicializa o índice do progresso

while ($row = $result_status_parcelex->fetch_assoc()) {
    // Atualiza o índice do progresso com base nos status já alcançados
    $index = array_search($row['status_atual'], $status_list_parcelex);
    if ($index !== false) {
        $current_index_parcelex = max($current_index_parcelex, $index + 1);
        $status_atual_parcelex = $row['status_atual']; // Atualiza o status atual
    }
}

// Calcula o progresso como porcentagem
$total_status_parcelex = count($status_list_parcelex); // Total de etapas
$progress_parcelex = ($current_index_parcelex / $total_status_parcelex) * 100; // Progresso em porcentagem


include '../includes/header.php';
?>

<style>
.client-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.client-info-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: none;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.info-item i {
    color: #667eea;
    margin-right: 1rem;
    width: 20px;
    text-align: center;
}

.info-label {
    font-weight: 600;
    color: #495057;
    margin-right: 0.5rem;
    min-width: 120px;
}

.info-value {
    color: #212529;
    flex: 1;
}

.product-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: none;
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.product-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #28a745;
    transition: all 0.3s ease;
    cursor: pointer;
}

.product-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-info {
    flex: 1;
    margin-left: 1rem;
}

.product-name {
    font-weight: 600;
    color: #212529;
    display: block;
    margin-bottom: 0.25rem;
}

.product-logo {
    width: 40px;
    height: 40px;
    margin-right: 1rem;
    border-radius: 8px;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-left: auto;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-pendente {
    background: #fff3cd;
    color: #856404;
}

.status-cancelado {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.btn-modern {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.section-title {
    color: #495057;
    font-weight: 700;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #667eea;
    display: inline-block;
}

.progress-modern {
    height: 25px;
    border-radius: 15px;
    background: #e9ecef;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar-modern {
    border-radius: 15px;
    transition: width 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
</style>

<div class="container-fluid">
    <!-- Header do Cliente -->
    <div class="client-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">
                    <i class="fas fa-building me-2"></i>
                    <?php echo htmlspecialchars($cliente['nome_fantasia']); ?>
                </h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo htmlspecialchars($cliente['cidade'] . ' - ' . $cliente['uf']); ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="action-buttons">
                    <a href="edit.php?id=<?php echo $id_cliente; ?>" class="btn btn-light btn-modern">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <a href="whatsapp.php?id=<?php echo $id_cliente; ?>" class="btn btn-success btn-modern">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informações Básicas -->
        <div class="col-md-6">
            <div class="client-info-card">
                <h4 class="section-title">
                    <i class="fas fa-info-circle me-2"></i>Informações Básicas
                </h4>
                
                <div class="info-item">
                    <i class="fas fa-id-card"></i>
                    <span class="info-label">Tipo:</span>
                    <span class="info-value"><?php echo $cliente['tipo_pessoa'] == 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física'; ?></span>
                </div>

                <?php if ($cliente['tipo_pessoa'] == 'juridica'): ?>
                <div class="info-item">
                    <i class="fas fa-building"></i>
                    <span class="info-label">CNPJ:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['cnpj']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-file-alt"></i>
                    <span class="info-label">Razão Social:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['razao_social']); ?></span>
                </div>
                <?php else: ?>
                <div class="info-item">
                    <i class="fas fa-user"></i>
                    <span class="info-label">CPF:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['adm_cpf']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-user-tie"></i>
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['adm_nome']); ?></span>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['email']); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <span class="info-label">Telefone 1:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['telefone1']); ?></span>
                </div>

                <?php if (!empty($cliente['telefone2'])): ?>
                <div class="info-item">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="info-label">Telefone 2:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['telefone2']); ?></span>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="info-label">Data Cadastro:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($cliente['data_register'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Endereço -->
        <div class="col-md-6">
            <div class="client-info-card">
                <h4 class="section-title">
                    <i class="fas fa-map-marker-alt me-2"></i>Endereço
                </h4>
                
                <div class="info-item">
                    <i class="fas fa-road"></i>
                    <span class="info-label">Logradouro:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['logradouro']); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-hashtag"></i>
                    <span class="info-label">Número:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['numero']); ?></span>
                </div>

                <?php if (!empty($cliente['complemento'])): ?>
                <div class="info-item">
                    <i class="fas fa-plus"></i>
                    <span class="info-label">Complemento:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['complemento']); ?></span>
                </div>
                <?php endif; ?>

                <div class="info-item">
                    <i class="fas fa-map"></i>
                    <span class="info-label">Bairro:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['bairro']); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-city"></i>
                    <span class="info-label">Cidade:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['cidade']); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-flag"></i>
                    <span class="info-label">UF:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['uf']); ?></span>
                </div>

                <div class="info-item">
                    <i class="fas fa-mail-bulk"></i>
                    <span class="info-label">CEP:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['cep']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos Contratados -->
    <div class="product-card">
        <h4 class="section-title">
            <i class="fas fa-box me-2"></i>Produtos Contratados
        </h4>
        
        <div class="product-grid">
            <?php
            $produtos = [];
            
            // Brasil Card
            if ($brasil_card) {
                $status_brasil = !empty($brasil_card['status']) ? $brasil_card['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'Brasil Card', 
                    'logo' => '../assets/images/logos/icone_brasilcard.png', 
                    'status' => $status_brasil,
                    'email' => '',
                    'senha' => ''
                ];
            }
            
            // FGTS
            if ($fgts) {
                $status_fgts = !empty($fgts['status']) ? $fgts['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'FGTS', 
                    'logo' => '../assets/images/logos/icone_fgts.png', 
                    'status' => $status_fgts,
                    'email' => $fgts['email'] ?? '',
                    'senha' => $fgts['senha'] ?? ''
                ];
            }
            
            // PagSeguro
            if ($pagseguro) {
                $status_pagseguro = !empty($pagseguro['status']) ? $pagseguro['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'PagSeguro', 
                    'logo' => '../assets/images/logos/icone_pagseguro.png', 
                    'status' => $status_pagseguro,
                    'email' => $pagseguro['email'] ?? '',
                    'senha' => $pagseguro['senha'] ?? ''
                ];
            }
            
            // Sou Fácil
            if ($soufacil) {
                $status_soufacil = !empty($soufacil['status']) ? $soufacil['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'Sou Fácil', 
                    'logo' => '../assets/images/logos/icon_soufacil.png', 
                    'status' => $status_soufacil,
                    'email' => '',
                    'senha' => ''
                ];
            }
            
            // Fliper
            if ($fliper) {
                $status_fliper = !empty($fliper['status']) ? $fliper['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'Fliper', 
                    'logo' => '../assets/images/logos/icon_flip.png', 
                    'status' => $status_fliper,
                    'email' => $fliper['email'] ?? '',
                    'senha' => $fliper['senha'] ?? ''
                ];
            }
            
            // Parcela Fácil
            if ($parcela_facil) {
                $status_parcela = !empty($parcela_facil['status']) ? $parcela_facil['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'Parcela Fácil', 
                    'logo' => '../assets/images/logos/icon_parcele.png', 
                    'status' => $status_parcela,
                    'email' => $parcela_facil['email'] ?? '',
                    'senha' => $parcela_facil['senha'] ?? ''
                ];
            }
            
            // BoltCard
            if ($boltcard) {
                $status_boltcard = !empty($boltcard['status']) ? $boltcard['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'BoltCard', 
                    'logo' => '../assets/images/logos/icon_bolt.png', 
                    'status' => $status_boltcard,
                    'email' => $boltcard['email'] ?? '',
                    'senha' => $boltcard['senha'] ?? ''
                ];
            }
            
            // Parcelex
            if ($parcelex) {
                $status_parcelex = !empty($parcelex['status']) ? $parcelex['status'] : 'Pendente';
                $produtos[] = [
                    'nome' => 'Parcelex', 
                    'logo' => '../assets/images/logos/parcelex.svg', 
                    'status' => $status_parcelex,
                    'email' => '',
                    'senha' => ''
                ];
            }

            if (!empty($produtos)) {
                foreach ($produtos as $produto) {
                    // Determinar classe CSS baseada no status
                    $statusClass = 'status-active';
                    $statusLower = strtolower($produto['status']);
                    if ($statusLower == 'pendente') {
                        $statusClass = 'status-pendente';
                    } elseif ($statusLower == 'inativo') {
                        $statusClass = 'status-inactive';
                    } elseif ($statusLower == 'cancelado') {
                        $statusClass = 'status-cancelado';
                    }
                    
                    echo '<div class="product-item" data-bs-toggle="modal" data-bs-target="#modalProduto' . str_replace(' ', '', $produto['nome']) . '">';
                    echo '<img src="' . $produto['logo'] . '" alt="' . $produto['nome'] . '" class="product-logo">';
                    echo '<div class="product-info">';
                    echo '<span class="product-name">' . $produto['nome'] . '</span>';
                    if (!empty($produto['email'])) {
                        echo '<small class="text-muted d-block"><i class="fas fa-envelope me-1"></i>' . htmlspecialchars($produto['email']) . '</small>';
                    }
                    echo '</div>';
                    echo '<span class="status-badge ' . $statusClass . '">' . htmlspecialchars($produto['status']) . '</span>';
                    echo '</div>';
                }
            } else {
                echo '<div class="text-center text-muted py-4">';
                echo '<i class="fas fa-box-open fa-3x mb-3"></i>';
                echo '<p>Nenhum produto contratado</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Representantes -->
    <div class="client-info-card">
        <h4 class="section-title">
            <i class="fas fa-users me-2"></i>Representantes
        </h4>
        
        <?php if (!empty($representantes)): ?>
            <div class="row">
                <?php foreach ($representantes as $representante): ?>
                <div class="col-md-6 mb-3">
                    <div class="info-item">
                        <i class="fas fa-user-tie"></i>
                        <span class="info-value"><?php echo htmlspecialchars($representante); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-user-slash fa-2x mb-2"></i>
                <p>Nenhum representante associado</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ações do Sistema -->
    <div class="client-info-card">
        <h4 class="section-title">
            <i class="fas fa-cogs me-2"></i>Ações do Sistema
        </h4>
        
        <div class="action-buttons">
            <button class="btn btn-danger btn-modern" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-1"></i> Excluir Cliente
            </button>
            <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#uploadDocumentosModal">
                <i class="fas fa-upload me-1"></i> Adicionar Documentos
            </button>

            <?php if ($soufacil): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#statusModalSouFacil">
                    <i class="fas fa-chart-line me-1"></i> Status Sou Fácil
                </button>
            <?php endif; ?>

            <?php if ($fliper): ?>
                <button class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#statusModalFliper">
                    <i class="fas fa-chart-line me-1"></i> Status Fliper
                </button>
            <?php endif; ?>

            <?php if ($brasil_card): ?>
                <button class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#statusModalBrasilCard">
                    <i class="fas fa-chart-line me-1"></i> Status Brasil Card
                </button>
            <?php endif; ?>

            <?php if ($fgts): ?>
                <button class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#statusModalFGTS">
                    <i class="fas fa-chart-line me-1"></i> Status FGTS
                </button>
            <?php endif; ?>

            <?php if ($parcelex): ?>
                <button class="btn btn-info btn-modern" data-bs-toggle="modal" data-bs-target="#statusModalParcelex">
                    <i class="fas fa-chart-line me-1"></i> Status Parcelex
                </button>
            <?php endif; ?>
        </div>
    </div>


    <!-- Termos e Contratos -->
    <div class="client-info-card">
        <h4 class="section-title">
            <i class="fas fa-file-contract me-2"></i>Termos e Contratos
        </h4>
        
        <div class="action-buttons">
            <?php if ($brasil_card): ?>
                <a href="termo_bcard.php?id=<?php echo $id_cliente; ?>" target="_blank" class="btn btn-success btn-modern">
                    <i class="fas fa-file-pdf me-1"></i> Gerar Termo de Adesão
                </a>
            <?php endif; ?>

            <?php if ($cliente['private'] == 1): ?>
                <a href="private.php?id=<?php echo $id_cliente; ?>" target="_blank" class="btn btn-success btn-modern">
                    <i class="fas fa-file-pdf me-1"></i> Private
                </a>
            <?php endif; ?>

            <a href="recibo.php?id=<?php echo $id_cliente; ?>" target="_blank" class="btn btn-info btn-modern">
                <i class="fas fa-file-invoice me-1"></i> Recibo
            </a>

            <?php if ($soufacil): ?>
                <a href="soufacildoc.php?id=<?php echo $id_cliente; ?>" target="_blank" class="btn btn-success btn-modern">
                    <i class="fas fa-file-pdf me-1"></i> Gerar Doc Sou Fácil
                </a>
            <?php endif; ?>

            <?php if ($documentos_completos): ?>
                <a href="gerar_zip.php?id=<?= $id_cliente ?>&produto=soufacil" class="btn btn-info btn-modern">
                    <i class="fas fa-file-archive me-1"></i> Gerar ZIP Sou Fácil
                </a>
            <?php endif; ?>
        </div>
    </div>


    <!-- Ações dos Produtos -->
    <div class="client-info-card">
        <h4 class="section-title">
            <i class="fas fa-tools me-2"></i>Ações dos Produtos
        </h4>
        
        <div class="action-buttons">
            <?php if ($brasil_card): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalBrasilCard">
                    <i class="fas fa-credit-card me-1"></i> Brasil Card
                </button>
            <?php endif; ?>

            <?php if ($check_ok): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalCheckOk">
                    <i class="fas fa-check-circle me-1"></i> Check OK
                </button>
            <?php endif; ?>

            <?php if ($fgts): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalFgts">
                    <i class="fas fa-university me-1"></i> FGTS
                </button>
            <?php endif; ?>

            <?php if ($ok_antecipa): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalOkAntecipa">
                    <i class="fas fa-clock me-1"></i> Ok Antecipa
                </button>
            <?php endif; ?>

            <?php if ($pagseguro): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalPagseguro">
                    <i class="fas fa-credit-card me-1"></i> PagSeguro
                </button>
            <?php endif; ?>

            <?php if ($soufacil): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalsoufacil">
                    <i class="fas fa-handshake me-1"></i> Sou Fácil
                </button>
            <?php endif; ?>

            <?php if ($fliper): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalfliper">
                    <i class="fas fa-exchange-alt me-1"></i> Fliper
                </button>
            <?php endif; ?>

            <?php if ($parcela_facil): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalParcelaFacil">
                    <i class="fas fa-calendar-check me-1"></i> Parcela Fácil
                </button>
            <?php endif; ?>

            <?php if ($boltcard): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalBoltCard">
                    <i class="fas fa-bolt me-1"></i> BoltCard
                </button>
            <?php endif; ?>

            <?php if ($parcelex): ?>
                <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#modalParcelex">
                    <i class="fas fa-handshake me-1"></i> Parcelex
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status dos Produtos -->
    <div class="row">
        <?php if ($soufacil): ?>
            <div class="col-md-6">
                <div class="client-info-card">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>Status Sou Fácil
                    </h4>
                    <div>
                        <p><strong>Status Atual:</strong> <?= $status_atual ?: "Nenhum status registrado" ?></p>
                        <div class="progress-modern">
                            <div class="progress-bar-modern bg-primary" style="width: <?= $progress ?: 0 ?>%;">
                                <?= round($progress) ?: 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($brasil_card): ?>
            <div class="col-md-6">
                <div class="client-info-card">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>Status Brasil Card
                    </h4>
                    <div>
                        <p><strong>Status Atual:</strong> <?= $status_atual_brasilcard ?: "Nenhum status registrado" ?></p>
                        <div class="progress-modern">
                            <div class="progress-bar-modern bg-success" style="width: <?= $progress_brasilcard ?: 0 ?>%;">
                                <?= round($progress_brasilcard) ?: 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($fgts): ?>
            <div class="col-md-6">
                <div class="client-info-card">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>Status FGTS
                    </h4>
                    <div>
                        <p><strong>Status Atual:</strong> <?= $status_atual_fgts ?: "Nenhum status registrado" ?></p>
                        <div class="progress-modern">
                            <div class="progress-bar-modern bg-info" style="width: <?= $progress_fgts ?: 0 ?>%;">
                                <?= round($progress_fgts) ?: 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($parcelex): ?>
            <div class="col-md-6">
                <div class="client-info-card">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line me-2"></i>Status Parcelex
                    </h4>
                    <div>
                        <p><strong>Status Atual:</strong> <?= $status_atual_parcelex ?: "Nenhum status registrado" ?></p>
                        <div class="progress-modern">
                            <div class="progress-bar-modern bg-info" style="width: <?= $progress_parcelex ?: 0 ?>%;">
                                <?= round($progress_parcelex) ?: 0 ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <!-- Documentos do Cliente -->
    <div class="client-info-card">
        <h4 class="section-title">
            <i class="fas fa-file-alt me-2"></i>Documentos do Cliente
        </h4>
        
        <?php if (!empty($documentos)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-tag me-1"></i>Tipo</th>
                            <th><i class="fas fa-file me-1"></i>Arquivo</th>
                            <th><i class="fas fa-cogs me-1"></i>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <?php
                            // Ajustar caminho do arquivo para URL correta
                            $path = $doc['caminho_arquivo'];
                            
                            // Garantir que o caminho seja absoluto (comece com /)
                            if (strpos($path, '/uploads/') === 0) {
                                // Já é um caminho absoluto correto
                            } elseif (strpos($path, '../uploads/') === 0) {
                                // Converter caminho relativo para absoluto
                                $path = '/' . str_replace('../', '', $path);
                            } elseif (strpos($path, 'uploads/') === 0) {
                                // Adicionar / no início
                                $path = '/' . $path;
                            } else {
                                // Se não tem caminho claro, assume que é só o nome do arquivo
                                $path = '/uploads/documentos/' . basename($path);
                            }
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?= $doc['tipo_documento'] ?></span>
                                </td>
                                <td><?= $doc['nome_arquivo'] ?></td>
                                <td>
                                    <a href="<?= $path ?>" target="_blank" class="btn btn-outline-primary btn-sm btn-modern">
                                        <i class="fas fa-eye me-1"></i> Abrir
                                    </a>
                                    <a href="<?= $path ?>" download class="btn btn-outline-success btn-sm btn-modern">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-file-alt fa-3x mb-3"></i>
                <p>Nenhum documento encontrado para este cliente.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'brasilcard.php' ?>
<?php include 'parcelex.php' ?>

<!-- Modal para editar Check OK -->
<div class="modal fade" id="modalCheckOk" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Check OK</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCheckOk" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_check_ok" value="1">
                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <input type="text" class="form-control" name="plano" id="plano" value="<?php echo $check_ok['plano']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" class="form-control" name="status" id="status" value="<?php echo $check_ok['status']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $check_ok['obs']; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formCheckOk').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar FGTS -->
<div class="modal fade" id="modalFgts" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar FGTS</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formFgts" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_fgts" value="1">
                    <div class="mb-3">
                        <label for="link" class="form-label">Link</label>
                        <input type="text" class="form-control" name="link" id="link" value="<?php echo $fgts['link']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $fgts['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" value="<?php echo $fgts['senha']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($fgts['status']) && $fgts['status'] == 'Pendente') ? 'selected' : (!isset($fgts['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($fgts['status']) && $fgts['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($fgts['status']) && $fgts['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($fgts['status']) && $fgts['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $fgts['obs']; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formFgts').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Ok Antecipa -->
<div class="modal fade" id="modalOkAntecipa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Ok Antecipa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formOkAntecipa" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_ok_antecipa" value="1">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" class="form-control" name="status" id="status" value="<?php echo $ok_antecipa['status']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $ok_antecipa['obs']; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formOkAntecipa').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar PagSeguro -->
<div class="modal fade" id="modalPagseguro" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar PagSeguro</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formPagseguro" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_pagseguro" value="1">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $pagseguro['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <input type="text" class="form-control" name="plano" id="plano" value="<?php echo $pagseguro['plano']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($pagseguro['status']) && $pagseguro['status'] == 'Pendente') ? 'selected' : (!isset($pagseguro['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($pagseguro['status']) && $pagseguro['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($pagseguro['status']) && $pagseguro['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($pagseguro['status']) && $pagseguro['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs"><?php echo $pagseguro['obs']; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formPagseguro').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Para confirmar a exclusão, digite <strong>"Excluir Cliente"</strong> no campo abaixo.</p>
                <form id="deleteForm" action="delete.php" method="POST">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="mb-3">
                        <label for="confirmationText" class="form-label">Confirmação</label>
                        <input
                            type="text"
                            class="form-control"
                            id="confirmationText"
                            name="confirmation_text"
                            placeholder="Digite: Excluir Cliente"
                            required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button
                    type="submit"
                    class="btn btn-danger"
                    form="deleteForm"
                    onclick="return validateConfirmation();">
                    Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para editar Sou Fácil -->
<div class="modal fade" id="modalsoufacil" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Sou Fácil</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSouFacil" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_soufacil" value="1">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($soufacil['status']) && $soufacil['status'] == 'Pendente') ? 'selected' : (!isset($soufacil['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($soufacil['status']) && $soufacil['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($soufacil['status']) && $soufacil['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($soufacil['status']) && $soufacil['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taxa_adm" class="form-label">Taxa Administrativa</label>
                        <input type="text" class="form-control" name="taxa_adm" id="taxa_adm" value="<?php echo $soufacil['taxa_adm'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="condicoes" class="form-label">Condições (absorver/repassar)</label>
                        <input type="text" class="form-control" name="condicoes" id="condicoes" value="<?php echo $soufacil['condicoes'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tipo_taxa" class="form-label">Tipo Taxa (Fixo ou Parcelado)</label>
                        <input type="text" class="form-control" name="tipo_taxa" id="tipo_taxa" value="<?php echo $soufacil['tipo_taxa'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="mensalidade" class="form-label">Mensalidade</label>
                        <input type="text" class="form-control" name="mensalidade" id="mensalidade" value="<?php echo $soufacil['mensalidade'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="taxa_antecipado" class="form-label">Taxa Antecipado</label>
                        <input type="text" class="form-control" name="taxa_antecipado" id="taxa_antecipado" value="<?php echo $soufacil['taxa_antecipado'] ?? ''; ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formSouFacil').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para editar Fliper -->
<div class="modal fade" id="modalfliper" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Fliper</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formFliper" action="update_prod.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_fliper" value="1">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $fliper['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" id="senha" value="<?php echo $fliper['senha'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="Pendente" <?php echo (isset($fliper['status']) && $fliper['status'] == 'Pendente') ? 'selected' : (!isset($fliper['status']) ? 'selected' : ''); ?>>Pendente</option>
                            <option value="Ativo" <?php echo (isset($fliper['status']) && $fliper['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="Inativo" <?php echo (isset($fliper['status']) && $fliper['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                            <option value="Cancelado" <?php echo (isset($fliper['status']) && $fliper['status'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taxa_adm" class="form-label">Taxa Administrativa</label>
                        <input type="text" class="form-control" name="taxa_adm" id="taxa_adm" value="<?php echo $fliper['taxa_adm'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="condicoes" class="form-label">Condições (absorver/repassar)</label>
                        <input type="text" class="form-control" name="condicoes" id="condicoes" value="<?php echo $fliper['condicoes'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="tipo_taxa" class="form-label">Tipo Taxa (Fixo ou Parcelado)</label>
                        <input type="text" class="form-control" name="tipo_taxa" id="tipo_taxa" value="<?php echo $fliper['tipo_taxa'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="mensalidade" class="form-label">Mensalidade</label>
                        <input type="text" class="form-control" name="mensalidade" id="mensalidade" value="<?php echo $fliper['mensalidade'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="taxa_antecipado" class="form-label">Taxa Antecipado</label>
                        <input type="text" class="form-control" name="taxa_antecipado" id="taxa_antecipado" value="<?php echo $fliper['taxa_antecipado'] ?? ''; ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formFliper').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<?php include 'modais_produtos_novos.php'; ?>
<?php include 'modal_status_soufacil.php'; ?>
<?php include 'modal_status_brasilcard.php'; ?>
<?php include 'modal_status_fgts.php'; ?>
<?php include 'modal_status_fliper.php'; ?>
<?php include 'modal_status_parcelex.php'; ?>
<?php include 'modal_documentos.php'; ?>


<!-- Modal para editar Parcela Fácil -->
<div class="modal fade" id="modalParcelaFacil" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Parcela Fácil</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formParcelaFacil" action="update_produtos_novos.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_parcela_facil" value="1">
                    <div class="mb-3">
                        <label for="plano" class="form-label">Plano</label>
                        <select class="form-select" name="plano" id="plano" required>
                            <option value="">Selecione uma opção</option>
                            <option value="Bronze R$ 149,00" <?php echo (isset($parcela_facil['plano']) && $parcela_facil['plano'] == 'Bronze R$ 149,00') ? 'selected' : ''; ?>>Bronze R$ 149,00</option>
                            <option value="Prata R$ 249,00" <?php echo (isset($parcela_facil['plano']) && $parcela_facil['plano'] == 'Prata R$ 249,00') ? 'selected' : ''; ?>>Prata R$ 249,00</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs" rows="3" placeholder="Observações sobre o plano"><?php echo $parcela_facil['obs'] ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formParcelaFacil').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar BoltCard -->
<div class="modal fade" id="modalBoltCard" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Editar BoltCard</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBoltCard" action="update_produtos_novos.php" method="post">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <input type="hidden" name="update_boltcard" value="1">
                    
                    <div class="mb-3">
                        <label for="plano" class="form-label">Qual plano você tem interesse? *</label>
                        <select class="form-select" name="plano" id="plano" required>
                            <option value="">Selecione uma opção</option>
                            <option value="Plano Classic (acima de R$ 40 mil)" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Plano Classic (acima de R$ 40 mil)') ? 'selected' : ''; ?>>Plano Classic (acima de R$ 40 mil)</option>
                            <option value="Plano Power (de R$ 20 mil a R$ 40 mil)" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Plano Power (de R$ 20 mil a R$ 40 mil)') ? 'selected' : ''; ?>>Plano Power (de R$ 20 mil a R$ 40 mil)</option>
                            <option value="Plano Platinum (até R$ 20 mil)" <?php echo (isset($boltcard['plano']) && $boltcard['plano'] == 'Plano Platinum (até R$ 20 mil)') ? 'selected' : ''; ?>>Plano Platinum (até R$ 20 mil)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modelo_maquininha" class="form-label">Qual maquininha você tem interesse? *</label>
                        <select class="form-select" name="modelo_maquininha" id="modelo_maquininha" required>
                            <option value="">Selecione uma opção</option>
                            <option value="Maquininha D195 (pequena)" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'Maquininha D195 (pequena)') ? 'selected' : ''; ?>>Maquininha D195 (pequena)</option>
                            <option value="Maquininha S920 (grande)" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'Maquininha S920 (grande)') ? 'selected' : ''; ?>>Maquininha S920 (grande)</option>
                            <option value="Maquininha Q92X (grande)" <?php echo (isset($boltcard['modelo_maquininha']) && $boltcard['modelo_maquininha'] == 'Maquininha Q92X (grande)') ? 'selected' : ''; ?>>Maquininha Q92X (grande)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="chip" class="form-label">Chip</label>
                        <select class="form-select" name="chip" id="chip">
                            <option value="">Selecione uma opção</option>
                            <option value="Claro" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'Claro') ? 'selected' : ''; ?>>Claro</option>
                            <option value="Vivo" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'Vivo') ? 'selected' : ''; ?>>Vivo</option>
                            <option value="TIM" <?php echo (isset($boltcard['chip']) && $boltcard['chip'] == 'TIM') ? 'selected' : ''; ?>>TIM</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valor_maquina" class="form-label">Valor da Máquina</label>
                        <input type="number" class="form-control" name="valor_maquina" id="valor_maquina" value="<?php echo $boltcard['valor_maquina'] ?? ''; ?>" step="0.01" placeholder="Valor da máquina">
                    </div>
                    
                    <div class="mb-3">
                        <label for="obs" class="form-label">Observações</label>
                        <textarea class="form-control" name="obs" id="obs" rows="3" placeholder="Observações adicionais"><?php echo $boltcard['obs'] ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('formBoltCard').submit();">Salvar alterações</button>
            </div>
        </div>
    </div>
</div>

<script>
    function validateConfirmation() {
        const confirmationText = document.getElementById('confirmationText').value;
        if (confirmationText !== "Excluir Cliente") {
            alert("Você deve digitar 'Excluir Cliente' para confirmar.");
            return false;
        }
        return true;
    }
</script>

<?php include '../includes/footer.php'; ?>