<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
session_start();
require 'db.php';

// ২. ইউজার ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        
        // নিরাপত্তা: অ্যাডমিন নিজেকে যেন ডিলিট না করতে পারে (যদি সেশনে আইডি থাকে)
        if(isset($_SESSION['admin_id']) && $delete_id == $_SESSION['admin_id']) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=self_delete");
            exit();
        }

        $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$delete_id]);
        header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
        exit();
    } catch (PDOException $e) {
        die("Deletion Failed: " . $e->getMessage());
    }
}

// ৩. কাউন্টার ও ইউজার লিস্ট নিয়ে আসা
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
    
    // ইউজার লিস্ট কুয়েরি
    $stmt = $pdo->query("SELECT * FROM user ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    die("Database Error: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Directory | Admin Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; color: #f1f5f9; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="min-h-screen p-6 md:p-10 flex flex-col items-center">

    <main class="w-full max-w-6xl space-y-6">
        
        <div class="w-full flex justify-start">
            <a href="admin.php" class="glass px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 hover:bg-white/5 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left-long"></i> Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="glass p-6 rounded-[2rem] border-blue-500/20 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 text-2xl shadow-lg shadow-blue-500/10"><i class="fa-solid fa-users"></i></div>
                <div>
                    <p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">User Ecosystem</p>
                    <h3 class="text-2xl font-black italic"><?= $totalUsers ?> <span class="text-sm font-medium text-slate-500 tracking-normal">Registered Nodes</span></h3>
                </div>
            </div>
            
            <div class="glass p-6 rounded-[2rem] border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Database Status: <span class="text-emerald-500">Synchronized</span></p>
                </div>
                <div class="text-[9px] font-black text-slate-600 tracking-[0.2em] italic uppercase">v2.0 Beta Cluster</div>
            </div>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 text-[10px] font-black uppercase tracking-[0.4em] text-slate-500">
                            <th class="p-6">User Profile</th>
                            <th class="p-6">Contact Endpoint</th>
                            <th class="p-6">Joined Date</th>
                            <th class="p-6 text-right">Operation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if(count($users) > 0): ?>
                        <?php foreach($users as $row): 
                            // ইউজারের প্রোফাইল পিকচার লজিক
                            $profile_img = !empty($row['u_image']) ? $row['u_image'] : "https://ui-avatars.com/api/?name=".urlencode($row['u_name'])."&background=0f172a&color=3b82f6&bold=true";
                        ?>
                        <tr class="hover:bg-white/[0.02] transition-all group">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <img src="<?= $profile_img ?>" class="w-12 h-12 rounded-2xl object-cover border border-white/10 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-[#0f172a] rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="text-white font-black italic tracking-tight text-base uppercase"><?= htmlspecialchars($row['u_name']) ?></p>
                                        <p class="text-[9px] text-slate-500 font-bold tracking-widest uppercase">Node ID: #USR-<?= $row['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-2 text-slate-300">
                                    <i class="fa-solid fa-envelope text-[10px] text-blue-500"></i>
                                    <span class="font-semibold text-sm"><?= htmlspecialchars($row['u_email']) ?></span>
                                </div>
                            </td>
                            <td class="p-6 text-slate-500 text-xs font-medium italic">
                                <i class="fa-regular fa-calendar-check mr-1.5"></i> <?= date('d M, Y', strtotime($row['u_created_at'] ?? 'now')) ?>
                            </td>
                            <td class="p-6 text-right">
                                <a href="<?= $_SERVER['PHP_SELF'] ?>?delete_id=<?= $row['id'] ?>" 
                                   onclick="return confirm('WARNING: Terminating this user node will permanently erase their data from the matrix. Proceed?');" 
                                   class="inline-flex w-10 h-10 glass rounded-xl items-center justify-center text-red-500 hover:bg-red-600 hover:text-white transition-all border border-red-500/10">
                                    <i class="fa-solid fa-user-minus text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-20 text-center">
                                <p class="text-slate-600 font-black italic tracking-[0.5em] uppercase text-xs">No active nodes detected in directory</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center px-10 text-[9px] font-bold text-slate-600 uppercase tracking-[0.5em]">
            <span>Security Layer: Active</span>
            <span>Cluster Date: <?= date('Y-m-d H:i') ?></span>
        </div>
    </main>

</body>
</html>