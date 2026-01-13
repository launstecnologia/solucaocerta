<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


try {
    $pdo = new PDO('mysql:host=localhost;dbname=asolucaocerta_platform', 'asolucaocerta_platform', '117910Campi!');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $contactNumber = $_GET['number'] ?? '';
    $after = $_GET['after'] ?? 0;

    if ($contactNumber) {
        $contactJid = "55" . $contactNumber . "@s.whatsapp.net";

        $stmt = $pdo->prepare("
            SELECT remetente, mensagem, timestamp, tipo 
            FROM mensagens 
            WHERE (remetente = :contactNumber OR remetente = :contactJid OR remetente = 'me') 
              AND timestamp > :after
            ORDER BY timestamp ASC
        ");
        $stmt->execute([
            'contactNumber' => $contactNumber,
            'contactJid' => $contactJid,
            'after' => $after
        ]);

        $messages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = [
                'from' => $row['remetente'],
                'content' => $row['mensagem'],
                'timestamp' => $row['timestamp'],
                'type' => $row['tipo']
            ];
        }

        echo json_encode($messages);
    } else {
        echo json_encode(["status" => "error", "message" => "Número de contato não especificado"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erro ao buscar mensagens: " . $e->getMessage()]);
}
