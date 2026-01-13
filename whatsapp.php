<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Gerenciamento de Conexões</h5>

            <!-- Botão para Atualizar Tabela de Conexões -->
            <button id="refreshConnections" class="btn btn-success mb-3">Atualizar Conexões</button>

            <!-- Tabela de Conexões -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nome da Instância</th>
                            <th>Estado</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="connectionsTable">
                        <tr>
                            <td colspan="3" class="text-center">Nenhuma conexão encontrada.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- QR Code e Status -->
            <div id="connectionResult" class="d-none">
                <h3>QR Code</h3>
                <div id="qrCodeContainer" class="text-center"></div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
    $(document).ready(function() {
        const instanceName = "solucaocerta";
        const token = "k334bvuk6t88wdxsyy8njl";

        // Atualiza a tabela de conexões
        function loadConnections() {
            $.ajax({
                url: `https://api.ovortex.tech/instance/fetchInstances`, // Endpoint correto
                method: 'GET',
                headers: {
                    "ApiKey": token,
                    "Content-Type": "application/json"
                },
                success: function(response) {
                    const connections = response.instances || []; // Adapte conforme o formato retornado
                    const tableBody = $('#connectionsTable');
                    tableBody.empty();

                    if (connections.length === 0) {
                        tableBody.append('<tr><td colspan="3" class="text-center">Nenhuma conexão encontrada.</td></tr>');
                    } else {
                        connections.forEach(instance => {
                            const state = instance.state; // Ajuste com base no campo real
                            const name = instance.name; // Ajuste com base no campo real
                            const actionButton = state === 'CONNECTED' ?
                                `<button class="btn btn-danger btn-sm disconnect-button" data-name="${name}">Desconectar</button>` :
                                `<button class="btn btn-primary btn-sm connect-button" data-name="${name}">Conectar</button>`;

                            tableBody.append(`
                        <tr>
                            <td>${name}</td>
                            <td>${state}</td>
                            <td>${actionButton}</td>
                        </tr>
                    `);
                        });
                    }
                },
                error: function(jqXHR) {
                    alert("Erro ao carregar conexões: " + jqXHR.responseText);
                }
            });
        }


        // Conecta uma instância
        function connectInstance(name) {
            $.ajax({
                url: `https://api.ovortex.tech/instance/connect/${name}`,
                method: 'GET',
                headers: {
                    "ApiKey": token,
                    "Content-Type": "application/json"
                },
                success: function(response) {
                    const qrCode = response.base64;
                    $('#qrCodeContainer').html(`<img src="${qrCode}" alt="QR Code" class="img-fluid">`);
                    $('#connectionResult').removeClass('d-none');
                    loadConnections();
                },
                error: function(jqXHR) {
                    alert("Erro ao conectar a instância: " + jqXHR.responseText);
                }
            });
        }

        // Desconecta uma instância
        function disconnectInstance(name) {
            $.ajax({
                url: `https://api.ovortex.tech/instance/disconnect/${name}`,
                method: 'GET',
                headers: {
                    "ApiKey": token,
                    "Content-Type": "application/json"
                },
                success: function() {
                    alert(`Instância ${name} desconectada com sucesso!`);
                    loadConnections();
                },
                error: function(jqXHR) {
                    alert("Erro ao desconectar a instância: " + jqXHR.responseText);
                }
            });
        }

        // Event listeners
        $(document).on('click', '.connect-button', function() {
            const name = $(this).data('name');
            connectInstance(name);
        });

        $(document).on('click', '.disconnect-button', function() {
            const name = $(this).data('name');
            disconnectInstance(name);
        });

        $('#refreshConnections').click(function() {
            loadConnections();
        });

        // Inicializa a tabela de conexões
        loadConnections();
    });
</script>