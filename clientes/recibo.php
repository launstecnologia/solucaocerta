<?php
require_once '../vendor/autoload.php'; // Autoload do Composer
require_once '../config/config.php';

$id_cliente = $_GET['id'];

// Buscar informações do cliente
$sql_cliente = "SELECT * FROM cliente WHERE id = ?";
$stmt_cliente = $conn->prepare($sql_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();
$cliente = $result_cliente->fetch_assoc();

// Extrair dados necessários do cliente
$valor_recebido = $cliente['valor'];
$valor_por_extenso = $cliente['valor_extenso'];
$data_emissao = date('d/m/Y', strtotime($cliente['data_register']));
$local = $cliente['cidade'] . '/' . $cliente['uf']; // Corrigido para concatenar

// Buscar o nome e CPF do representante associado ao cliente
$sql_representante = "SELECT r.nome, r.cpf FROM representante r 
JOIN cliente_representante cr ON r.id = cr.id_representante 
WHERE cr.id_cliente = ? LIMIT 1";
$stmt_representante = $conn->prepare($sql_representante);
$stmt_representante->bind_param("i", $id_cliente);
$stmt_representante->execute();
$result_representante = $stmt_representante->get_result();
$representante = $result_representante->fetch_assoc(); // Agora é um array com nome e cpf

class PDF extends FPDF
{
    function Header()
    {
        // Adicionar logo
        //$this->Image('../assets/images/logos/logo_brasil_card.png', 10, 10, 30);

        // Definir fonte
        $this->SetFont('Arial', 'B', 12);

        // Adicionar título do recibo
        $this->SetY(10); 
        $this->Cell(0, 30, $this->convertToIso('RECIBO'), 0, 0, 'C');
        $this->Ln(20);
    }

    function AddClientReceiptDetails($cliente, $valor_recebido, $valor_por_extenso, $data_emissao, $local, $representante)
    {
        // Definir fonte e adicionar detalhes do recibo
        $this->SetFont('Arial', '', 12);
        $this->Ln(10);
        $this->MultiCell(0, 8, $this->convertToIso(
            "Recebemos de {$cliente['razao_social']}, inscrito no CPF/CNPJ: {$cliente['cnpj']}, a quantia de R$ {$valor_recebido} ({$valor_por_extenso}), referente ao credenciamento e treinamento da plataforma de crédito.\n\nEste valor foi recebido integralmente e se refere exclusivamente ao serviço mencionado acima."
        ));

        // Adicionar data e local
        $this->Ln(10);
        $this->Cell(0, 8, $this->convertToIso("Data: {$data_emissao}"), 0, 1);
        $this->Cell(0, 8, $this->convertToIso("Local: {$local}"), 0, 1);

        // Assinatura
        $this->Ln(20);
        $this->Cell(0, 8, '______________________________', 0, 1, 'C');
        $this->Cell(0, 8, $this->convertToIso("Assinatura do Responsável"), 0, 1, 'C');
        $this->Cell(0, 8, $this->convertToIso($representante['nome']), 0, 1, 'C');
        $this->Cell(0, 8, $this->convertToIso($representante['cpf']), 0, 1, 'C'); // Agora é um array
    }

    function convertToIso($text)
    {
        return iconv('UTF-8', 'ISO-8859-1', $text);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Adicionar detalhes do cliente ao recibo
$pdf->AddClientReceiptDetails($cliente, $valor_recebido, $valor_por_extenso, $data_emissao, $local, $representante);

$pdf->Output('I', 'Recibo.pdf');
?>
