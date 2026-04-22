<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];

    try {
        // প্রথমে সার্ভিসের ডেলিভারি টাইম কত দিন তা জেনে নেওয়া
        $sStmt = $pdo->prepare("SELECT delivery_time FROM services WHERE id = ?");
        $sStmt->execute([$service_id]);
        $service = $sStmt->fetch();

        if ($service) {
            $days = $service['delivery_time'];
            // বর্তমান সময় থেকে ডেলিভারি টাইম যোগ করে expiry_time বের করা
            $expiry_time = date('Y-m-d H:i:s', strtotime("+$days days"));

            // রিকোয়েস্ট টেবিলে ডাটা ইনসার্ট
            $sql = "INSERT INTO requests (user_id, service_id, status, expiry_time) VALUES (?, ?, 'pending', ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$user_id, $service_id, $expiry_time])) {
                // অর্ডার সফল হলে Requested ট্যাবে পাঠিয়ে দিবে
                header("Location: dashboard.php?tab=requested&order=success");
                exit();
            }
        }
    } catch (PDOException $e) {
        die("Order Error: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}