<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

$message = "";
$is_blocked = false;
$seconds_left = 0;
$blocked_email = ""; // ব্লকড ইমেইলটি ট্র্যাক করার জন্য

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT 'admin' as type, id, email, password, status, blocked_until, login_attempts FROM admin WHERE email = ? 
                UNION 
                SELECT 'user' as type, id, u_email as email, u_pass as password, status, blocked_until, login_attempts FROM user WHERE u_email = ?
            ");
            $stmt->execute([$email, $email]);
            $account = $stmt->fetch();

            if ($account) {
                $table = ($account['type'] === 'admin') ? 'admin' : 'user';
                $email_col = ($account['type'] === 'admin') ? 'email' : 'u_email';

                $currentTime = time();
                $blockedUntil = $account['blocked_until'] ? strtotime($account['blocked_until']) : 0;

                if ($account['status'] === 'blocked' && $blockedUntil > $currentTime) {
                    $is_blocked = true;
                    $seconds_left = $blockedUntil - $currentTime;
                    $blocked_email = $email;
                    $message = "This specific account is locked. Please wait.";
                } else {
                    if (password_verify($password, $account['password'])) {
                        $pdo->prepare("UPDATE $table SET login_attempts = 0, status = 'active', blocked_until = NULL WHERE $email_col = ?")
                            ->execute([$email]);

                        session_regenerate_id(true);
                        if ($account['type'] === 'admin') {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $account['id'];
                            header("Location: admin.php");
                        } else {
                            $_SESSION['user_id'] = $account['id'];
                            $_SESSION['user_name'] = $account['u_name'] ?? 'User';
                            header("Location: dashboard.php");
                        }
                        exit();
                    } else {
                        $new_attempts = ($account['login_attempts'] ?? 0) + 1;
                        if ($new_attempts >= 3) {
                            $lock_time = date('Y-m-d H:i:s', time() + 600);
                            $pdo->prepare("UPDATE $table SET login_attempts = ?, status = 'blocked', blocked_until = ? WHERE $email_col = ?")
                                ->execute([$new_attempts, $lock_time, $email]);
                            
                            $is_blocked = true;
                            $seconds_left = 600;
                            $blocked_email = $email;
                            $message = "Too many failed attempts. Account locked for 10 minutes.";
                        } else {
                            $pdo->prepare("UPDATE $table SET login_attempts = ? WHERE $email_col = ?")
                                ->execute([$new_attempts, $email]);
                            $left = 3 - $new_attempts;
                            $message = "Invalid password. $left attempts remaining.";
                        }
                    }
                }
            } else {
                $message = "No account found with this email.";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Secure Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-md p-8 rounded-3xl relative">
        <a href="index.php" class="absolute top-6 left-6 text-slate-400 hover:text-white transition-all text-xs flex items-center gap-2 group">
            <i class="fa-solid fa-arrow-left-long group-hover:-translate-x-1 transition-transform"></i>
            <span>Back</span>
        </a>

        <div class="mt-4">
            <h2 class="text-2xl font-bold mb-2 text-center text-white">Welcome Back</h2>
            <p class="text-center text-xs text-slate-400 mb-8">Enter your credentials to access your account.</p>
        </div>

        <?php if($message): ?>
            <div id="msg-container" class="mb-4 p-2 text-center rounded bg-red-500/10 border border-red-500/50 text-red-400 text-xs italic font-bold flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> 
                <span><span id="msg-text"><?= $message ?></span> <span id="timer" class="ml-1"></span></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="login-form" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500"><i class="fa-solid fa-envelope text-xs"></i></span>
                    <input type="email" name="email" id="email-input" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required placeholder="name@company.com" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 pl-10 text-sm focus:border-blue-500 outline-none transition text-white">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs text-slate-400">Password</label>
                    <a href="forgot-password.php" class="text-[10px] text-blue-400 hover:underline">Forgot password?</a>
                </div>
                <div class="relative flex items-center">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500"><i class="fa-solid fa-lock text-xs"></i></span>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 pl-10 pr-10 text-sm focus:border-blue-500 outline-none transition text-white">
                    <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-0 w-10 h-full flex items-center justify-center text-slate-500 hover:text-white transition"><i id="eye1" class="fa-solid fa-eye text-xs"></i></button>
                </div>
            </div>

            <button type="submit" name="login" id="login-btn" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg font-bold text-sm transition shadow-lg shadow-blue-900/20 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                Sign In
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 mt-8">
            New here? <a href="registration.php" class="text-blue-400 hover:underline">Create an account</a>
        </p>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            input.type = input.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }

        const btn = document.getElementById('login-btn');
        const timerDisplay = document.getElementById('timer');
        const emailInput = document.getElementById('email-input');
        const msgContainer = document.getElementById('msg-container');
        const msgText = document.getElementById('msg-text');

        // পিএইচপি থেকে আসা ডাটা
        let secondsLeft = <?= (int)$seconds_left ?>;
        let blockedEmail = "<?= $blocked_email ?>";

        function startTimer() {
            if (secondsLeft > 0) {
                btn.disabled = true;
                const countdown = setInterval(() => {
                    secondsLeft--;
                    let m = Math.floor(secondsLeft / 60);
                    let s = secondsLeft % 60;
                    timerDisplay.innerText = `(${m}:${s < 10 ? '0' + s : s})`;

                    if (secondsLeft <= 0) {
                        clearInterval(countdown);
                        timerDisplay.innerText = "";
                        btn.disabled = false;
                        if(msgContainer) msgContainer.style.display = 'none';
                    }
                }, 1000);
            }
        }

        // পেজ লোডে যদি ব্লকড ইমেইলটি ইনপুট ফিল্ডে থাকে তবে টাইমার শুরু হবে
        if (emailInput.value === blockedEmail && secondsLeft > 0) {
            startTimer();
        }

        // ইউজার ইমেইল চেঞ্জ করলে বাটন এনাবেল হবে (যদি না নতুন ইমেইলটিও ব্লকড হয়)
        emailInput.addEventListener('input', function() {
            if (this.value !== blockedEmail) {
                btn.disabled = false;
                timerDisplay.innerText = "";
                // মেসেজ হাইড না করা ভালো, কারণ এটি আগের অ্যাটেম্পটের হিস্ট্রি দেখায়
            } else if (secondsLeft > 0) {
                btn.disabled = true;
                startTimer();
            }
        });
    </script>
</body>
</html>