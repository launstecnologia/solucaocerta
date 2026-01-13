<?php
require_once '../vendor/autoload.php'; // Autoload do Composer
require_once '../config/config.php';

$id_cliente = $_GET['id'];

// Buscar informações do cliente
$sql_cliente = "SELECT 
    cliente.*,
    soufacil.status as soufacil_status,
    soufacil.taxa_adm,
    soufacil.condicoes,
    soufacil.tipo_taxa,
    soufacil.mensalidade,
    soufacil.taxa_antecipado
FROM cliente 
LEFT OUTER JOIN soufacil ON soufacil.id_cliente = cliente.id 
WHERE cliente.id = ?";
$stmt_cliente = $conn->prepare($sql_cliente);
$stmt_cliente->bind_param("i", $id_cliente);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();
$cliente = $result_cliente->fetch_assoc();


// Buscar representantes do cliente
$sql_representantes = "SELECT r.nome FROM representante r 
JOIN cliente_representante cr ON r.id = cr.id_representante 
WHERE cr.id_cliente = ?";
$stmt_representantes = $conn->prepare($sql_representantes);
$stmt_representantes->bind_param("i", $id_cliente);
$stmt_representantes->execute();
$result_representantes = $stmt_representantes->get_result();
$representantes = [];
while ($row = $result_representantes->fetch_assoc()) {
    $representantes[] = $row['nome'];
}


class PDF extends FPDF
{
    function Header()
    {
        // Adicionar logo à esquerda
        $this->Image('../assets/images/logos/soufacil.png', 10, 10, 30);

        // Definir fonte
        $this->SetFont('Arial', 'B', 12);

        // Posicionar cursor para o texto à direita
        $this->SetY(10);
        $this->SetX(-50);

        // Adicionar texto
        $this->Cell(40, 10, $this->convertToIso('Credenciamento Lojista'), 0, -10, 'R');

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

    function AddClientDetails($cliente, $representantes)
    {
        

        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Informações'), 0, 1);
        

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('CNPJ: ' . $cliente['cnpj']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Nome Social: ' . $cliente['razao_social']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Nome Fantasia: ' . $cliente['nome_fantasia']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Inscrição Estadual: ' . $cliente['insc_est']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Nome Responsável: ' . $cliente['adm_nome']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Telefone Gestor: ' . $cliente['telefone2']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('RG: ' . $cliente['adm_rg']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('CPF: ' . $cliente['adm_cpf']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Data de Nascimento: ' . $cliente['adm_data_nasc']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Estado Civil: ' . $cliente['adm_est_civil']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Nacionalidade: ' . $cliente['adm_nacionalidade']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('E-mail Comercial: ' . $cliente['email']), 0, 1);

        
        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Gerente'), 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Nome Gerente: ' . $cliente['ger_nome']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Telefone Gerente: ' . $cliente['ger_telefone1']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('E-mail Gerente: ' . ($cliente['ger_email'] ?? '')), 0, 1);

        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Financeiro'), 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Nome Financeiro: ' . $cliente['fin_nome']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Telefone Financeiro: ' . $cliente['fin_telefone1']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('E-mail Financeiro: ' . ($cliente['fin_email'] ?? '')), 0, 1);

        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Endereço Comercial'), 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Endereco: ' . $cliente['logradouro'] . ', ' . $cliente['numero'] . ' ' . $cliente['complemento']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Bairro: ' . $cliente['bairro']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Cidade: ' . $cliente['cidade']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('UF: ' . $cliente['uf']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('CEP: ' . $cliente['cep']), 0, 1);

        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Taxas e Condições'), 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Taxa Condições: ' . $cliente['taxa_adm']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Condições: ' . $cliente['condicoes']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Taxas: ' . $cliente['tipo_taxa']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Valor Mensalidade: ' . $cliente['mensalidade']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Taxa Antecipação: ' . $cliente['taxa_antecipado']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Adesão: ' . $cliente['valor']), 0, 1);


        $this->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
        $this->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
        $this->Cell(0, 6, $this->convertToIso('Dados Bancário'), 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, $this->convertToIso('Nome Conta: ' . $cliente['razao_social']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Agência: ' . $cliente['agencia']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Conta: ' . $cliente['conta']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Banco: ' . $cliente['banco']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Pix: ' . $cliente['pix']), 0, 1);
        $this->Cell(0, 6, $this->convertToIso('Favorecido: ' . $cliente['favorecido']), 0, 1);

        $this->Ln(5); // Adicionar espaçamento para Representantes
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, $this->convertToIso('Representantes:'), 0, 1);
        $this->SetFont('Arial', '', 12);

        if (!empty($representantes)) {
            foreach ($representantes as $representante) {
                $this->Cell(0, 6, $this->convertToIso('- ' . $representante), 0, 1);
            }
        } else {
            $this->Cell(0, 6, $this->convertToIso('Nenhum representante cadastrado.'), 0, 1);
        }
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
$pdf->AddClientDetails($cliente, $representantes);


$pdf->Output('I', 'credenciamento_lojista.pdf');
?>
