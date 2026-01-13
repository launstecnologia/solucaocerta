<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../login/session.php';
include '../includes/header.php';

// Query para calcular o total de adesão agrupado por mês e ano, com o nome do representante
$queryDados = "
    SELECT 
        MONTH(cliente.data_register) AS mes,
        YEAR(cliente.data_register) AS ano,
        representante.nome AS representante_nome,
        SUM(cliente.valor) AS total_valor
    FROM 
        cliente
    LEFT OUTER JOIN 
        cliente_representante 
    ON 
        cliente_representante.id_cliente = cliente.id
    LEFT OUTER JOIN 
        representante 
    ON 
        cliente_representante.id_representante = representante.id
    WHERE 
        YEAR(cliente.data_register) >= 2025
    GROUP BY 
        YEAR(cliente.data_register), MONTH(cliente.data_register), representante.nome
    ORDER BY 
        ano DESC, mes DESC
";

$stmt = $conn->prepare($queryDados);

if ($stmt) {
    $stmt->execute(); // Executa a consulta
    $result = $stmt->get_result(); // Obtém o resultado da consulta
} else {
    die("Erro ao preparar a consulta: " . $conn->error);
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Adesão - Valor Total por Mês, Ano e Representante</h5>

            <!-- Tabela de Dados -->
            <div class="table-responsive mt-3">
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Ano</th>
                            <th>Representante</th>
                            <th>Valor Total da Adesão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo str_pad($row['mes'], 2, '0', STR_PAD_LEFT); // Exibe o mês com 2 dígitos ?></td>
                                <td><?php echo htmlspecialchars($row['ano']); ?></td>
                                <td><?php echo htmlspecialchars($row['representante_nome']); ?></td>
                                <td><?php echo number_format($row['total_valor'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
