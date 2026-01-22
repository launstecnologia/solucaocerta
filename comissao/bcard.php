<?php
require_once '../config/config.php';
include '../includes/header.php';

// Query para calcular comissões diretamente da tabela fat_brasil_card
// Agrupa por mês e ano, calcula somas e comissões
$sql = "SELECT 
    mes,
    ano,
    SUM(popular) as qtd_popular,
    SUM(popular) * 15 as comissao_popular,
    SUM(cdc) as fat_cdc,
    SUM(cdc) * 0.02 as comissao_cdc,
    (SUM(popular) * 15) + (SUM(cdc) * 0.02) as total_comissao
FROM fat_brasil_card 
GROUP BY mes, ano
ORDER BY ano DESC, mes DESC";

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
                    <?php 
                    while ($row = $result->fetch_assoc()) { 
                        // Formatar mês com zero à esquerda
                        $mesFormatado = str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
                        $competencia = $mesFormatado . '/' . $row['ano'];
                        
                        // Valores já calculados na query
                        $qtdPopular = (int)$row['qtd_popular'];
                        $comissaoPopular = (float)$row['comissao_popular'];
                        $fatCdc = (float)$row['fat_cdc'];
                        $comissaoCdc = (float)$row['comissao_cdc'];
                        $totalComissao = (float)$row['total_comissao'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($competencia); ?></td>
                            <td><?php echo htmlspecialchars($qtdPopular); ?></td>
                            <td><?php echo 'R$ ' . number_format($comissaoPopular, 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($fatCdc, 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($comissaoCdc, 2, ',', '.'); ?></td>
                            <td><?php echo 'R$ ' . number_format($totalComissao, 2, ',', '.'); ?></td>
                        </tr>
                    <?php 
                    }
                    
                    // Fechar recursos
                    if ($result) {
                        $result->close();
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
