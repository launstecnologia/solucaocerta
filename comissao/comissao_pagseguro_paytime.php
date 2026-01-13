<?php
require_once '../config/config.php';
include '../includes/header.php';

// Query para obter os dados da tabela comissao_pag
$sql = "SELECT competencia, tpv, markup, royalties, comissao, status FROM comissao_pag ORDER BY id DESC";
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
            <h5 class="card-title fw-semibold mb-4">Comissão PagSeguro (paytime)</h5>

            <!-- Tabela de Clientes -->
            <div class="table-responsive mt-3">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Competência</th>
                        <th>TPV</th>
                        <th>Markup</th>
                        <th>Royalties</th>
                        <th>Comissão</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['competencia']); ?></td>
                            <td><?php echo htmlspecialchars($row['tpv']); ?></td>
                            <td><?php echo htmlspecialchars($row['markup']); ?></td>
                            <td><?php echo htmlspecialchars($row['royalties']); ?></td>
                            <td><?php echo htmlspecialchars($row['comissao']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
