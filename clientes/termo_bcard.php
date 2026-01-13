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

class PDF extends FPDF
{
    function Header()
    {
        // Adicionar logo à esquerda
        $this->Image('../assets/images/logos/logo_brasil_card.png', 10, 10, 30);

        // Definir fonte
        $this->SetFont('Arial', 'B', 12);

        // Posicionar cursor para o texto à direita
        $this->SetY(10);
        $this->SetX(-50);

        // Adicionar texto
        $this->Cell(40, 10, $this->convertToIso('Termo de Adesao'), 0, -10, 'R');

        // Linha adicional para espaçamento
        $this->Ln(10);
    }

    function ChapterTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, $this->convertToIso($title), 0, 1);
        $this->Ln(4);
    }

    function ChapterBody($body)
    {
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 8, $this->convertToIso($body));
        $this->Ln();
    }

    function AddClientDetails($cliente)
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Nome: ' . $cliente['razao_social']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Email: ' . $cliente['email']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Telefone: ' . $cliente['telefone1']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('CNPJ: ' . $cliente['cnpj']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Razao Social: ' . $cliente['razao_social']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Nome Fantasia: ' . $cliente['nome_fantasia']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Endereco: ' . $cliente['logradouro'] . ', ' . $cliente['numero'] . ' ' . $cliente['complemento']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Bairro: ' . $cliente['bairro']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Cidade: ' . $cliente['cidade']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('UF: ' . $cliente['uf']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('CEP: ' . $cliente['cep']), 0, 1);
    }

    function convertToIso($text)
    {
        return iconv('UTF-8', 'ISO-8859-1', $text);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Título
$pdf->ChapterTitle('Dados do Cliente');

// Detalhes do Cliente
$pdf->AddClientDetails($cliente);

// Corpo do contrato com trechos específicos em negrito
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $pdf->convertToIso(
    "\nPelo presente instrumento particular, a empresa L.R. de Moraes Representações, inscrita no CNPJ sob o nº 19.945.360/0001-94, doravante denominada empresa CONTRATADA, credencia e disponibiliza desde já para a empresa CONTRATANTE, a implementação da Brasil Card Meios de Pagamentos LTDA.\n\n"
    . "CLÁUSULA 1 - ATIVAÇÃO E FORNECIMENTO DO SISTEMA\nA BRASIL CARD enviará à CONTRATANTE um link para aceite eletrônico do sistema de pagamento, no prazo máximo de sete (7) dias úteis a partir da assinatura deste termo. Após o aceite eletrônico pela CONTRATANTE, a BRASIL CARD disponibilizará o PDV e a senha para acesso no prazo acordado.\n\n"
    . "CLÁUSULA 2 - VIGÊNCIA E RESCISÃO\nEste termo entra em vigor a partir da data do aceite eletrônico pela CONTRATANTE e permanecerá em vigor até eventual rescisão ou modificação por ambas as partes. O contrato poderá ser rescindido (cancelado) a qualquer momento, por qualquer das partes, sem imposição de multa rescisória, assim como sem direito a reembolso do valor da taxa de implementação.\n\n"
    . "CLÁUSULA 3 - TAXAS E CUSTOS\n\n"
));

// Texto "Para o Lojista:" em negrito
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 8, $pdf->convertToIso("Para o Lojista:"));
$pdf->SetFont('Arial', '', 12); // Volta para o estilo normal
$pdf->MultiCell(0, 8, $pdf->convertToIso(
    "Taxa de Borderô: R$ 50,00 (cinquenta reais) por recebimento, aplicável a pagamentos realizados semanalmente ou D+2.\n"
    . "Utilização Web: R$ 29,90 (vinte e nove reais e noventa centavos), por mês.\n\n"
));

// Texto "Para o Consumidor:" em negrito
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 8, $pdf->convertToIso("Para o Consumidor:"));
$pdf->SetFont('Arial', '', 12); // Volta para o estilo normal
$pdf->MultiCell(0, 8, $pdf->convertToIso(
    "Utilização do Serviço: R$ 14,90 (quatorze reais e noventa centavos).\n"
    . "SMS: R$ 6,99 (Seis reais e noventa e nove centavos), sendo que o consumidor poderá cancelar este serviço a qualquer momento."
));

$pdf->Output('I', 'Termo_de_Adesao.pdf');
?>
