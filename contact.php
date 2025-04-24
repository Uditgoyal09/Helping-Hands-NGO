<?php
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message']);

    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        
        // Send email notification
        $to = "bob1977singh@gmail.com";
        $subject = "New Contact Form Submission";
        $headers = "From: $email";
        
        mail($to, $subject, $message, $headers);
        
        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>