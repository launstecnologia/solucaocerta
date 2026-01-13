<?php
require_once '../config/config.php';

$hoje = date('Y-m-d');
$sql = "SELECT t.*, l.nome as lead_nome 
        FROM lead_tarefas t
        JOIN lead l ON t.lead_id = l.id
        WHERE t.data_prevista <= ? AND t.concluido = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hoje);
$stmt->execute();
$result = $stmt->get_result();

while ($tarefa = $result->fetch_assoc()) {
    echo "Tarefa Pendente: " . $tarefa['descricao'] . " para " . $tarefa['lead_nome'] . " em " . $tarefa['data_prevista'] . "\n";
    // Aqui você pode enviar email ou registrar alerta
}
