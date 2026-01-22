<?php
// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';
require_once 'login/session.php';
include 'includes/header.php';

// Obter mês e ano atual
$mesAtual = (int)date('m');
$anoAtual = (int)date('Y');

// Query para obter o faturamento CDC do mês atual da tabela fat_brasil_card
// Soma todos os valores da coluna CDC (que já é numérica)
$queryFaturamento = "SELECT 
    COALESCE(SUM(cdc), 0) as total_cdc
FROM fat_brasil_card 
WHERE mes = ? AND ano = ?";

$stmtFaturamento = $conn->prepare($queryFaturamento);
$stmtFaturamento->bind_param("ii", $mesAtual, $anoAtual);
$stmtFaturamento->execute();
$resultFaturamento = $stmtFaturamento->get_result();

if ($resultFaturamento && $resultFaturamento->num_rows > 0) {
    $row = $resultFaturamento->fetch_assoc();
    $cdcNumerico = (float)($row['total_cdc'] ?? 0);
} else {
    $cdcNumerico = 0.0;
}

if ($resultFaturamento) {
    $resultFaturamento->close();
}
$stmtFaturamento->close();

// ========== SOU FÁCIL ==========
$querySouFacil = "SELECT 
    COALESCE(SUM(faturamento), 0) as total_faturamento
FROM fat_sou_facil 
WHERE mes = ? AND ano = ?";

$stmtSouFacil = $conn->prepare($querySouFacil);
$stmtSouFacil->bind_param("ii", $mesAtual, $anoAtual);
$stmtSouFacil->execute();
$resultSouFacil = $stmtSouFacil->get_result();

if ($resultSouFacil && $resultSouFacil->num_rows > 0) {
    $row = $resultSouFacil->fetch_assoc();
    $souFacilFaturamento = (float)($row['total_faturamento'] ?? 0);
} else {
    $souFacilFaturamento = 0.0;
}

if ($resultSouFacil) {
    $resultSouFacil->close();
}
$stmtSouFacil->close();

// ========== PARCELEX ==========
// Verificar se existe tabela de faturamento Parcelex
$parcelexFaturamento = 0.0;
// Se houver tabela fat_parcelex no futuro, adicionar query aqui

// ========== PAYTIME (PAGBANK) ==========
$queryPaytime = "SELECT 
    tpv, comissao
FROM comissao_pag 
WHERE competencia LIKE ?
ORDER BY id DESC
LIMIT 1";

$competenciaAtual = str_pad($mesAtual, 2, '0', STR_PAD_LEFT) . '/' . $anoAtual;
$stmtPaytime = $conn->prepare($queryPaytime);
$stmtPaytime->bind_param("s", $competenciaAtual);
$stmtPaytime->execute();
$resultPaytime = $stmtPaytime->get_result();

$paytimeFaturamento = 0.0;

if ($resultPaytime && $resultPaytime->num_rows > 0) {
    $row = $resultPaytime->fetch_assoc();
    // TPV pode estar formatado como "R$ X.XXX,XX", precisa converter
    $tpv_str = $row['tpv'] ?? '0';
    $tpv_str = str_replace(['R$', ' ', '.'], '', $tpv_str);
    $tpv_str = str_replace(',', '.', $tpv_str);
    $paytimeFaturamento = (float)$tpv_str;
} else {
    $paytimeFaturamento = 0.0;
}

if ($resultPaytime) {
    $resultPaytime->close();
}
$stmtPaytime->close();

// Formatar valores
$cdcFormatted = number_format($cdcNumerico, 2, ',', '.');
$souFacilFormatted = number_format($souFacilFaturamento, 2, ',', '.');
$parcelexFormatted = number_format($parcelexFaturamento, 2, ',', '.');
$paytimeFormatted = number_format($paytimeFaturamento, 2, ',', '.');
?>
<style>
    .card-custom {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 30px;
        color: #333;
        transition: transform 0.2s ease;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .card-custom:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        font-size: 1.1rem;
        font-weight: bold;
        color: #555;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-icon {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }

    .card-value {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin: 15px 0;
    }

    .card-progress {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Cards de Faturamento - 4 por linha -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card-custom" style="background-color: #f0f8ff;">
                <div class="card-header">
                    <img src="assets/images/logos/icone_brasilcard.png" alt="Brasil Card" class="card-icon">
                    <span>Brasil Card</span>
                </div>
                <div class="card-value">R$ <?php echo $cdcFormatted; ?></div>
                <div class="card-progress">
                    <small>Faturamento CDC Brasil Card</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom" style="background-color: #fff3e0;">
                <div class="card-header">
                    <img src="assets/images/logos/icon_soufacil.png" alt="Sou Fácil" class="card-icon">
                    <span>Sou Fácil</span>
                </div>
                <div class="card-value">R$ <?php echo $souFacilFormatted; ?></div>
                <div class="card-progress">
                    <small>Faturamento Sou Fácil</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom" style="background-color: #e1f5fe;">
                <div class="card-header">
                    <img src="assets/images/logos/parcelex.svg" alt="Parcelex" class="card-icon">
                    <span>Parcelex</span>
                </div>
                <div class="card-value">R$ <?php echo $parcelexFormatted; ?></div>
                <div class="card-progress">
                    <small>Faturamento Parcelex</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-custom" style="background-color: #fce4ec;">
                <div class="card-header">
                    <i class="fas fa-credit-card" style="font-size: 24px; color: #6c757d;"></i>
                    <span>Paytime (PagBank)</span>
                </div>
                <div class="card-value">R$ <?php echo $paytimeFormatted; ?></div>
                <div class="card-progress">
                    <small>TPV Paytime (PagBank)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>
