<?php
session_start();

/* Prevent browser cache */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {

    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }

    // Prevent browser back cache
    echo '
    <script>
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.location.href = "login.php";
        };
    </script>
    ';
}

function getCurrentUser() {
    return $_SESSION['username'] ?? '';
}
?>