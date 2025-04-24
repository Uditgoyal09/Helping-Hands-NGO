<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    try {
        $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        echo json_encode(['status' => 'success', 'message' => 'Subscription successful!']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Subscription failed: ' . $e->getMessage()]);
    }
}
?>