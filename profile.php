<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ডেটাবেস থেকে ইউজারের লেটেস্ট তথ্য আনা
try {
    $stmt = $pdo->prepare("SELECT u_name, u_email, created_at FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    $userName = $user['u_name'];
    $userEmail = $user['u_email'];
    $joinedDate = date('F Y', strtotime($user['created_at']));
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Service Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f1a; 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,15%,1) 0, transparent 40%), radial-gradient(at 100% 0%, hsla(339,49%,15%,1) 0, transparent 40%); 
            background-attachment: fixed; min-height: 100vh; color: white;
        }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 2.5rem; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3); }
        .input-glass { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.1); color: white; transition: all 0.3s ease; }
        .input-glass:focus { background: rgba(255, 255, 255, 0.05); border-color: #3b82f6; outline: none; box-shadow: 0 0 15px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="p-4 md:p-8 flex items-center justify-center">

    <div class="max-w-4xl w-full">
        <?php if(isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-bold uppercase tracking-widest text-center">
                Profile Updated Successfully!
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl text-xs font-bold uppercase tracking-widest text-center">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors mb-8 group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span class="text-sm font-bold uppercase tracking-widest">Back to Dashboard</span>
        </a>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <div class="glass-card p-8 flex flex-col items-center text-center">
                    <div class="relative group">
                        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-4xl font-black shadow-[0_0_30px_rgba(59,130,246,0.3)]">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <div class="absolute bottom-1 right-1 h-8 w-8 bg-emerald-500 border-4 border-[#121622] rounded-full shadow-lg"></div>
                    </div>
                    <h2 class="mt-6 text-xl font-black tracking-tight"><?= htmlspecialchars($userName) ?></h2>
                    <p class="text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] mt-1">Verified Member</p>
                    <div class="w-full h-[1px] bg-white/5 my-6"></div>
                    <div class="w-full space-y-4">
                        <div class="flex items-center gap-4 text-left p-3 rounded-2xl bg-white/5 border border-white/5">
                            <i class="fa-solid fa-envelope text-blue-500 w-5"></i>
                            <div class="flex flex-col">
                                <span class="text-[9px] text-slate-500 uppercase font-black">Email Address</span>
                                <span class="text-xs truncate max-w-[150px]"><?= htmlspecialchars($userEmail) ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-left p-3 rounded-2xl bg-white/5 border border-white/5">
                            <i class="fa-solid fa-calendar text-indigo-500 w-5"></i>
                            <div class="flex flex-col">
                                <span class="text-[9px] text-slate-500 uppercase font-black">Joined On</span>
                                <span class="text-xs"><?= $joinedDate ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="glass-card p-8 md:p-10 h-full">
                    <h3 class="text-2xl font-black tracking-tighter mb-8">Account Settings</h3>
                    <form action="update_profile.php" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
                                <input type="text" name="name" required value="<?= htmlspecialchars($userName) ?>" class="w-full mt-2 p-4 rounded-2xl input-glass font-semibold text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                                <input type="email" readonly value="<?= htmlspecialchars($userEmail) ?>" class="w-full mt-2 p-4 rounded-2xl bg-white/5 border border-white/10 text-slate-500 text-sm cursor-not-allowed">
                            </div>
                        </div>
                        <div class="pt-4">
                            <h4 class="text-xs font-black text-blue-500 uppercase tracking-[0.3em] mb-6">Security Update</h4>
                            <div class="space-y-4">
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-600"></i>
                                    <input type="password" name="old_password" placeholder="Current Password" class="w-full p-4 pl-12 rounded-2xl input-glass text-sm">
                                </div>
                                <div class="relative">
                                    <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-600"></i>
                                    <input type="password" name="new_password" placeholder="New Password (Optional)" class="w-full p-4 pl-12 rounded-2xl input-glass text-sm">
                                </div>
                            </div>
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_10px_20px_rgba(59,130,246,0.2)] active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>