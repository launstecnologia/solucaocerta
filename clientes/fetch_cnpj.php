<?php
header('Content-Type: application/json');

if (isset($_GET['cnpj'])) {
    $cnpj = preg_replace('/\D/', '', $_GET['cnpj']); // Remove caracteres não numéricos

    if (strlen($cnpj) === 14) {
        $url = "https://receitaws.com.br/v1/cnpj/$cnpj";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo json_encode(['error' => 'Erro ao buscar CNPJ: ' . curl_error($ch)]);
        } else {
            echo $response;
        }
        
        curl_close($ch);
    } else {
        echo json_encode(['error' => 'CNPJ inválido.']);
    }
} else {
    echo json_encode(['error' => 'CNPJ não fornecido.']);
}
?>
