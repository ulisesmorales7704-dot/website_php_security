<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
session_start();
require 'db.php';

// ২. এভেলেবিলিটি টগল লজিক (স্ট্যাটাস পরিবর্তন)
if (isset($_GET['toggle_id'])) {
    try {
        $tid = $_GET['toggle_id'];
        $current_status = $_GET['current'];
        $new_status = ($current_status == 'available') ? 'unavailable' : 'available';
        
        $updateStmt = $pdo->prepare("UPDATE services SET status = ? WHERE id = ?");
        $updateStmt->execute([$new_status, $tid]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) { die("Toggle Failed: " . $e->getMessage()); }
}

// ৩. ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$delete_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) { die("Deletion Failed: " . $e->getMessage()); }
}

// ৪. কাউন্টার ও লিস্ট ডাটা নিয়ে আসা
try {
    $totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $availableServices = $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'available'")->fetchColumn();
    $hiddenServices = $pdo->query("SELECT COUNT(*) FROM services WHERE status = 'unavailable'")->fetchColumn();

    $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Inventory | Admin Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; color: #f1f5f9; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .toggle-switch { width: 40px; height: 20px; background: rgba(255,255,255,0.1); border-radius: 20px; position: relative; cursor: pointer; transition: 0.3s; }
        .toggle-circle { width: 14px; height: 14px; background: white; border-radius: 50%; position: absolute; top: 3px; left: 4px; transition: 0.3s; }
        .available .toggle-switch { background: #10b981; }
        .available .toggle-circle { left: 22px; }
    </style>
</head>
<body class="min-h-screen p-6 md:p-10 flex flex-col items-center">

    <main class="w-full max-w-6xl space-y-6">
        
        <div class="w-full flex justify-start">
            <a href="admin.php" class="glass px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 hover:bg-white/5 transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left-long"></i> Back to Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass p-6 rounded-[2rem] border-white/5 flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-400 text-xl shadow-lg"><i class="fa-solid fa-layer-group"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Total Matrix</p><h3 class="text-xl font-black italic"><?= $totalServices ?> Items</h3></div>
            </div>
            <div class="glass p-6 rounded-[2rem] border-white/5 flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-xl shadow-lg"><i class="fa-solid fa-bolt"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Live Node</p><h3 class="text-xl font-black italic text-emerald-400"><?= $availableServices ?> Available</h3></div>
            </div>
            <div class="glass p-6 rounded-[2rem] border-white/5 flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-400 text-xl shadow-lg"><i class="fa-solid fa-eye-slash"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Offline</p><h3 class="text-xl font-black italic text-red-400"><?= $hiddenServices ?> Hidden</h3></div>
            </div>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/5 text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">
                            <th class="p-6">Service Identity</th>
                            <th class="p-6">Finance Node</th>
                            <th class="p-6">Visibility</th>
                            <th class="p-6 text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($services as $row): 
                            // ইমেজ URL লজিক
                            $img_display = !empty($row['image']) ? $row['image'] : "https://placehold.co/600x400/1e293b/60a5fa?text=Service";
                        ?>
                        <tr class="hover:bg-white/[0.02] transition-all">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative group">
                                        <img src="<?= htmlspecialchars($img_display) ?>" class="w-16 h-12 rounded-xl object-cover border border-white/10 shadow-lg group-hover:scale-105 transition-transform" onerror="this.src='https://placehold.co/600x400/1e293b/ef4444?text=URL+Error'">
                                    </div>
                                    <div>
                                        <p class="text-white font-black italic tracking-tight text-base"><?= htmlspecialchars($row['title']) ?></p>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">ID: #SRV-0<?= $row['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <p class="text-emerald-400 font-black italic text-base">$<?= number_format($row['price'], 2) ?></p>
                                <p class="text-[9px] text-slate-500 font-bold uppercase italic tracking-widest"><?= $row['delivery_time'] ?> Days Cycle</p>
                            </td>
                            <td class="p-6">
                                <a href="<?= $_SERVER['PHP_SELF'] ?>?toggle_id=<?= $row['id'] ?>&current=<?= $row['status'] ?>" 
                                   class="<?= ($row['status'] == 'available') ? 'available' : '' ?> flex items-center gap-3">
                                    <div class="toggle-switch shadow-lg">
                                        <div class="toggle-circle shadow-md"></div>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] <?= ($row['status'] == 'available') ? 'text-emerald-500' : 'text-slate-500' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </a>
                            </td>
                            <td class="p-6 text-right space-x-2">
                                <a href="update_service.php?id=<?= $row['id'] ?>" class="inline-flex w-10 h-10 glass rounded-xl items-center justify-center text-blue-400 hover:bg-blue-600 hover:text-white transition-all border border-blue-500/10"><i class="fa-solid fa-edit text-xs"></i></a>
                                
                                <a href="<?= $_SERVER['PHP_SELF'] ?>?delete_id=<?= $row['id'] ?>" onclick="return confirm('Attention: Terminate this service node?');" class="inline-flex w-10 h-10 glass rounded-xl items-center justify-center text-red-400 hover:bg-red-600 hover:text-white transition-all border border-red-500/10">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center px-10 text-[9px] font-bold text-slate-600 uppercase tracking-[0.5em]">
            <span>System Secure: AES-256</span>
            <span>Active Node: <?= date('d M Y') ?></span>
        </div>
    </main>

</body>
</html>