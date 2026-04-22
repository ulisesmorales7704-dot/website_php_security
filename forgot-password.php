<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 1. Database Connection & Configuration
$host = 'localhost'; $dbname = 'd_website'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Force lowercase column access to prevent case-sensitivity issues
    $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
} catch (PDOException $e) { die("System Offline: Matrix Connection Failure"); }

$message = ""; $status = ""; $view = "request_email"; 

// --- HELPER: SMTP Dispatcher ---
function sendSecureLink($toEmail, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nurfahim58@gmail.com'; 
        $mail->Password   = 'ufnp icbd fnod wulu'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('nurfahim58@gmail.com', 'Secure Portal');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Identity Synchronization Protocol';
        $link = "http://localhost" . $_SERVER['PHP_SELF'] . "?token=$token";
        $mail->Body = "<h3>Security Alert: Reset Requested</h3>
                       <p>A request to synchronize credentials has been authorized.</p>
                       <p>Establish your new access key here:</p>
                       <a href='$link' style='padding:12px 24px; background:#2563eb; color:white; text-decoration:none; border-radius:8px; display:inline-block; font-weight:bold;'>Reset Password</a>
                       <br><p style='color:#64748b; font-size:12px; margin-top:20px;'>Link validity: 20 minutes.</p>";
        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}

// --- STEP 0: Link Token Processing ---
if (isset($_GET['token'])) {
    $token_url = $_GET['token'];
    $session_data = $_SESSION['reset_data'] ?? null;
    if ($session_data && $token_url === $session_data['token'] && time() <= $session_data['expiry']) {
        $view = "reset_form";
    } else {
        $message = "CRITICAL: Reset link expired or tampered.";
        $view = "request_email";
    }
}

// --- STEP 1: Search Identity ---
if (isset($_POST['send_link'])) {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if (!empty($admin['security_question'])) {
            $_SESSION['temp_recovery'] = [
                'email'    => $admin['email'], 
                'type'     => 'admin', 
                'question' => $admin['security_question'], 
                'hash'     => $admin['security_answer'] 
            ];
            $view = "security_challenge";
        } else {
            $message = "Error: This Admin has no security question set.";
        }
    } else {
        $stmtUser = $pdo->prepare("SELECT u_email FROM user WHERE u_email = ? LIMIT 1");
        $stmtUser->execute([$email]);
        if ($stmtUser->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['reset_data'] = ['email' => $email, 'type' => 'user', 'token' => $token, 'expiry' => time() + 1200];
            if (sendSecureLink($email, $token)) {
                $message = "Success: Synchronization link dispatched.";
                $status = "success";
            }
        } else {
            $message = "Error: Identity not found in matrix.";
        }
    }
}

// --- STEP 2: Verify Admin Challenge ---
if (isset($_POST['verify_answer'])) {
    $user_answer = trim($_POST['security_answer']);
    $temp = $_SESSION['temp_recovery'] ?? null;

    if ($temp && password_verify($user_answer, $temp['hash'])) {
        $token = bin2hex(random_bytes(32));
        $_SESSION['reset_data'] = ['email' => $temp['email'], 'type' => 'admin', 'token' => $token, 'expiry' => time() + 1200];
        if (sendSecureLink($temp['email'], $token)) {
            $message = "Challenge Passed: Check mailbox for final link.";
            $status = "success";
            unset($_SESSION['temp_recovery']);
        }
    } else {
        $message = "Error: Secret answer mismatch.";
        $view = "security_challenge";
    }
}

// --- STEP 3: Update Credentials ---
if (isset($_POST['update_password'])) {
    $new_pass = $_POST['new_pass'];
    $conf_pass = $_POST['conf_pass'];
    $data = $_SESSION['reset_data'] ?? null;

    if ($data && $new_pass === $conf_pass) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        if ($data['type'] == 'admin') {
            $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE email = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE user SET u_pass = ? WHERE u_email = ?");
        }
        $stmt->execute([$hashed, $data['email']]);
        unset($_SESSION['reset_data']);
        $view = "success_msg";
    } else {
        $message = "Error: Credential mismatch. Try again.";
        $view = "reset_form";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery | Secure Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; color: #f1f5f9; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        .eye-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748b; transition: color 0.3s; }
        .eye-icon:hover { color: #3b82f6; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-center">

    <div class="glass w-full max-w-md p-10 rounded-[2.5rem]">
        
        <?php if($message): ?>
            <div class="mb-6 p-3 text-[10px] font-bold uppercase tracking-widest rounded-xl border <?= $status == 'success' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($view == "request_email"): ?>
            <h2 class="text-2xl font-black italic uppercase mb-2 tracking-tight">Identity <span class="text-blue-500">Search</span></h2>
            <p class="text-[10px] text-slate-500 mb-8 uppercase tracking-widest">System authentication check</p>
            <form method="POST" class="space-y-4">
                <input type="email" name="email" required placeholder="Identity Email" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 text-sm outline-none focus:border-blue-500 text-white transition-all">
                <button type="submit" name="send_link" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] transition shadow-lg shadow-blue-900/40 active:scale-95">Verify Identity</button>
            </form>

        <?php elseif($view == "security_challenge"): ?>
            <h2 class="text-2xl font-black italic uppercase mb-2 text-yellow-500 tracking-tighter">Identity Challenge</h2>
            <p class="text-[10px] text-slate-500 mb-8 uppercase tracking-widest">Verification for Admin Nodes</p>
            <form method="POST" class="space-y-4 text-left">
                <div class="bg-blue-600/10 p-5 rounded-xl border border-blue-500/20 mb-4">
                    <p class="text-[9px] text-blue-400 uppercase font-black tracking-widest mb-1 italic">Security Question:</p>
                    <p class="text-sm italic text-white font-medium leading-relaxed">
                        "<?= htmlspecialchars($_SESSION['temp_recovery']['question'] ?? 'Fetch Error: Verify Admin Table Data') ?>?"
                    </p>
                </div>
                <input type="text" name="security_answer" required placeholder="Secret Answer" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 text-sm outline-none focus:border-blue-500 text-white">
                <button type="submit" name="verify_answer" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] transition shadow-lg shadow-blue-900/40 active:scale-95">Verify Answer</button>
            </form>

        <?php elseif($view == "reset_form"): ?>
            <h2 class="text-2xl font-black italic uppercase mb-2 tracking-tight">Overwrite <span class="text-blue-500">Access</span></h2>
            <p class="text-[10px] text-slate-500 mb-8 uppercase tracking-widest">Establish new matrix credentials</p>
            <form method="POST" class="space-y-5 text-left">
                <div>
                    <label class="text-[10px] font-black text-slate-500 ml-1 uppercase tracking-widest">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_pass" name="new_pass" required class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 text-sm outline-none focus:border-blue-500 text-white pr-12">
                        <i class="fa-solid fa-eye eye-icon" onclick="togglePass('new_pass', this)"></i>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 ml-1 uppercase tracking-widest">Confirm Sync</label>
                    <div class="relative">
                        <input type="password" id="conf_pass" name="conf_pass" required class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 text-sm outline-none focus:border-blue-500 text-white pr-12">
                        <i class="fa-solid fa-eye eye-icon" onclick="togglePass('conf_pass', this)"></i>
                    </div>
                </div>
                <button type="submit" name="update_password" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-xl font-bold text-xs uppercase tracking-[0.2em] transition">Sync Matrix</button>
            </form>

            <script>
                function togglePass(inputId, icon) {
                    const input = document.getElementById(inputId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            </script>

        <?php elseif($view == "success_msg"): ?>
            <i class="fa-solid fa-circle-check text-6xl text-emerald-500 mb-6"></i>
            <h2 class="text-2xl font-black uppercase italic mb-2 tracking-tighter">Sync Successful</h2>
            <p class="text-[11px] text-slate-400 mb-8 uppercase tracking-[0.2em] leading-relaxed italic">Matrix credentials updated. Return to portal.</p>
            <a href="login.php" class="inline-block w-full bg-white text-slate-950 py-4 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg active:scale-95 transition">Login Portal</a>
        <?php endif; ?>

        <div class="mt-10 text-xs text-slate-500 border-t border-white/5 pt-6">
            <a href="login.php" class="hover:text-blue-400 transition-colors uppercase tracking-widest font-black text-[10px] flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Return to Login
            </a>
        </div>
    </div>

</body>
</html>