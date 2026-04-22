<?php
session_start();
require 'db.php';

$message = "";

// 1. Handle Manual Slot Assignment
if (isset($_POST['update_slot'])) {
    $id = (int)$_POST['service_id'];
    $slot = (int)$_POST['slot'];

    try {
        // If assigning a manual slot (1, 2, or 3), clear that slot from any other service first
        if ($slot > 0) {
            $clear = $pdo->prepare("UPDATE services SET top = 0 WHERE top = ?");
            $clear->execute([$slot]);
        }

        $update = $pdo->prepare("UPDATE services SET top = ? WHERE id = ?");
        $update->execute([$slot, $id]);
        $message = "Matrix Synchronized Successfully";
    } catch (PDOException $e) {
        $message = "System Error: " . $e->getMessage();
    }
}

// 2. AUTO-SELECT LOGIC
// Check if any services are currently assigned to slots 1, 2, or 3
$checkTop = $pdo->query("SELECT COUNT(*) FROM services WHERE top > 0")->fetchColumn();

if ($checkTop == 0) {
    // If NO services are selected, automatically pick the latest 3 and assign them slots 1, 2, and 3
    $latest = $pdo->query("SELECT id FROM services ORDER BY id DESC LIMIT 3")->fetchAll();
    
    $slotCounter = 1;
    foreach ($latest as $s) {
        $autoAssign = $pdo->prepare("UPDATE services SET top = ? WHERE id = ?");
        $autoAssign->execute([$slotCounter, $s['id']]);
        $slotCounter++;
    }
    $message = "Auto-Selection Active: Latest 3 services assigned.";
}

// 3. Fetch All Services for the list
$services = $pdo->query("SELECT id, title, price, top FROM services ORDER BY top DESC, id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Matrix | Secure Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f1f5f9;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
        }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); }
        select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-10 flex justify-center">

    <main class="w-full max-w-5xl space-y-8">
        
        <div class="glass w-full rounded-[2.5rem] p-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-6">
                <a href="admin.php" class="glass w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-white/10 transition-all border-white/10">
                    <i class="fa-solid fa-arrow-left text-blue-500"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-white italic tracking-tight uppercase">Featured <span class="text-blue-500">Matrix</span></h2>
                    <p class="text-slate-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-1">Slot Allocation System</p>
                </div>
            </div>
            <?php if($message): ?>
                <div class="glass px-6 py-3 rounded-2xl border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-widest">
                    <i class="fa-solid fa-bolt-lightning mr-2"></i> <?= $message ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5">
                        <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500">Service Metadata</th>
                        <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500 text-center">Status</th>
                        <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500 text-right">Operation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach($services as $row): ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="p-6">
                            <p class="text-white font-bold tracking-tight"><?= htmlspecialchars($row['title']) ?></p>
                            <p class="text-[10px] text-slate-500 font-mono italic">REF: #SRV-<?= $row['id'] ?> | Valuation: $<?= number_format($row['price'], 2) ?></p>
                        </td>
                        <td class="p-6 text-center">
                            <?php if($row['top'] > 0): ?>
                                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-600/20 border border-blue-500/30 text-blue-400 text-[10px] font-black uppercase tracking-widest shadow-xl">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                    Slot <?= $row['top'] ?>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-700 text-[10px] font-bold uppercase tracking-widest">Idle State</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6 text-right">
                            <form action="" method="POST" class="flex justify-end">
                                <input type="hidden" name="service_id" value="<?= $row['id'] ?>">
                                <select name="slot" onchange="this.form.submit()" class="bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-[11px] font-bold uppercase tracking-widest text-white outline-none w-36 cursor-pointer hover:border-blue-500 transition-all shadow-inner">
                                    <option value="0" <?= $row['top'] == 0 ? 'selected' : '' ?>>Deactivate</option>
                                    <option value="1" <?= $row['top'] == 1 ? 'selected' : '' ?>>Assign Slot 1</option>
                                    <option value="2" <?= $row['top'] == 2 ? 'selected' : '' ?>>Assign Slot 2</option>
                                    <option value="3" <?= $row['top'] == 3 ? 'selected' : '' ?>>Assign Slot 3</option>
                                </select>
                                <input type="hidden" name="update_slot" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center">
            <p class="text-[9px] text-slate-600 uppercase font-bold tracking-[0.5em]">System: Auto-redundancy enabled. If all slots empty, latest 3 inherit positions.</p>
        </div>
    </main>

</body>
</html>