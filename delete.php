<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM measurements WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: view_table.php');
    exit;
} else {
    echo "Invalid request.";
}
