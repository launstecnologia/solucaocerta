<?php

$loginUrl = "https://portal.asolucaocerta.com.br/login";
$clientPageUrl = "https://portal.asolucaocerta.com.br/marketplace/clients?page=1&limit=20";
$postData = [
    'email' => 'lucasmoraes.lrm@gmail.com',
    'password' => '271001Lrm!'
];

// Inicializa o cURL para login
$ch = curl_init();

// Configurações de cabeçalho
$headers = [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36',
    'Referer: ' . $loginUrl
];

// Configurações para fazer a requisição de login
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Aplica os cabeçalhos
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_VERBOSE, true); // Habilita modo de depuração

// Executa o login e captura a resposta para verificação
$loginResponse = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Erro no cURL: ' . curl_error($ch);
} else {
    echo "Resposta de login:<br>";
    echo $loginResponse;
}

// Configurações para acessar a página de destino após o login
curl_setopt($ch, CURLOPT_URL, $clientPageUrl);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Executa a requisição para a página de destino e captura o conteúdo
$pageContent = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Erro no cURL ao acessar a página de destino: ' . curl_error($ch);
} else {
    echo "Conteúdo da página de destino:<br>";
    echo $pageContent;
}

// Fecha a conexão cURL
curl_close($ch);
?>