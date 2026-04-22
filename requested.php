<?php
// ১. ডাটাবেস কানেকশন গ্লোবাল করা যাতে ড্যাশবোর্ড থেকে পায়
global $pdo;

if (!isset($pdo)) {
    require 'db.php';
}
?>

<div class="max-w-5xl mx-auto px-4 md:px-0">
    <h2 class="text-xl md:text-2xl font-black text-white mb-6 md:mb-8 tracking-tight">Requested Services</h2>

    <div class="hidden md:block dark-glass rounded-[2rem] overflow-hidden border border-white/5 shadow-2xl bg-white/[0.02] backdrop-blur-md">
        <table class="w-full text-left border-collapse">
            <thead class="text-[10px] text-slate-500 uppercase tracking-[0.2em] bg-white/5">
                <tr>
                    <th class="p-6 font-black">Service Information</th>
                    <th class="p-6 font-black">Current Status</th>
                    <th class="p-6 font-black text-right">Delivery Progress</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT r.*, s.title 
                        FROM requests r 
                        JOIN services s ON r.service_id = s.id 
                        WHERE r.user_id = ? AND r.status != 'completed'
                        ORDER BY r.id DESC
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $requests = $stmt->fetchAll();

                    if (count($requests) > 0) {
                        foreach ($requests as $row) {
                            $percent = 0;
                            if ($row['status'] == 'active' && !empty($row['expiry_time'])) {
                                $start = strtotime($row['created_at'] ?? 'now');
                                $end = strtotime($row['expiry_time']);
                                $now = time();
                                $total = $end - $start;
                                $elapsed = $now - $start;
                                $percent = ($total > 0) ? round(($elapsed / $total) * 100) : 0;
                                $percent = max(0, min(100, $percent));
                            }
                ?>
                <tr class="border-b border-white/5 hover:bg-white/[0.03] transition-colors group">
                    <td class="p-6"> 
                        <div class="flex flex-col">
                            <span class="text-white font-bold text-base group-hover:text-blue-400 transition-colors">
                                <?= htmlspecialchars($row['title']) ?>
                            </span>
                            <span class="text-[9px] text-slate-500 mt-1 font-mono uppercase tracking-widest">
                                ID: #<?= $row['id'] ?>
                            </span>
                        </div>
                    </td>
                    <td class="p-6">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border 
                            <?= $row['status']=='pending' ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' ?>">
                            <i class="fa-solid <?= $row['status']=='pending' ? 'fa-spinner fa-spin' : 'fa-bolt' ?> mr-2"></i>
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>
                    <td class="p-6 text-right font-mono">
                        <?php if ($row['status'] == 'active' && $row['expiry_time']): ?>
                            <div class="flex flex-col items-end w-48 ml-auto">
                                <span class="countdown text-blue-400 font-bold text-base drop-shadow-[0_0_10px_rgba(59,130,246,0.3)]" data-time="<?= $row['expiry_time'] ?>">--:--:--</span>
                                <div class="w-full h-1 bg-white/5 rounded-full mt-2 overflow-hidden border border-white/5">
                                    <div class="h-full bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.8)] transition-all duration-1000" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-slate-600 italic text-[10px] tracking-widest uppercase font-black opacity-50">Awaiting...</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } } else { echo '<tr><td colspan="3" class="p-16 text-center text-slate-500">No active requests found.</td></tr>'; } ?>
            </tbody>
        </table>
    </div>

    <div class="md:hidden space-y-4">
        <?php if (isset($requests) && count($requests) > 0): ?>
            <?php foreach ($requests as $row): 
                $percent = 0;
                if ($row['status'] == 'active' && !empty($row['expiry_time'])) {
                    $start = strtotime($row['created_at']);
                    $end = strtotime($row['expiry_time']);
                    $now = time();
                    $total = $end - $start;
                    $percent = ($total > 0) ? round((($now - $start) / $total) * 100) : 0;
                    $percent = max(0, min(100, $percent));
                }
            ?>
            <div class="dark-glass p-5 rounded-[1.5rem] border border-white/10 bg-white/[0.03] backdrop-blur-lg shadow-xl">
                <div class="flex justify-between items-start mb-4">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-white font-bold text-base truncate"><?= htmlspecialchars($row['title']) ?></h3>
                        <p class="text-[9px] text-slate-500 font-mono mt-1">ID: #<?= $row['id'] ?></p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black uppercase tracking-tighter border shrink-0
                        <?= $row['status']=='pending' ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' ?>">
                        <?= strtoupper($row['status']) ?>
                    </span>
                </div>

                <?php if ($row['status'] == 'active' && $row['expiry_time']): ?>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Progress</span>
                            <span class="countdown text-blue-400 font-bold text-sm" data-time="<?= $row['expiry_time'] ?>">--:--:--</span>
                        </div>
                        <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden border border-white/5">
                            <div class="h-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)] transition-all duration-1000" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="py-2 border-t border-white/5 mt-2">
                        <span class="text-slate-600 italic text-[10px] tracking-widest uppercase font-black opacity-50 flex items-center">
                            <i class="fa-regular fa-clock mr-2"></i> Awaiting activation...
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center p-10 text-slate-500 italic">No active requests.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateCountdowns() {
        document.querySelectorAll('.countdown').forEach(el => {
            const targetStr = el.dataset.time;
            if (!targetStr) return;

            const target = new Date(targetStr).getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff > 0) {
                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                let timerText = "";
                if (d > 0) timerText += `${d}d `;
                timerText += `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                
                el.innerText = timerText;
            } else {
                el.innerText = "Finalizing...";
                el.classList.replace('text-blue-400', 'text-emerald-400');
                // Progress bar update
                const container = el.closest('.flex-col, .space-y-2');
                const progressBar = container ? container.querySelector('.bg-blue-500') : null;
                if(progressBar) progressBar.style.width = '100%';
            }
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();
</script>

<?php } catch (PDOException $e) { /* Error handeling already inside view */ } ?>