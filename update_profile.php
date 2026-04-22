<?php
session_start();

// ১. ডাটাবেস কানেকশন
if (file_exists('db.php')) {
    require 'db.php';
} else {
    header("Location: profile.php?error=Database configuration missing");
    exit();
}

// ২. সেশন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // ইনপুট ডাটা
    $newName = trim($_POST['name']);
    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];

    if (empty($newName)) {
        header("Location: profile.php?error=Name cannot be empty");
        exit();
    }

    try {
        // ৩. শুধুমাত্র নাম আপডেট (যদি পাসওয়ার্ড পরিবর্তনের চেষ্টা না করা হয়)
        if (empty($oldPass) && empty($newPass)) {
            $stmt = $pdo->prepare("UPDATE user SET u_name = ? WHERE id = ?");
            $stmt->execute([$newName, $user_id]);
            
            $_SESSION['u_name'] = $newName; // সেশন আপডেট
            header("Location: profile.php?success=1");
            exit();
        }

        // ৪. পাসওয়ার্ডসহ প্রোফাইল আপডেট
        if (!empty($oldPass)) {
            // ডাটাবেস থেকে বর্তমান পাসওয়ার্ড (u_pass) আনা
            $stmt = $pdo->prepare("SELECT u_pass FROM user WHERE id = ?");
            $stmt->execute([$user_id]);
            $userData = $stmt->fetch();

            // password_verify এর মাধ্যমে u_pass কলাম চেক করা
            if ($userData && password_verify($oldPass, $userData['u_pass'])) {
                
                if (!empty($newPass)) {
                    // নতুন পাসওয়ার্ড হ্যাশ করা
                    $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE user SET u_name = ?, u_pass = ? WHERE id = ?");
                    $updateStmt->execute([$newName, $hashedPass, $user_id]);
                } else {
                    // যদি নতুন পাসওয়ার্ড ফিল্ড খালি থাকে, শুধু নাম আপডেট
                    $updateStmt = $pdo->prepare("UPDATE user SET u_name = ? WHERE id = ?");
                    $updateStmt->execute([$newName, $user_id]);
                }

                $_SESSION['u_name'] = $newName;
                header("Location: profile.php?success=1");
                exit();
            } else {
                header("Location: profile.php?error=Current password is incorrect");
                exit();
            }
        } else {
            header("Location: profile.php?error=Please provide current password to update security settings");
            exit();
        }

    } catch (PDOException $e) {
        header("Location: profile.php?error=Update failed: " . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}