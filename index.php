<?php
// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';
require_once 'login/session.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login/index.php");
    exit();
}
?>
