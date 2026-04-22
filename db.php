<?php
// ১. ডাটাবেস কনফিগারেশন
define('DB_HOST', 'localhost');
define('DB_NAME', 'd_website');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // ২. PDO কানেকশন এবং কিছু সিকিউরিটি অপশন সেট করা
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // এররগুলো এক্সেপশন হিসেবে দেখাবে
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // ডিফল্টভাবে এসোসিয়েটিভ অ্যারে দিবে
        PDO::ATTR_EMULATE_PREPARES   => false,                  // রিয়েল প্রিপেয়ার্ড স্টেটমেন্ট নিশ্চিত করবে (সিকিউরিটি!)
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // ৩. সিকিউরিটি টিপ: লাইভ সার্ভারে কখনোই ফুল এরর মেসেজ দেখাবেন না। 
    // শুধু একটি লগ ফাইল বা সিম্পল মেসেজ দিন।
    error_log($e->getMessage()); // এররটি সার্ভার লগে সেভ হবে
    die("Database Connection Error. Please try again later."); 
}
?>