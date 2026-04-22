<?php
session_start();

// 1. IMPORT PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 2. DATABASE CONFIGURATION
$host = 'localhost'; $dbname = 'd_website'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Connection failed: " . $e->getMessage()); }

$message = "";
$view = "register"; 

// --- HELPER FUNCTION: Send Mail (Centralized) ---
function sendOTP($toEmail, $otpCode) {
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
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your 6-digit verification code is: <b style='font-size: 20px;'>$otpCode</b>. <br>It will expire in 2 minutes.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// --- BRANCH 1: Initial Registration ---
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Error: Passwords do not match!";
    } else {
        $stmt = $pdo->prepare("SELECT u_email FROM user WHERE u_email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "Error: This email is already registered!";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['pending'] = [
                'u_name'  => $username,
                'u_email' => $email,
                'u_pass'  => password_hash($password, PASSWORD_DEFAULT),
                'otp'     => $otp,
                'time'    => time()
            ];
            if (sendOTP($email, $otp)) {
                $view = "verify"; 
            } else {
                $message = "Error: Email could not be sent.";
            }
        }
    }
}

// --- BRANCH 2: Resend Code Logic ---
if (isset($_POST['resend'])) {
    if (isset($_SESSION['pending'])) {
        $new_otp = rand(100000, 999999);
        $_SESSION['pending']['otp'] = $new_otp;
        $_SESSION['pending']['time'] = time(); 

        if (sendOTP($_SESSION['pending']['u_email'], $new_otp)) {
            $message = "Success: A new code has been sent!";
            $view = "verify";
        } else {
            $message = "Error: Email failed to resend.";
            $view = "verify";
        }
    } else {
        $message = "Error: Registration session expired. Please start over.";
        $view = "register";
    }
}

// --- BRANCH 3: Verification ---
if (isset($_POST['verify'])) {
    $pending = $_SESSION['pending'] ?? null;
    $user_otp = $_POST['otp_code'];

    if ($pending && ($user_otp == $pending['otp'])) {
        if ((time() - $pending['time']) <= 120) {
            try {
                $sql = "INSERT INTO user (u_name, u_email, u_pass) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$pending['u_name'], $pending['u_email'], $pending['u_pass']]);
                unset($_SESSION['pending']);
                $view = "success";
            } catch (PDOException $e) {
                $message = "Error: Database problem.";
                $view = "register";
            }
        } else {
            $message = "Error: Code expired! Please click Resend.";
            $view = "verify";
        }
    } else {
        $message = "Error: Invalid verification code!";
        $view = "verify";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Register | Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        input::-ms-reveal, input::-ms-clear { display: none; } /* Hide default edge eye */
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6 relative">

    <div class="glass w-full max-w-md p-8 rounded-3xl relative">
        <a href="index.php" class="absolute top-6 left-6 text-slate-400 hover:text-white transition-all text-xs flex items-center gap-2 group">
            <i class="fa-solid fa-arrow-left-long group-hover:-translate-x-1 transition-transform"></i>
            <span>Back</span>
        </a>

        <?php if($message): ?>
            <div class="mt-8 mb-4 p-2 text-center rounded <?= strpos($message, 'Success') !== false ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?> text-xs italic font-bold border">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <?php if($view == "register"): ?>
                <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>
                <form method="POST" class="space-y-3">
                    <div>
                        <label class="text-xs text-slate-400">User Name</label>
                        <input type="text" name="username" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400">Email Address</label>
                        <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm text-white focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div class="relative">
                        <label class="text-xs text-slate-400">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 pr-10 text-sm text-white focus:outline-none focus:border-blue-500">
                            <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i id="eye1" class="fa-solid fa-eye-slash text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="text-xs text-slate-400">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 pr-10 text-sm text-white focus:outline-none focus:border-blue-500">
                            <button type="button" onclick="togglePass('confirm_password', 'eye2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i id="eye2" class="fa-solid fa-eye-slash text-xs"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="w-full bg-blue-600 hover:bg-blue-500 py-2.5 rounded-lg font-bold text-sm transition mt-3">Register Now</button>
                    
                    <div class="text-center mt-6">
                        <p class="text-xs text-slate-400">
                            Already have an account? 
                            <a href="login.php" class="text-blue-400 hover:text-blue-300 font-bold ml-1 transition-colors underline underline-offset-4">Sign In</a>
                        </p>
                    </div>
                </form>

                <script>
                    function togglePass(inputId, eyeId) {
                        const input = document.getElementById(inputId);
                        const eye = document.getElementById(eyeId);
                        if (input.type === "password") {
                            input.type = "text";
                            eye.classList.replace('fa-eye-slash', 'fa-eye');
                        } else {
                            input.type = "password";
                            eye.classList.replace('fa-eye', 'fa-eye-slash');
                        }
                    }
                </script>

            <?php elseif($view == "verify"): ?>
                <h2 class="text-2xl font-bold mb-2 text-center">Verify Email</h2>
                <p class="text-xs text-slate-400 text-center mb-6">Sent to: <?= htmlspecialchars($_SESSION['pending']['u_email']) ?></p>
                
                <div class="text-center mb-6">
                    <span class="text-[10px] uppercase text-slate-500">Code expires in:</span>
                    <div id="timer" class="text-xl font-mono font-bold text-blue-400">02:00</div>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="text" name="otp_code" id="otp_input" placeholder="000000" maxlength="6" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-3 text-center text-2xl tracking-[0.3em] text-white outline-none focus:border-blue-500">
                    
                    <div id="button-container">
                        <button type="submit" id="btn-verify" name="verify" class="w-full bg-emerald-600 hover:bg-emerald-500 py-2.5 rounded-lg font-bold text-sm transition">Verify Code</button>
                        <button type="submit" id="btn-resend" name="resend" class="hidden w-full bg-blue-600 hover:bg-blue-500 py-2.5 rounded-lg font-bold text-sm transition">Resend Code</button>
                    </div>
                </form>

                <script>
                    let startTime = <?= $_SESSION['pending']['time'] ?>;
                    const timerDisplay = document.getElementById('timer');
                    const verifyBtn = document.getElementById('btn-verify');
                    const resendBtn = document.getElementById('btn-resend');
                    const otpInput = document.getElementById('otp_input');

                    function updateTimer() {
                        const now = Math.floor(Date.now() / 1000);
                        const diff = 120 - (now - startTime);

                        if (diff <= 0) {
                            timerDisplay.innerText = "00:00";
                            timerDisplay.classList.replace('text-blue-400', 'text-red-500');
                            verifyBtn.classList.add('hidden');
                            resendBtn.classList.remove('hidden');
                            otpInput.disabled = true;
                            otpInput.placeholder = "Expired";
                        } else {
                            const mins = Math.floor(diff / 60).toString().padStart(2, '0');
                            const secs = (diff % 60).toString().padStart(2, '0');
                            timerDisplay.innerText = `${mins}:${secs}`;
                        }
                    }
                    setInterval(updateTimer, 1000);
                    updateTimer();
                </script>

            <?php elseif($view == "success"): ?>
                <div class="text-center py-6">
                    <div class="text-5xl mb-4 text-emerald-500"><i class="fa-solid fa-circle-check"></i></div>
                    <h2 class="text-xl font-bold mb-2">Success!</h2>
                    <p class="text-sm text-slate-400 mb-6">Verified successfully.</p>
                    <a href="login.php" class="inline-block w-full bg-white text-slate-950 py-2.5 rounded-lg font-bold text-sm transition">Login Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>