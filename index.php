<?php
require_once __DIR__ . '/config/session.php';
requireLogin();
if (isLoggedIn()) { header('Location: dashboard.php'); } else { header('Location: login.php'); }
exit();
