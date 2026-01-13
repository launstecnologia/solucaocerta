<?php
session_start();
require_once '../config/config.php';
include '../includes/header.php';

$usuarioId = $_SESSION['id'];
$nivel = $_SESSION['nivel'];

$statusQuery = "SELECT * FROM lead_status ORDER BY id ASC";
$statusResult = $conn->query($statusQuery);

$leadsByStatus = [];
$statusInfo = [];

while ($status = $statusResult->fetch_assoc()) {
    $statusId = $status['id'];
    $statusNome = $status['nome'];
    
    // Armazenar informações do status para uso posterior
    $statusInfo[$statusNome] = $status;

    if ($nivel == 'user') {
        if ($statusNome == 'novo') {
            $leadsQuery = "SELECT l.*, ls.nome as status_nome, u.nome as usuario_nome FROM lead l 
                          LEFT JOIN lead_status ls ON l.status_id = ls.id 
                          LEFT JOIN usuario u ON l.usuario_id = u.id
                          WHERE l.status_id = $statusId ORDER BY l.criado_em DESC";
        } else {
            $leadsQuery = "SELECT l.*, ls.nome as status_nome, u.nome as usuario_nome FROM lead l 
                          LEFT JOIN lead_status ls ON l.status_id = ls.id 
                          LEFT JOIN usuario u ON l.usuario_id = u.id
                          WHERE l.status_id = $statusId AND l.usuario_id = $usuarioId 
                          ORDER BY l.criado_em DESC";
        }
    } else {
        $leadsQuery = "SELECT l.*, ls.nome as status_nome, u.nome as usuario_nome FROM lead l 
                      LEFT JOIN lead_status ls ON l.status_id = ls.id 
                      LEFT JOIN usuario u ON l.usuario_id = u.id
                      WHERE l.status_id = $statusId ORDER BY l.criado_em DESC";
    }

    $leadsResult = $conn->query($leadsQuery);
    $leads = [];
    while ($lead = $leadsResult->fetch_assoc()) {
        $leads[] = $lead;
    }
    $leadsByStatus[$statusNome] = $leads;
}
?>

<style>
/* Dashboard Header */
.dashboard-header {
    background: white;
    padding: 30px 0;
    margin-bottom: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.dashboard-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
}

.dashboard-subtitle {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 30px;
}

/* Métricas Cards */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.metric-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.metric-icon.purple { background: linear-gradient(135deg, #667eea, #764ba2); }
.metric-icon.pink { background: linear-gradient(135deg, #f093fb, #f5576c); }
.metric-icon.blue { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.metric-icon.green { background: linear-gradient(135deg, #43e97b, #38f9d7); }

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
}

/* Search and Filters */
.search-filters-section {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.search-bar {
    position: relative;
    margin-bottom: 20px;
}

.search-bar input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-bar input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.search-bar i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1.1rem;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-btn.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.filter-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

/* Kanban Board */
.kanban-board {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.kanban-column {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.kanban-column.drag-over {
    background-color: #f8f9ff;
    border: 2px dashed #667eea;
    transform: scale(1.02);
    transition: all 0.3s ease;
}

.column-header {
    padding: 20px 25px;
    border-bottom: 3px solid #e9ecef;
    position: relative;
}

.column-header.blue { border-bottom-color: #4facfe; }
.column-header.purple { border-bottom-color: #667eea; }
.column-header.orange { border-bottom-color: #ff9a56; }
.column-header.red { border-bottom-color: #ff6b6b; }

.column-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.column-count {
    background: #f8f9fa;
    color: #6c757d;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.column-content {
    padding: 20px;
    min-height: 400px;
}

.add-card-btn {
    width: 100%;
    padding: 15px;
    border: 2px dashed #e9ecef;
    background: transparent;
    color: #6c757d;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.add-card-btn:hover {
    border-color: #667eea;
    color: #667eea;
    background: #f8f9ff;
}

/* Lead Cards */
.lead-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #e9ecef;
    cursor: grab;
    transition: all 0.3s ease;
    position: relative;
}

.lead-card.blue { border-left-color: #4facfe; }
.lead-card.purple { border-left-color: #667eea; }
.lead-card.orange { border-left-color: #ff9a56; }
.lead-card.red { border-left-color: #ff6b6b; }

.lead-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.lead-card.dragging {
    opacity: 0.7;
    transform: rotate(3deg) scale(0.95);
    cursor: grabbing;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.company-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.contact-person {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 10px;
}

.value-badge {
    background: #667eea;
    color: white;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    position: absolute;
    top: 15px;
    right: 15px;
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 10px 0;
}

.tag {
    background: #f8f9fa;
    color: #6c757d;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.time-indicator {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 8px 0;
}

.responsible-badge {
    width: 30px;
    height: 30px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    position: absolute;
    bottom: 15px;
    right: 15px;
}

/* Loading */
.loading-spinner {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    background: rgba(255, 255, 255, 0.95);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-title {
        font-size: 2rem;
    }
    
    .metrics-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .kanban-board {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .filter-buttons {
        justify-content: center;
    }
}
</style>

<div class="container-fluid mt-4">
    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">CRM Dashboard</h1>
            <p class="dashboard-subtitle">Gerencie seus leads e oportunidades de vendas</p>
            
            <!-- Métricas -->
            <div class="metrics-grid">
                <?php
                // Calcular métricas
                $totalLeads = 0;
                $emNegociacao = 0;
                $vendasFechadas = 0;
                $valorTotal = 0;
                
                foreach ($leadsByStatus as $status => $leads) {
                    $totalLeads += count($leads);
                    foreach ($leads as $lead) {
                        if (in_array($status, ['negociação', 'fechamento'])) {
                            $emNegociacao++;
                        }
                        if ($status == 'fechado') {
                            $vendasFechadas++;
                        }
                        $valorTotal += floatval($lead['valor_estimado'] ?? 0);
                    }
                }
                ?>
                
                <div class="metric-card">
                    <div class="metric-icon purple">
                        <i class="fas fa-users"></i>
                        </div>
                    <div class="metric-value"><?= $totalLeads ?></div>
                    <div class="metric-label">Total de Leads</div>
                    </div>
                
                <div class="metric-card">
                    <div class="metric-icon pink">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="metric-value"><?= $emNegociacao ?></div>
                    <div class="metric-label">Em Negociação</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon blue">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="metric-value"><?= $vendasFechadas ?></div>
                    <div class="metric-label">Vendas Fechadas</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon green">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="metric-value">R$ <?= number_format($valorTotal/1000, 0) ?>k</div>
                    <div class="metric-label">Valor Total</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-filters-section">
        <div class="container">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por empresa ou contato...">
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">Todos</button>
                <button class="filter-btn" data-filter="high">Alta Prioridade</button>
                <button class="filter-btn" data-filter="medium">Média</button>
                <button class="filter-btn" data-filter="low">Baixa</button>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        <?php 
        $columnColors = ['blue', 'purple', 'orange', 'red'];
        $colorIndex = 0;
        foreach ($leadsByStatus as $statusNome => $leads): 
            $colorClass = $columnColors[$colorIndex % count($columnColors)];
            $colorIndex++;
        ?>
            <div class="kanban-column">
                <div class="column-header <?= $colorClass ?>">
                    <div class="column-title"><?= htmlspecialchars($statusNome) ?></div>
                    <div class="column-count"><?= count($leads) ?></div>
                </div>
                
                <div class="column-content" 
                         data-status="<?= htmlspecialchars($statusNome) ?>"
                         data-status-id="<?= $statusInfo[$statusNome]['id'] ?>">
                    
                        <?php foreach ($leads as $lead): ?>
                        <?php
                        // Determinar informações do lead
                        $nomeEmpresa = $lead['razao_social'] ?: $lead['nome'];
                        $contato = $lead['nome'];
                        $valorFormatado = '';
                        if (!empty($lead['valor_estimado']) && $lead['valor_estimado'] > 0) {
                            $valorFormatado = 'R$ ' . number_format($lead['valor_estimado']/1000, 0) . 'k';
                        }
                        
                        // Tags baseadas no produto e origem
                        $tags = [];
                        if (!empty($lead['produto_id'])) {
                            $tags[] = ucfirst(str_replace('_', ' ', $lead['produto_id']));
                        }
                        if (!empty($lead['origem'])) {
                            $tags[] = $lead['origem'];
                        }
                        if (!empty($lead['segmento'])) {
                            $tags[] = $lead['segmento'];
                        }
                        
                        // Tempo desde criação
                        $dataCriacao = new DateTime($lead['criado_em']);
                        $agora = new DateTime();
                        $diferenca = $agora->diff($dataCriacao);
                        $tempoIndicador = '';
                        
                        if ($diferenca->days == 0) {
                            $tempoIndicador = 'Hoje';
                        } elseif ($diferenca->days == 1) {
                            $tempoIndicador = '1 dia';
                        } elseif ($diferenca->days < 7) {
                            $tempoIndicador = $diferenca->days . ' dias';
                        } else {
                            $tempoIndicador = '1 semana';
                        }
                        
                        // Iniciais do responsável
                        $iniciais = '';
                        if (!empty($lead['usuario_nome'])) {
                            $nomes = explode(' ', $lead['usuario_nome']);
                            $iniciais = strtoupper(substr($nomes[0], 0, 1));
                            if (count($nomes) > 1) {
                                $iniciais .= strtoupper(substr($nomes[1], 0, 1));
                            }
                        }
                        ?>
                        
                        <div class="lead-card <?= $colorClass ?>"
                                 draggable="true"
                                 data-lead-id="<?= $lead['id'] ?>"
                                 data-current-status="<?= $lead['status_id'] ?>"
                                 data-bs-toggle="modal"
                                 data-bs-target="#createLeadModal"
                                 data-id="<?= $lead['id'] ?>"
                                 data-nome="<?= htmlspecialchars($lead['nome']) ?>"
                                 data-whatsapp="<?= htmlspecialchars($lead['whatsapp']) ?>"
                                 data-email="<?= htmlspecialchars($lead['email']) ?>"
                                 data-cpf="<?= htmlspecialchars($lead['cpf']) ?>"
                                 data-obs="<?= htmlspecialchars($lead['obs']) ?>"
                                 data-status="<?= $lead['status_id'] ?>"
                                 data-usuario="<?= $lead['usuario_id'] ?>"
                             data-produto="<?= $lead['produto_id'] ?>"
                             data-origem="<?= htmlspecialchars($lead['origem'] ?? '') ?>"
                             data-probabilidade="<?= $lead['probabilidade'] ?? 0 ?>"
                             data-valor="<?= $lead['valor_estimado'] ?? 0 ?>">
                            
                            <div class="company-name"><?= htmlspecialchars($nomeEmpresa) ?></div>
                            <div class="contact-person"><?= htmlspecialchars($contato) ?></div>
                            
                            <?php if (!empty($valorFormatado)): ?>
                            <div class="value-badge"><?= $valorFormatado ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tags)): ?>
                            <div class="tags">
                                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                            <div class="time-indicator"><?= $tempoIndicador ?></div>
                            
                            <?php if (!empty($iniciais)): ?>
                            <div class="responsible-badge"><?= $iniciais ?></div>
                            <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    
                    <button class="add-card-btn" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                        + Adicionar Card
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal de criação/edição -->
<div class="modal fade" id="createLeadModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="function.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    <span id="modalTitle">Novo Lead</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="leadId">
                
                <!-- Abas de navegação -->
                <ul class="nav nav-tabs mb-4" id="leadTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>Dados do Cliente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="interacoes-tab" data-bs-toggle="tab" data-bs-target="#interacoes" type="button" role="tab">
                            <i class="fas fa-comments me-1"></i>Interações
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vendas-tab" data-bs-toggle="tab" data-bs-target="#vendas" type="button" role="tab">
                            <i class="fas fa-dollar-sign me-1"></i>Oportunidades
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="leadTabsContent">
                    <!-- Aba Dados do Cliente -->
                    <div class="tab-pane fade show active" id="dados" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-id-card me-1"></i>Informações Pessoais
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadNome" class="form-label">Nome Completo *</label>
                                    <input type="text" name="nome" id="leadNome" class="form-control" placeholder="Nome completo do cliente" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadCpf" class="form-label">CPF / CNPJ</label>
                                    <input type="text" name="cpf" id="leadCpf" class="form-control" placeholder="000.000.000-00 ou 00.000.000/0000-00">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadRazaoSocial" class="form-label">Razão Social / Nome Fantasia</label>
                                    <input type="text" name="razao_social" id="leadRazaoSocial" class="form-control" placeholder="Nome da empresa ou nome fantasia">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadEmail" class="form-label">E-mail</label>
                                    <input type="email" name="email" id="leadEmail" class="form-control" placeholder="email@exemplo.com">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadWhatsapp" class="form-label">Telefone / WhatsApp *</label>
                                    <input type="text" name="whatsapp" id="leadWhatsapp" class="form-control" placeholder="(00) 00000-0000" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt me-1"></i>Endereço Completo
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadEndereco" class="form-label">Rua</label>
                                    <input type="text" name="endereco" id="leadEndereco" class="form-control" placeholder="Nome da rua">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="leadNumero" class="form-label">Número</label>
                                            <input type="text" name="numero" id="leadNumero" class="form-control" placeholder="123">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="leadComplemento" class="form-label">Complemento</label>
                                            <input type="text" name="complemento" id="leadComplemento" class="form-control" placeholder="Apto, sala, etc.">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadBairro" class="form-label">Bairro</label>
                                    <input type="text" name="bairro" id="leadBairro" class="form-control" placeholder="Nome do bairro">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="leadCidade" class="form-label">Cidade</label>
                                            <input type="text" name="cidade" id="leadCidade" class="form-control" placeholder="Nome da cidade">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="leadEstado" class="form-label">Estado</label>
                                            <input type="text" name="estado" id="leadEstado" class="form-control" placeholder="UF" maxlength="2">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadCep" class="form-label">CEP</label>
                                    <input type="text" name="cep" id="leadCep" class="form-control" placeholder="00000-000">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadOrigem" class="form-label">Origem do Lead</label>
                                    <select name="origem" id="leadOrigem" class="form-select">
                                        <option value="">Selecione a origem</option>
                                        <option value="Site">Site</option>
                                        <option value="Redes Sociais">Redes Sociais</option>
                                        <option value="Indicação">Indicação</option>
                                        <option value="Campanha">Campanha</option>
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="Telefone">Telefone</option>
                                        <option value="E-mail">E-mail</option>
                                        <option value="Outros">Outros</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadCargo" class="form-label">Cargo / Função</label>
                                    <input type="text" name="cargo" id="leadCargo" class="form-control" placeholder="Cargo na empresa">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadSegmento" class="form-label">Segmento / Nicho de Mercado</label>
                                    <input type="text" name="segmento" id="leadSegmento" class="form-control" placeholder="Ex: Varejo, Serviços, Indústria">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aba Interações -->
                    <div class="tab-pane fade" id="interacoes" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-clock me-1"></i>Datas de Contato
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadPrimeiroContato" class="form-label">Data do Primeiro Contato</label>
                                    <input type="datetime-local" name="primeiro_contato" id="leadPrimeiroContato" class="form-control">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadUltimoContato" class="form-label">Último Contato</label>
                                    <input type="datetime-local" name="ultimo_contato" id="leadUltimoContato" class="form-control">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadCanalComunicacao" class="form-label">Canal de Comunicação</label>
                                    <select name="canal_comunicacao" id="leadCanalComunicacao" class="form-select">
                                        <option value="">Selecione o canal</option>
                                        <option value="WhatsApp">WhatsApp</option>
                                        <option value="E-mail">E-mail</option>
                                        <option value="Telefone">Telefone</option>
                                        <option value="Reunião Presencial">Reunião Presencial</option>
                                        <option value="Reunião Online">Reunião Online</option>
                                        <option value="SMS">SMS</option>
                </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadResponsavel" class="form-label">Responsável pelo Atendimento</label>
                                    <select name="usuario_id" id="leadUsuario" class="form-select">
                    <?php
                    $res = $conn->query("SELECT id, nome FROM usuario WHERE status = 'ativo'");
                    while ($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                    }
                    ?>
                </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-sticky-note me-1"></i>Histórico e Observações
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadObs" class="form-label">Histórico de Conversas / Observações</label>
                                    <textarea name="obs" id="leadObs" class="form-control" rows="4" placeholder="Registre aqui o histórico de conversas e observações importantes"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadAgendamento" class="form-label">Próximo Agendamento / Follow-up</label>
                                    <input type="datetime-local" name="agendamento" id="leadAgendamento" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Aba Oportunidades -->
                    <div class="tab-pane fade" id="vendas" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-chart-line me-1"></i>Status e Etapas
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadStatus" class="form-label">Status do Lead</label>
                                    <select name="status_id" id="leadStatus" class="form-select">
                    <?php
                                        $res = $conn->query("SELECT * FROM lead_status");
                    while ($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                    }
                    ?>
                </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadEtapaFunil" class="form-label">Etapa do Funil</label>
                                    <select name="etapa_funil" id="leadEtapaFunil" class="form-select">
                                        <option value="">Selecione a etapa</option>
                                        <option value="Prospect">Prospect</option>
                                        <option value="Qualificado">Qualificado</option>
                                        <option value="Proposta Enviada">Proposta Enviada</option>
                                        <option value="Negociação">Negociação</option>
                                        <option value="Fechamento">Fechamento</option>
                                        <option value="Fechado">Fechado</option>
                                        <option value="Perdido">Perdido</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadProduto" class="form-label">Produto / Serviço de Interesse</label>
                                    <select name="produto_id" id="leadProduto" class="form-select">
                                        <option value="">Selecione o produto</option>
                    <?php
                                        // Buscar produtos do banco de dados
                                        $produtos = [
                                            'brasil_card' => 'Brasil Card',
                                            'fgts' => 'FGTS',
                                            'pagseguro' => 'PagSeguro',
                                            'soufacil' => 'Sou Fácil',
                                            'fliper' => 'Fliper',
                                            'parcela_facil' => 'Parcela Fácil',
                                            'boltcard' => 'BoltCard'
                                        ];
                                        
                                        foreach ($produtos as $key => $nome) {
                                            echo "<option value='{$key}'>{$nome}</option>";
                    }
                    ?>
                </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadValorEstimado" class="form-label">Valor Estimado da Venda</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" name="valor_estimado" id="leadValorEstimado" class="form-control" step="0.01" placeholder="0,00">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-percentage me-1"></i>Probabilidade e Datas
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="leadProbabilidade" class="form-label">Probabilidade de Fechamento (%)</label>
                                    <input type="range" name="probabilidade" id="leadProbabilidade" class="form-range" min="0" max="100" value="0">
                                    <div class="d-flex justify-content-between">
                                        <small>0%</small>
                                        <small id="probabilidadeValue">0%</small>
                                        <small>100%</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadDataFechamento" class="form-label">Data de Fechamento Prevista</label>
                                    <input type="date" name="data_fechamento" id="leadDataFechamento" class="form-control">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadCondicoesComerciais" class="form-label">Condições Comerciais / Observações</label>
                                    <textarea name="condicoes_comerciais" id="leadCondicoesComerciais" class="form-control" rows="3" placeholder="Condições especiais, descontos, formas de pagamento, etc."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="leadMotivoPerda" class="form-label">Motivo da Perda (se aplicável)</label>
                                    <select name="motivo_perda" id="leadMotivoPerda" class="form-select">
                                        <option value="">Selecione o motivo</option>
                                        <option value="Preço Alto">Preço Alto</option>
                                        <option value="Concorrência">Concorrência</option>
                                        <option value="Não Necessita">Não Necessita</option>
                                        <option value="Sem Orçamento">Sem Orçamento</option>
                                        <option value="Timing Inadequado">Timing Inadequado</option>
                                        <option value="Outros">Outros</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="convertToClientBtn" style="display: none;">
                    <i class="fas fa-user-check me-1"></i>Converter para Cliente
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Salvar Lead
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let draggedElement = null;
    let isDragging = false;

    console.log('Kanban inicializado'); // Debug

    // Edit Modal - Ajustado para não abrir durante drag
    const editModal = document.getElementById('createLeadModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            if (isDragging) {
                event.preventDefault();
                return false;
            }
            
            const trigger = event.relatedTarget;
            if (!trigger || !trigger.classList.contains('lead-card')) return;

            // Preencher campos do modal
            const fields = ['id', 'nome', 'whatsapp', 'email', 'cpf', 'obs', 'status', 'usuario', 'produto'];
            fields.forEach(field => {
                const element = document.getElementById('lead' + field.charAt(0).toUpperCase() + field.slice(1));
                if (element && trigger.dataset[field]) {
                    element.value = trigger.dataset[field];
                }
            });
            
            // Atualizar título do modal
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) {
                modalTitle.textContent = trigger.dataset.id ? 'Editar Lead' : 'Novo Lead';
            }
            
            // Mostrar botão de conversão se o lead estiver em etapa de fechamento
            const convertBtn = document.getElementById('convertToClientBtn');
            if (convertBtn) {
                const etapaFunil = document.getElementById('leadEtapaFunil');
                if (etapaFunil && etapaFunil.value === 'Fechado') {
                    convertBtn.style.display = 'inline-block';
                } else {
                    convertBtn.style.display = 'none';
                }
            }
        });
    }

    // Inicializar Drag and Drop
    initializeDragAndDrop();

    function initializeDragAndDrop() {
        const leadCards = document.querySelectorAll('.lead-card');
        const kanbanColumns = document.querySelectorAll('.column-content');

        console.log('Cards encontrados:', leadCards.length); // Debug
        console.log('Colunas encontradas:', kanbanColumns.length); // Debug

        // Configurar eventos de drag para cada card
        leadCards.forEach((card, index) => {
            console.log(`Configurando card ${index}:`, card.dataset.leadId); // Debug
            
            card.addEventListener('dragstart', function(e) {
                console.log('Drag start:', this.dataset.leadId); // Debug
                draggedElement = this;
                isDragging = true;
                this.classList.add('dragging');
                
                // Definir dados para transferência
                e.dataTransfer.setData('text/plain', this.dataset.leadId);
                e.dataTransfer.effectAllowed = 'move';
            });

            card.addEventListener('dragend', function(e) {
                console.log('Drag end'); // Debug
                this.classList.remove('dragging');
                
                // Reset após delay para evitar conflito com modal
                setTimeout(() => {
                    draggedElement = null;
                    isDragging = false;
                }, 200);
            });

            // Prevenir modal ao clicar no ícone de arrastar
            const dragHandle = card.querySelector('.drag-handle');
            if (dragHandle) {
                dragHandle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                });
            }
        });

        // Configurar zonas de drop
        kanbanColumns.forEach((column, index) => {
            console.log(`Configurando coluna ${index}:`, column.dataset.statusId); // Debug
            
            column.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
            });

            column.addEventListener('dragleave', function(e) {
                // Só remove a classe se realmente saiu da coluna
                if (!this.contains(e.relatedTarget)) {
                    this.classList.remove('drag-over');
                }
            });

            column.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                console.log('Drop detectado'); // Debug

                if (draggedElement) {
                    const newStatusId = this.dataset.statusId;
                    const leadId = draggedElement.dataset.leadId;
                    const currentStatusId = draggedElement.dataset.currentStatus;

                    console.log('Movendo lead:', leadId, 'de', currentStatusId, 'para', newStatusId); // Debug

                    // Só atualiza se o status realmente mudou
                    if (newStatusId !== currentStatusId) {
                        // Mostrar loading
                        showLoading(true);

                        // Atualizar status via AJAX
                        updateLeadStatus(leadId, newStatusId)
                            .then(success => {
                                showLoading(false);
                                
                                if (success) {
                                    // Mover o card para nova coluna (antes do botão "Adicionar Card")
                                    const addButton = this.querySelector('.add-card-btn');
                                    if (addButton) {
                                        this.insertBefore(draggedElement, addButton);
                                    } else {
                                    this.appendChild(draggedElement);
                                    }
                                    draggedElement.dataset.currentStatus = newStatusId;
                                    draggedElement.dataset.status = newStatusId;
                                    
                                    // Atualizar contadores
                                    updateBadgeCounts();
                                    
                                    // Mostrar sucesso
                                    showNotification('Lead movido com sucesso!', 'success');
                                } else {
                                    showNotification('Erro ao mover lead. Tente novamente.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Erro AJAX:', error);
                                showLoading(false);
                                showNotification('Erro de conexão. Tente novamente.', 'error');
                            });
                    } else {
                        console.log('Status não mudou, ignorando'); // Debug
                    }
                }
            });
        });
    }

    // Função para atualizar status via AJAX
    function updateLeadStatus(leadId, newStatusId) {
        console.log('Enviando AJAX para atualizar status'); // Debug
        
        return fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: parseInt(leadId),
                status_id: parseInt(newStatusId)
            })
        })
        .then(response => {
            console.log('Resposta recebida:', response.status); // Debug
            if (!response.ok) {
                throw new Error('Erro HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados da resposta:', data); // Debug
            return data.success === true;
        });
    }

    // Função para mostrar/esconder loading
    function showLoading(show) {
        const spinner = document.querySelector('.loading-spinner');
        if (spinner) {
            spinner.style.display = show ? 'block' : 'none';
        }
    }

    // Função para atualizar badges de contagem
    function updateBadgeCounts() {
        const kanbanColumns = document.querySelectorAll('.column-content');
        kanbanColumns.forEach(column => {
            const cards = column.querySelectorAll('.lead-card:not([style*="display: none"])');
            const badge = column.closest('.kanban-column').querySelector('.column-count');
            if (badge) {
                badge.textContent = cards.length;
            }
        });
    }

    // Função para mostrar notificações
    function showNotification(message, type) {
        // Remove notificações anteriores
        const existingNotifications = document.querySelectorAll('.kanban-notification');
        existingNotifications.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed kanban-notification`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; opacity: 0.95; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        notification.innerHTML = `
            <strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}
            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover após 4 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 4000);
    }
    
    // Controle da probabilidade
    const probabilidadeSlider = document.getElementById('leadProbabilidade');
    const probabilidadeValue = document.getElementById('probabilidadeValue');
    
    if (probabilidadeSlider && probabilidadeValue) {
        probabilidadeSlider.addEventListener('input', function() {
            probabilidadeValue.textContent = this.value + '%';
        });
    }
    
    // Funcionalidade de conversão para cliente
    const convertBtn = document.getElementById('convertToClientBtn');
    if (convertBtn) {
        convertBtn.addEventListener('click', function() {
            const leadId = document.getElementById('leadId').value;
            const nome = document.getElementById('leadNome').value;
            const whatsapp = document.getElementById('leadWhatsapp').value;
            const email = document.getElementById('leadEmail').value;
            const cpf = document.getElementById('leadCpf').value;
            const produto = document.getElementById('leadProduto').value;
            
            if (!nome || !whatsapp) {
                showNotification('Nome e WhatsApp são obrigatórios para conversão', 'error');
                return;
            }
            
            if (confirm('Deseja converter este lead em cliente? Esta ação criará um novo registro na base de clientes.')) {
                // Redirecionar para página de criação de cliente com dados pré-preenchidos
                const params = new URLSearchParams({
                    nome: nome,
                    whatsapp: whatsapp,
                    email: email,
                    cpf: cpf,
                    produto: produto,
                    origem: 'Lead Convertido'
                });
                
                window.location.href = `../clientes/create.php?${params.toString()}`;
            }
        });
    }
    
    // Funcionalidade de filtros e busca
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Função para filtrar leads
    function filterLeads() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;
        
        const leadCards = document.querySelectorAll('.lead-card');
        
        leadCards.forEach(card => {
            let showCard = true;
            
            // Filtro de busca
            if (searchTerm) {
                const nome = card.dataset.nome.toLowerCase();
                const whatsapp = card.dataset.whatsapp.toLowerCase();
                const email = card.dataset.email.toLowerCase();
                
                if (!nome.includes(searchTerm) && !whatsapp.includes(searchTerm) && !email.includes(searchTerm)) {
                    showCard = false;
                }
            }
            
            // Filtro de prioridade
            if (activeFilter !== 'all') {
                const probabilidade = parseInt(card.dataset.probabilidade) || 0;
                let cardPriority = '';
                
                if (probabilidade >= 80) {
                    cardPriority = 'high';
                } else if (probabilidade >= 50) {
                    cardPriority = 'medium';
                } else {
                    cardPriority = 'low';
                }
                
                if (cardPriority !== activeFilter) {
                    showCard = false;
                }
            }
            
            // Mostrar/ocultar card
            card.style.display = showCard ? 'block' : 'none';
        });
        
        // Atualizar contadores
        updateBadgeCounts();
    }
    
    // Event listeners para filtros
    if (searchInput) {
        searchInput.addEventListener('input', filterLeads);
    }
    
    // Event listeners para botões de filtro
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remover classe active de todos os botões
            filterButtons.forEach(b => b.classList.remove('active'));
            // Adicionar classe active ao botão clicado
            this.classList.add('active');
            // Aplicar filtro
            filterLeads();
        });
    });
});
</script>