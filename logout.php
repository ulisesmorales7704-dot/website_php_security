<?php
session_start();

// ১. সব সেশন ডাটা রিমুভ করা
$_SESSION = array();

// ২. সেশন কুকি ডিলিট করা (ব্রাউজার থেকে সেশন আইডি মুছে ফেলার জন্য)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ৩. সেশনটি পুরোপুরি ধ্বংস করা
session_destroy();

// ৪. ইউজারকে লগইন পেজে পাঠিয়ে দেয়া
header("Location: login.php?logout=success");
exit();
?>