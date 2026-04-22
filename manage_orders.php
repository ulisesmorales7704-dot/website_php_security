<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
session_start();
require 'db.php';

// ২. স্ট্যাটাস আপডেট লজিক
if (isset($_GET['update_status_id'])) {
    try {
        $req_id = $_GET['update_status_id'];
        $new_status = $_GET['new_status'];
        $updateStmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $updateStmt->execute([$new_status, $req_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) { die("Update Failed: " . $e->getMessage()); }
}

// ৩. ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        $pdo->prepare("DELETE FROM requests WHERE id = ?")->execute([$delete_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) { die("Deletion Failed: " . $e->getMessage()); }
}

// ৪. ডাটা নিয়ে আসা
try {
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
    $activeOrders = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'active'")->fetchColumn();
    $completedOrders = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'completed'")->fetchColumn();

    $query = "SELECT requests.*, services.title as service_name, services.price, user.u_name as username, user.u_email as email 
              FROM requests
              INNER JOIN services ON requests.service_id = services.id 
              INNER JOIN user ON requests.user_id = user.id 
              ORDER BY requests.order_date DESC";
    $stmt = $pdo->query($query);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Matrix | Final Production</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; color: #f1f5f9; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        select option { background: #0f172a; color: white; }
        select:disabled { opacity: 0.8; cursor: not-allowed; border-color: rgba(16, 185, 129, 0.3); }
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
            <div class="glass p-6 rounded-[2rem] border-orange-500/20 flex items-center gap-5 text-orange-400">
                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center text-xl"><i class="fa-solid fa-clock"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Pending Queue</p><h3 class="text-xl font-black italic"><?= $pendingOrders ?> Requests</h3></div>
            </div>
            <div class="glass p-6 rounded-[2rem] border-blue-500/20 flex items-center gap-5 text-blue-400">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-xl"><i class="fa-solid fa-spinner animate-spin-slow"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Active Cycle</p><h3 class="text-xl font-black italic"><?= $activeOrders ?> Processing</h3></div>
            </div>
            <div class="glass p-6 rounded-[2rem] border-emerald-500/20 flex items-center gap-5 text-emerald-400">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-xl"><i class="fa-solid fa-check-double"></i></div>
                <div><p class="text-[9px] uppercase font-black text-slate-500 tracking-[0.3em]">Completed Node</p><h3 class="text-xl font-black italic"><?= $completedOrders ?> Delivered</h3></div>
            </div>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/5 text-[10px] font-black uppercase tracking-[0.4em] text-slate-500">
                            <th class="p-6">Client Identity</th>
                            <th class="p-6">Service Detail</th>
                            <th class="p-6">Flow Status</th>
                            <th class="p-6 text-right">Delivery Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($orders as $row): 
                            $st = strtolower($row['status']); 
                            $statusColor = ($st == 'active') ? 'text-blue-400' : (($st == 'completed') ? 'text-emerald-400' : 'text-orange-400');
                            $isCompleted = ($st == 'completed'); // লক করার কন্ডিশন
                        ?>
                        <tr class="hover:bg-white/[0.02] transition-all <?= $isCompleted ? 'bg-emerald-500/[0.01]' : '' ?>">
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="text-white font-black italic tracking-tight text-base"><?= htmlspecialchars($row['username']) ?></span>
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">ID: #REQ-0<?= $row['id'] ?></span>
                                </div>
                            </td>
                            <td class="p-6">
                                <p class="text-slate-200 font-semibold"><?= htmlspecialchars($row['service_name']) ?></p>
                                <p class="text-blue-400 font-black italic text-xs">$<?= number_format($row['price'], 2) ?></p>
                            </td>
                            <td class="p-6">
                                <select onchange="confirmStatusChange(<?= $row['id'] ?>, this.value, '<?= $st ?>')" 
                                        <?= $isCompleted ? 'disabled' : '' ?>
                                        class="bg-transparent <?= $statusColor ?> text-[10px] font-black uppercase tracking-widest border border-white/10 rounded-full px-4 py-2 cursor-pointer outline-none focus:border-blue-500 transition-all">
                                    
                                    <option value="pending" <?= $st == 'pending' ? 'selected' : '' ?> <?= ($st != 'pending') ? 'disabled style="display:none;"' : '' ?>>Pending</option>
                                    <option value="active" <?= $st == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="completed" <?= $st == 'completed' ? 'selected' : '' ?> <?= $isCompleted ? '' : 'disabled style="display:none;"' ?>>Completed</option>
                                </select>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex justify-end items-center gap-3">
                                    <?php if($st == 'pending'): ?>
                                        <span class="text-[9px] uppercase font-bold text-slate-600 italic">Awaiting Action</span>
                                    <?php else: ?>
                                        <?php if($st == 'active'): ?>
                                            <button onclick="sendLink(<?= $row['id'] ?>)" class="px-4 py-2 glass rounded-xl text-blue-400 hover:bg-blue-600 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest border border-blue-500/20">
                                                <i class="fa-solid fa-paper-plane mr-2"></i> Send Link
                                            </button>
                                        <?php elseif($isCompleted): ?>
                                            <button onclick="sendLink(<?= $row['id'] ?>)" class="px-4 py-2 glass rounded-xl text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                                                <i class="fa-solid fa-arrows-rotate mr-2"></i> Update Doc
                                            </button>
                                            <a href="<?= htmlspecialchars($row['delivery_link']) ?>" target="_blank" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-slate-400 hover:text-white transition-all border border-white/5">
                                                <i class="fa-solid fa-link text-[10px]"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if(!$isCompleted): ?>
                                    <a href="<?= $_SERVER['PHP_SELF'] ?>?delete_id=<?= $row['id'] ?>" onclick="return confirm('Attention: Terminate this order node?');" class="inline-flex w-10 h-10 glass rounded-xl items-center justify-center text-red-500 hover:bg-red-600 hover:text-white transition-all border border-red-500/10 ml-2">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                    <?php else: ?>
                                        <div class="w-10 h-10 glass rounded-xl flex items-center justify-center text-emerald-500/30 border border-emerald-500/10 cursor-help" title="Completed orders are locked">
                                            <i class="fa-solid fa-lock text-[10px]"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    function confirmStatusChange(id, newValue, oldStatus) {
        if (newValue === 'active' && oldStatus === 'pending') {
            if (confirm("Activate this order? Once activated, it cannot be reverted to pending.")) {
                window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?update_status_id=' + id + '&new_status=' + newValue;
            } else { location.reload(); }
        }
    }

    function sendLink(requestId) {
        const link = prompt("Enter the Delivery Link. Status will be locked to COMPLETED:");
        if (!link) return;
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('delivery_link', link);
        fetch('update_link.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "Success") {
                alert("Order Locked & Document Synced.");
                location.reload();
            } else { alert("Failed: " + data); }
        })
        .catch(err => alert("Matrix Connection Error."));
    }
    </script>
</body>
</html>