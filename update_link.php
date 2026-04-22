<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['delivery_link'])) {
    
    $id = $_POST['request_id'];
    $link = $_POST['delivery_link'];

    try {
        // লজিক: লিঙ্ক সেভ করার পাশাপাশি স্ট্যাটাস completed করে দেওয়া হচ্ছে
        $sql = "UPDATE requests SET delivery_link = ?, status = 'completed' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$link, $id])) {
            echo "Success";
        } else {
            echo "Update Failed";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}
?>