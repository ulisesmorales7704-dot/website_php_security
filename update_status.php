<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $id = (int)$_POST['request_id'];
    $status = $_POST['status'];

    try {
        // 1. Update the status in the requests table
        $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);

        if ($result) {
            // 2. Logic: If status is 'active', send email to the user
            if ($status == 'active') {
                // Fetch user email and service name from your dump structure
                $userStmt = $pdo->prepare("SELECT u.u_email, u.u_name, s.title 
                                           FROM requests r 
                                           JOIN user u ON r.user_id = u.id 
                                           JOIN services s ON r.service_id = s.id 
                                           WHERE r.id = ?");
                $userStmt->execute([$id]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $to = $userData['u_email'];
                    $subject = "Update: Project Matrix Status Active";
                    $message = "Hello " . $userData['u_name'] . ",\n\n" .
                               "Your request for '" . $userData['title'] . "' has been set to ACTIVE.\n" .
                               "Our studio team is now processing your project.\n\n" .
                               "Regards,\nStudio Admin";
                    $headers = "From: admin@studio.com";

                    // Note: mail() works on live servers. On local XAMPP, it requires SMTP config.
                    @mail($to, $subject, $message, $headers);
                }
            }
            echo "Success";
        } else {
            echo "Failed to update database.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}
?>