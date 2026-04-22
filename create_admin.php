<?php
// ১. ডাটাবেস কানেকশন ইমপোর্ট
require 'db.php'; 

// অ্যাডমিন ডিটেইলস
$username = 'admin'; 
$email    = 'nuryeshafahim@gmail.com'; 
$password = 'fahim123'; 

// সিকিউরিটি কোশ্চেন (Plain Text - so it can be displayed)
// অ্যানসার (Encrypted - for security)
$question = "What was the name of your first school?";
$answer   = "RDA";

try {
    // ২. শুধুমাত্র পাসওয়ার্ড এবং অ্যানসার হ্যাশ (Encrypt) করা হচ্ছে
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $hashed_answer   = password_hash($answer, PASSWORD_BCRYPT);

    // ৩. ডাটাবেসে ইনসার্ট করার কুয়েরি
    $sql = "INSERT INTO admin (username, email, password, security_question, security_answer) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // কোশ্চেনটি সরাসরি পাঠানো হচ্ছে ($question)
    if($stmt->execute([
        $username, 
        $email, 
        $hashed_password, 
        $question, 
        $hashed_answer
    ])) {
        echo "<div style='font-family: sans-serif; padding: 20px; background: #dcfce7; color: #166534; border-radius: 10px; border: 1px solid #bbf7d0;'>
                <strong style='font-size: 18px;'>Success:</strong> Admin node created! <br><br>
                <ul style='font-size: 14px;'>
                    <li>Password: <b>Encrypted</b></li>
                    <li>Security Question: <b>Plain Text</b> (Visible for recovery)</li>
                    <li>Security Answer: <b>Encrypted</b></li>
                </ul>
                <hr style='border: 0; border-top: 1px solid #166534; opacity: 0.2; margin: 15px 0;'>
                <em style='color: #991b1b;'>CRITICAL: Delete this file (create_admin.php) immediately from your server for security.</em>
              </div>";
    }
} catch (PDOException $e) {
    echo "<div style='color: red; font-family: sans-serif;'>Database Error: " . $e->getMessage() . "</div>";
}
?>