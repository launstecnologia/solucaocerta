<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['id']);
}

function isAdmin() {
    return isset($_SESSION['nivel']) && $_SESSION['nivel'] == 'admin';
}

function protectPage() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

function protectAdminPage() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: index.php");
        exit();
    }
}
?>
