<?php
require_once '../config/config.php';
include '../includes/header.php';

// Query para obter os dados da tabela comissao_brasil_card
$sql = "SELECT * FROM comissao_brasil_card ORDER BY id DESC";
$stmt = $conn->prepare($sql);

// Verifica se a consulta foi preparada corretamente
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
            <h5 class="card-title fw-semibold mb-4">Comissão Brasil Card</h5>

            <!-- Tabela de Clientes -->
            <div class="table-responsive mt-3">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Competência</th>
                        <th>Qtd Popular</th>
                        <th>Comissão Popular</th>
                        <th>Fat. CDC</th>
                        <th>Comissão CDC</th>
                        <th>Total Comissão</th> <!-- Nova Coluna -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { 
                        // Calcula o total da comissão
                        $totalComissao = $row['comissao_popular'] + $row['comissao_cdc'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['mes_competencia']) . '/' . htmlspecialchars($row['ano_competencia']); ?></td>
                            <td><?php echo htmlspecialchars($row['qtd_popular']); ?></td>
                            <td><?php echo 'R$ ' . number_format($row['comissao_popular'], 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($row['fat_cdc'], 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($row['comissao_cdc'], 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($totalComissao, 2, ',', '.'); ?></td> <!-- Exibe o Total Formatado -->
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
