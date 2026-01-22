<?php
session_start();
require_once '../config/config.php';

// Função helper para gerar URLs corretas de tickets
// Sempre retorna caminho relativo porque estamos dentro da pasta ticket/
function ticket_url($file) {
    return $file;
}

include '../includes/header.php';

// Verificar se veio com id_cliente pré-selecionado (vindo do detalhe do cliente)
$id_cliente_pre_selecionado = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;
$cliente_pre_selecionado = null;
if ($id_cliente_pre_selecionado > 0) {
    $sql_cliente = "SELECT id, nome_fantasia, cidade, uf FROM cliente WHERE id = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $id_cliente_pre_selecionado);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente_pre_selecionado = $result_cliente->fetch_assoc();
    $stmt_cliente->close();
}

// Obter a lista de status
$sql_status = "SELECT * FROM ticket_status";
$result_status = $conn->query($sql_status);
$statuses = [];
while ($row = $result_status->fetch_assoc()) {
    $statuses[] = $row;
}

// Obter lista de representantes para filtro
$sql_representantes = "SELECT id, nome FROM representante ORDER BY nome ASC";
$result_representantes = $conn->query($sql_representantes);
$representantes = [];
while ($row = $result_representantes->fetch_assoc()) {
    $representantes[] = $row;
}
?>

<style>
    .filtro-cliente {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .filtro-cliente .row {
        margin-bottom: 15px;
    }
    .resultado-cliente {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        display: none;
    }
    .cliente-item {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: background 0.2s;
    }
    .cliente-item:hover {
        background: #e9ecef;
    }
    .cliente-item:last-child {
        border-bottom: none;
    }
    .cliente-selecionado {
        background: #d1ecf1;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
    }
    .anexo-preview {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }
    .anexo-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px;
        background: white;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .anexo-item .remove-anexo {
        color: #dc3545;
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Criar Ticket</h5>
            <form action="<?php echo ticket_url('save_ticket.php'); ?>" method="post" enctype="multipart/form-data" id="formTicket">
                <!-- Seleção de Cliente com Filtros Avançados -->
                <div class="filtro-cliente">
                    <h6 class="mb-3">Buscar Cliente</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filtro_cnpj">CNPJ</label>
                            <input type="text" id="filtro_cnpj" class="form-control" placeholder="Digite o CNPJ">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_cpf">CPF</label>
                            <input type="text" id="filtro_cpf" class="form-control" placeholder="Digite o CPF">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_nome_fantasia">Nome Fantasia</label>
                            <input type="text" id="filtro_nome_fantasia" class="form-control" placeholder="Digite o nome">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_razao_social">Razão Social</label>
                            <input type="text" id="filtro_razao_social" class="form-control" placeholder="Digite a razão social">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label for="filtro_cidade">Cidade</label>
                            <input type="text" id="filtro_cidade" class="form-control" placeholder="Digite a cidade">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_representante">Representante</label>
                            <select id="filtro_representante" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach ($representantes as $rep): ?>
                                    <option value="<?php echo $rep['id']; ?>"><?php echo htmlspecialchars($rep['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_pdv">PDV</label>
                            <input type="text" id="filtro_pdv" class="form-control" placeholder="Digite o PDV">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_produto">Produto</label>
                            <select id="filtro_produto" class="form-control">
                                <option value="">Todos</option>
                                <option value="brasil_card">Brasil Card</option>
                                <option value="parcelex">Parcelex</option>
                                <option value="fgts">FGTS</option>
                                <option value="pagseguro">PagSeguro</option>
                                <option value="soufacil">Sou Fácil</option>
                                <option value="fliper">Fliper</option>
                                <option value="parcela_facil">Parcela Fácil</option>
                                <option value="boltcard">BoltCard</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="btnBuscarCliente">
                                <i class="fas fa-search me-1"></i> Buscar Cliente
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnLimparFiltros">
                                <i class="fas fa-eraser me-1"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                    <div id="resultadoCliente" class="resultado-cliente"></div>
                    <div id="clienteSelecionado" class="cliente-selecionado" style="<?php echo $cliente_pre_selecionado ? 'display: block;' : 'display: none;'; ?>">
                        <strong>Cliente Selecionado:</strong> <span id="nomeClienteSelecionado"><?php echo $cliente_pre_selecionado ? htmlspecialchars($cliente_pre_selecionado['nome_fantasia'] . ' - ' . $cliente_pre_selecionado['cidade'] . '/' . $cliente_pre_selecionado['uf']) : ''; ?></span>
                        <input type="hidden" name="id_cliente" id="id_cliente" value="<?php echo $cliente_pre_selecionado ? $cliente_pre_selecionado['id'] : ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" name="titulo" id="titulo" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição <span class="text-danger">*</span></label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="5" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_status">Status <span class="text-danger">*</span></label>
                            <select name="id_status" id="id_status" class="form-control" required>
                                <?php foreach ($statuses as $status) : ?>
                                    <option value="<?php echo $status['id']; ?>"><?php echo htmlspecialchars($status['status_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_retorno">Data e Hora de Retorno</label>
                            <input type="datetime-local" name="data_retorno" id="data_retorno" class="form-control">
                            <small class="form-text text-muted">Opcional - Você será notificado quando chegar esta data/hora</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="anexos">Anexos</label>
                    <input type="file" name="anexos[]" id="anexos" class="form-control" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <small class="form-text text-muted">Você pode selecionar múltiplos arquivos (máx. 10MB por arquivo)</small>
                    <div id="anexoPreview" class="anexo-preview"></div>
                </div>

                <br>
                <button type="submit" class="btn btn-primary" style="float: right;">
                    <i class="fas fa-save me-1"></i> Salvar Ticket
                </button>
                <a href="<?php echo ticket_url('index.php'); ?>" class="btn btn-secondary" style="float: right; margin-right: 10px;">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </form>
        </div>
    </div>
</div>

<script>
let anexosSelecionados = [];

// Preview de anexos
document.getElementById('anexos').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('anexoPreview');
    preview.innerHTML = '';
    
    files.forEach((file, index) => {
        if (file.size > 10 * 1024 * 1024) {
            alert(`O arquivo ${file.name} excede 10MB e será ignorado.`);
            return;
        }
        
        anexosSelecionados.push(file);
        const div = document.createElement('div');
        div.className = 'anexo-item';
        div.innerHTML = `
            <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
            <span class="remove-anexo" onclick="removerAnexo(${anexosSelecionados.length - 1})">✕</span>
        `;
        preview.appendChild(div);
    });
});

function removerAnexo(index) {
    anexosSelecionados.splice(index, 1);
    const input = document.getElementById('anexos');
    const dt = new DataTransfer();
    anexosSelecionados.forEach(file => dt.items.add(file));
    input.files = dt.files;
    
    // Atualizar preview
    const preview = document.getElementById('anexoPreview');
    preview.innerHTML = '';
    anexosSelecionados.forEach((file, idx) => {
        const div = document.createElement('div');
        div.className = 'anexo-item';
        div.innerHTML = `
            <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
            <span class="remove-anexo" onclick="removerAnexo(${idx})">✕</span>
        `;
        preview.appendChild(div);
    });
}

// Buscar cliente
document.getElementById('btnBuscarCliente').addEventListener('click', function() {
    const filtros = {
        cnpj: document.getElementById('filtro_cnpj').value.trim(),
        cpf: document.getElementById('filtro_cpf').value.trim(),
        nome_fantasia: document.getElementById('filtro_nome_fantasia').value.trim(),
        razao_social: document.getElementById('filtro_razao_social').value.trim(),
        cidade: document.getElementById('filtro_cidade').value.trim(),
        representante: document.getElementById('filtro_representante').value,
        pdv: document.getElementById('filtro_pdv').value.trim(),
        produto: document.getElementById('filtro_produto').value
    };
    
    // Verificar se pelo menos um filtro foi preenchido
    const temFiltro = Object.values(filtros).some(v => v !== '');
    if (!temFiltro) {
        alert('Preencha pelo menos um filtro para buscar.');
        return;
    }
    
    // Fazer requisição AJAX
    const ticketBase = window.location.pathname.includes('/ticket/') ? '' : 'ticket/';
    fetch(ticketBase + 'ajax_buscar_cliente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filtros)
    })
    .then(response => response.json())
    .then(data => {
        const resultado = document.getElementById('resultadoCliente');
        resultado.innerHTML = '';
        
        if (data.success && data.clientes.length > 0) {
            resultado.style.display = 'block';
            data.clientes.forEach(cliente => {
                const div = document.createElement('div');
                div.className = 'cliente-item';
                div.innerHTML = `<strong>${cliente.nome_fantasia}</strong> - ${cliente.cidade || 'N/A'}${cliente.uf ? '/' + cliente.uf : ''}`;
                div.onclick = () => selecionarCliente(cliente);
                resultado.appendChild(div);
            });
        } else {
            resultado.style.display = 'block';
            resultado.innerHTML = '<div class="text-center p-3">Nenhum cliente encontrado.</div>';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao buscar clientes.');
    });
});

function selecionarCliente(cliente) {
    document.getElementById('id_cliente').value = cliente.id;
    document.getElementById('nomeClienteSelecionado').textContent = cliente.nome_fantasia + ' - ' + (cliente.cidade || 'N/A');
    document.getElementById('clienteSelecionado').style.display = 'block';
    document.getElementById('resultadoCliente').style.display = 'none';
}

// Limpar filtros
document.getElementById('btnLimparFiltros').addEventListener('click', function() {
    document.getElementById('filtro_cnpj').value = '';
    document.getElementById('filtro_cpf').value = '';
    document.getElementById('filtro_nome_fantasia').value = '';
    document.getElementById('filtro_razao_social').value = '';
    document.getElementById('filtro_cidade').value = '';
    document.getElementById('filtro_representante').value = '';
    document.getElementById('filtro_pdv').value = '';
    document.getElementById('filtro_produto').value = '';
    document.getElementById('resultadoCliente').style.display = 'none';
    <?php if (!$cliente_pre_selecionado): ?>
    document.getElementById('clienteSelecionado').style.display = 'none';
    document.getElementById('id_cliente').value = '';
    <?php endif; ?>
});

// Se cliente já está pré-selecionado, ocultar a seção de busca
<?php if ($cliente_pre_selecionado): ?>
document.addEventListener('DOMContentLoaded', function() {
    const filtroSection = document.querySelector('.filtro-cliente');
    if (filtroSection) {
        filtroSection.style.display = 'none';
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
