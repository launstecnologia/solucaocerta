<?php
require_once '../config/config.php';

$id_cliente = $_GET['id']; // Recebe o ID do cliente via URL

// Função para limpar o número de telefone
function limparNumero($numero) {
    return preg_replace('/\D/', '', $numero);
}

// Token atualizado
$token = "a6f7b874ea69329372ad75353314d7bcacd8c0be365023dab195bcac015d6009";

// Consulta SQL para buscar os dados do cliente e verificar colunas de produtos
$sql = "SELECT adm_nome, telefone2, valor, brasil_card, check_ok, assisfinan, fgts, ok_antecipa, pagseguro, soufacil
        FROM cliente
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta SQL: " . $conn->error);
}

$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

if (!$dados) {
    die("Cliente não encontrado ou erro na execução da consulta.");
}

// Dados do cliente
$clienteNome = $dados['adm_nome'];
$clienteWhatsapp = limparNumero($dados['telefone2']);
$produtosSelecionados = [];
$temBrasilCard = $dados['brasil_card'] ? true : false;
$valorRecibo = $dados['valor'];

// Verificar cada coluna de produtos e adicionar ao array de produtos selecionados
if ($dados['brasil_card']) $produtosSelecionados[] = "Brasil Card";
if ($dados['check_ok']) $produtosSelecionados[] = "Check OK";
if ($dados['assisfinan']) $produtosSelecionados[] = "Assisfinan";
if ($dados['fgts']) $produtosSelecionados[] = "FGTS";
if ($dados['ok_antecipa']) $produtosSelecionados[] = "OK Antecipação";
if ($dados['pagseguro']) $produtosSelecionados[] = "PagSeguro";
if ($dados['soufacil']) $produtosSelecionados[] = "Sou Fácil";

// Criar a lista de produtos como uma string separada por vírgulas
$produtos = implode(", ", $produtosSelecionados);

// Links condicionais
$termoLink = $temBrasilCard ? "https://sistema.asolucaocerta.com.br/clientes/termo_bcard.php?id=" . $id_cliente : "";
$reciboLink = $valorRecibo ? "https://sistema.asolucaocerta.com.br/clientes/recibo.php?id=" . $id_cliente : "";

// Formatar a lista de produtos com negrito e quebra de linha
$produtosFormatados = "";
foreach ($produtosSelecionados as $produto) {
    $produtosFormatados .= "*$produto*\n";
}

// Criar a mensagem de boas-vindas personalizada
$primeiroNome = explode(' ', $clienteNome)[0];
$mensagem = "Olá, $primeiroNome!\n\n";
$mensagem .= "Bem-Vindo(a) à Solução Certa! Estamos aqui para auxiliar em todo o seu processo de credenciamento.\n\n";
$mensagem .= "Você está credenciando nos seguintes serviços/produtos:\n$produtosFormatados\n";

if ($temBrasilCard) {
    $mensagem .= "Segue abaixo o link do Termo de Adesão:\n$termoLink\n\n";
}

if ($valorRecibo) {
    $mensagem .= "Agora, estamos enviando o recibo referente ao credenciamento e treinamento:\n$reciboLink\n\n";
}

$mensagem .= "Dúvidas? Estamos totalmente à disposição.\nSolução Certa";

// Função para enviar mensagem de texto
function sendMessage($token, $number, $body) {
    $ch = curl_init("https://api.evowhats.com.br/api/messages/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "number" => $number,
        "body" => $body
    ]));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Função para enviar mídia (PDF)
function sendMedia($token, $number, $filePath) {
    $ch = curl_init("https://api.evowhats.com.br/api/messages/send");
    $file = new CURLFile($filePath);

    $data = [
        "number" => $number,
        "medias" => $file
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: multipart/form-data"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Caminho do PDF
$pdfFilePath = __DIR__ . "/../assets/images/Nossas_Boas_Vindas.pdf";

// Enviar mensagem de texto
$responseText = sendMessage($token, $clienteWhatsapp, $mensagem);

// Enviar PDF
$responseMedia = sendMedia($token, $clienteWhatsapp, $pdfFilePath);

header('Location: detalhes.php?id=' . $id_cliente);
