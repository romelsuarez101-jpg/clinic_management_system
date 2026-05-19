<?php
require_once __DIR__ . '/config/session.php';
requireLogin();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('medicines.php'); }

$stmt = $conn->prepare("SELECT medicine_name FROM medicines WHERE medicine_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) { setFlash('error', 'Medicine not found.'); redirect('medicines.php'); }

$stmt = $conn->prepare("DELETE FROM medicines WHERE medicine_id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    setFlash('success', "Medicine '{$row['medicine_name']}' deleted.");
} else {
    setFlash('error', 'Delete failed: ' . $stmt->error);
}
$stmt->close();
redirect('medicines.php');
