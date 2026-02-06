<?php
// Redirecionar para a pasta ticket/
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /ticket/reply_ticket.php' . $query);
exit;
?>









