<?php
// Redirecionar para a pasta ticket/
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /ticket/view_ticket.php' . $query);
exit;
?>









