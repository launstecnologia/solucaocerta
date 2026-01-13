<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';
require_once 'login/session.php';
include 'includes/header.php';

// Query para obter os dados da produção Popular e CDC
$queryDados = "SELECT qtd_popular, fat_cdc FROM comissao_brasil_card ORDER BY id DESC LIMIT 1";
$resultDados = $conn->query($queryDados);

if ($resultDados && $resultDados->num_rows > 0) {
    $row = $resultDados->fetch_assoc();
    $popular = (int)$row['qtd_popular'];
    $cdcNumerico = (float)$row['fat_cdc'];
} else {
    $popular = 0;
    $cdcNumerico = 0.0;
}

// Query para obter o faturamento do Sou Fácil no mês atual
$queryFaturamentoSouFacil = "
    SELECT SUM(faturamento) AS total_faturamento
FROM fat_sou_facil
WHERE mes = MONTH(CURDATE())
  AND ano = YEAR(CURDATE());

";
$resultFaturamentoSouFacil = $conn->query($queryFaturamentoSouFacil);

if ($resultFaturamentoSouFacil && $resultFaturamentoSouFacil->num_rows > 0) {
    $row = $resultFaturamentoSouFacil->fetch_assoc();
    $faturamentoSouFacil = (float)$row['total_faturamento'];
} else {
    $faturamentoSouFacil = 0.0;
}

// Query para obter os dados do último mês disponível na tabela 'comissao_pag'
$queryCompetencia = "SELECT competencia, tpv, comissao, status FROM comissao_pag ORDER BY id DESC LIMIT 1";
$resultCompetencia = $conn->query($queryCompetencia);

if ($resultCompetencia && $resultCompetencia->num_rows > 0) {
    $row = $resultCompetencia->fetch_assoc();
    $competencia = $row['competencia'];
    $tpv = $row['tpv'];
    $comissaoTotal = $row['comissao'];
    $status = $row['status'];
} else {
    $competencia = "N/A";
    $tpv = "N/A";
    $comissaoTotal = "N/A";
    $status = "N/A";
}

// Calcular comissões
$comissaoPopular = $popular * 15;
$comissaoCdc = $cdcNumerico * 0.02;
$comissaoSouFacil = $faturamentoSouFacil * 0.02;

// Formatar valores
$comissaoPopularFormatted = number_format($comissaoPopular, 2, ',', '.');
$comissaoCdcFormatted = number_format($comissaoCdc, 2, ',', '.');
$faturamentoSouFacilFormatted = number_format($faturamentoSouFacil, 2, ',', '.');
$comissaoSouFacilFormatted = number_format($comissaoSouFacil, 2, ',', '.');
?>
<style>
    .card-custom {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        color: #333;
        transition: transform 0.2s ease;
    }

    .card-custom:hover {
        transform: translateY(-5px);
    }

    .card-header {
        font-size: 1.2rem;
        font-weight: bold;
        color: #555;
    }

    .card-value {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
    }

    .card-progress {
        display: flex;
        align-items: center;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Produção Popular -->
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-header">Produção Carteira - Popular</div>
                <div class="card-value"><?php echo $popular; ?> cartões</div>
                <div class="card-progress">
                    <small>Cartão Popular Brasil Card</small>
                </div>
            </div>
        </div>

        <!-- Produção Carteira - CDC -->
        <div class="col-md-4">
            <div class="card-custom" style="background-color: #f0f8ff;">
                <div class="card-header">Produção Carteira - CDC</div>
                <div class="card-value">R$ <?php echo number_format($cdcNumerico, 2, ',', '.'); ?></div>
                <div class="card-progress">
                    <small>Produção CDC Brasil Card</small>
                </div>
            </div>
        </div>

        <!-- TPV PagSeguro -->
        <div class="col-md-4">
            <div class="card-custom" style="background-color: #f9f0f0;">
                <div class="card-header">TPV - <?php echo $competencia; ?></div>
                <div class="card-value"><?php echo $tpv; ?></div>
                <div class="card-progress">
                    <small>Status: <?php echo $status; ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Comissão - Popular -->
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-header">Comissão - Popular</div>
                <div class="card-value">R$ <?php echo $comissaoPopularFormatted; ?></div>
                <div class="card-progress">
                    <small>Comissão Popular</small>
                </div>
            </div>
        </div>

        <!-- Comissão - CDC -->
        <div class="col-md-4">
            <div class="card-custom" style="background-color: #f0f8ff;">
                <div class="card-header">Comissão - CDC</div>
                <div class="card-value">R$ <?php echo $comissaoCdcFormatted; ?></div>
                <div class="card-progress">
                    <small>Comissão CDC</small>
                </div>
            </div>
        </div>

        <!-- Comissão Total -->
        <div class="col-md-4">
            <div class="card-custom" style="background-color: #f9f0f0;">
                <div class="card-header">Comissão Total</div>
                <div class="card-value"><?php echo $comissaoTotal; ?></div>
                <div class="card-progress">
                    <small>Status: <?php echo $status; ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Faturamento Sou Fácil -->
        <div class="col-md-4">
            <div class="card-custom" style="background-color: #f0f8ff;">
                <div class="card-header">Faturamento Sou Fácil</div>
                <div class="card-value">R$ <?php echo $faturamentoSouFacilFormatted; ?></div>
                <div class="card-progress">
                    <small>Total Faturamento</small>
                </div>
            </div>
        </div>

        <!-- Comissão Sou Fácil -->
        <div class="col-md-4">
            <div class="card-custom">
                <div class="card-header">Comissão Sou Fácil</div>
                <div class="card-value">R$ <?php echo $comissaoSouFacilFormatted; ?></div>
                <div class="card-progress">
                    <small>Comissão 2% do Faturamento</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
