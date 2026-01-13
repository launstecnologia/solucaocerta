<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php'; // Para a biblioteca FPDF

$id_cliente = $_GET['id'];
$produto = $_GET['produto']; // Neste caso, 'soufacil'

// Configuração do diretório de uploads
$upload_dir = "../uploads/documentos/$produto/";
$zip_file = "../uploads/documentos/$produto-$id_cliente.zip";

// Buscar documentos relacionados ao produto
$stmt = $conn->prepare("
    SELECT d.caminho_arquivo, d.tipo_documento 
    FROM documentos_cliente d
    JOIN documentos_produto dp ON dp.id_documento = d.id
    WHERE d.id_cliente = ? AND dp.produto = ?
");
$stmt->bind_param("is", $id_cliente, $produto);
$stmt->execute();
$result = $stmt->get_result();

// Criar o arquivo ZIP
$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Adicionar documentos ao ZIP
    while ($row = $result->fetch_assoc()) {
        $caminho_arquivo = $row['caminho_arquivo'];
        $nome_arquivo = basename($caminho_arquivo);
        $zip->addFile($caminho_arquivo, $nome_arquivo);
    }

    // Gerar o PDF com as informações completas do cliente
    $pdf_file = "../uploads/documentos/soufacil_credenciamento_$id_cliente.pdf";
    gerarPdfCompleto($id_cliente, $pdf_file);
    $zip->addFile($pdf_file, "credenciamento_soufacil.pdf");

    $zip->close();

    // Forçar o download do ZIP
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=" . basename($zip_file));
    header("Content-Length: " . filesize($zip_file));
    readfile($zip_file);

    // Limpar os arquivos gerados
    unlink($zip_file);
    unlink($pdf_file);
} else {
    echo "Erro ao criar o arquivo ZIP.";
}

/**
 * Função para gerar o PDF com todas as informações completas do cliente
 */
function gerarPdfCompleto($id_cliente, $pdf_file)
{
    global $conn;

    // Buscar informações do cliente
    $sql_cliente = "SELECT * FROM cliente 
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

    // Criar o PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Função para converter os textos
    function utf8_to_iso($text)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    // Cabeçalho
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Image('../assets/images/logos/soufacil.png', 10, 10, 30);
    $pdf->Cell(0, 6, utf8_to_iso('Credenciamento Lojista'), 0, 1, 'C');
    $pdf->Ln(10);

    // Informações do cliente
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_to_iso('Informações do Cliente:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('CNPJ: ' . $cliente['cnpj']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Razão Social: ' . $cliente['razao_social']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Nome Fantasia: ' . $cliente['nome_fantasia']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Inscrição Estadual: ' . $cliente['insc_est']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Nome Responsável: ' . $cliente['adm_nome']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Telefone Gestor: ' . $cliente['telefone2']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('RG: ' . $cliente['adm_rg']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('CPF: ' . $cliente['adm_cpf']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Data de Nascimento: ' . $cliente['adm_data_nasc']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Estado Civil: ' . $cliente['adm_est_civil']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Nacionalidade: ' . $cliente['adm_nacionalidade']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('E-mail: ' . $cliente['email']), 0, 1);
    $pdf->Ln(10);

    // Informações do Gerente
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Informações do Gerente:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Nome do Gerente: ' . $cliente['ger_nome']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Telefone do Gerente: ' . $cliente['ger_telefone1']), 0, 1);
    $pdf->Ln(10);

    // Informações do Financeiro
    // Informações do Financeiro
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Informações Financeiras:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Nome Financeiro: ' . $cliente['fin_nome']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Telefone Financeiro: ' . $cliente['fin_telefone1']), 0, 1);
    $pdf->Ln(10);

    // Endereço Comercial
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_to_iso('Endereço Comercial:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Endereço: ' . $cliente['logradouro'] . ', ' . $cliente['numero'] . ' ' . $cliente['complemento']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Bairro: ' . $cliente['bairro']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Cidade: ' . $cliente['cidade']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('UF: ' . $cliente['uf']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('CEP: ' . $cliente['cep']), 0, 1);
    $pdf->Ln(10);

    // Informações de Taxas e Condições
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Taxas e Condições:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Taxa Condições: ' . $cliente['taxa_adm']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Condições: ' . $cliente['condicoes']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Tipo Taxa: ' . $cliente['tipo_taxa']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Mensalidade: ' . $cliente['mensalidade']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Taxa Antecipação: ' . $cliente['taxa_antecipado']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Adesão: ' . $cliente['valor']), 0, 1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12); // Configurar fonte em negrito
    $pdf->Ln(10); // Adicionar espaçamento superior (10 unidades de altura)
    $pdf->Cell(0, 6, utf8_to_iso('Dados Bancários'), 0, 1);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Nome Conta: ' . $cliente['razao_social']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Agência: ' . $cliente['agencia']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Conta: ' . $cliente['conta']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Banco: ' . $cliente['banco']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Pix: ' . $cliente['pix']), 0, 1);
    $pdf->Cell(0, 6, utf8_to_iso('Favorecido: ' . $cliente['favorecido']), 0, 1);


    // Representantes
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, utf8_to_iso('Representantes:'), 0, 1);
    $pdf->SetFont('Arial', '', 12);
    if (!empty($representantes)) {
        foreach ($representantes as $representante) {
            $pdf->Cell(0, 6, utf8_to_iso('- ' . $representante), 0, 1);
        }
    } else {
        $pdf->Cell(0, 6, utf8_to_iso('Nenhum representante cadastrado.'), 0, 1);
    }

    // Salvar o PDF
    $pdf->Output('F', $pdf_file);
}
