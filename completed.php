<?php
// ১. ডাটাবেস ও সেশন হ্যান্ডলিং
global $pdo;
if (!isset($pdo)) { require 'db.php'; }

// সেশন থেকে ইউজার আইডি নিশ্চিত করা
$user_id = $_SESSION['user_id'] ?? 0;
?>

<style>
    .completed-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.7);
    }
    /* মোডাল স্টাইলগুলো রাখা হয়েছে যাতে ডিজাইনে কোনো ইমপ্যাক্ট না পড়ে */
    .glass-modal {
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(30px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .star-rating-modal {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 8px;
    }
    .star-rating-modal input { display: none; }
    .star-rating-modal label { font-size: 24px; color: #334155; cursor: pointer; transition: 0.2s; }
    .star-rating-modal input:checked ~ label,
    .star-rating-modal label:hover,
    .star-rating-modal label:hover ~ label { color: #fbbf24; }
</style>

<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-black text-white mb-8 tracking-tight italic uppercase">Completed <span class="text-emerald-500">Projects</span></h2>

    <div class="space-y-4"> 
        <?php
        try {
            // সার্ভিস এবং রিকোয়েস্ট জয়েন করে ডাটা আনা
            $stmt = $pdo->prepare("SELECT r.*, s.title, s.id as s_id FROM requests r JOIN services s ON r.service_id = s.id WHERE r.user_id = ? AND r.status = 'completed' ORDER BY r.id DESC");
            $stmt->execute([$user_id]);
            $results = $stmt->fetchAll();

            if (count($results) > 0) {
                foreach ($results as $row) {
                    // রিভিউ অলরেডি দেওয়া হয়েছে কি না চেক করা
                    $checkRev = $pdo->prepare("SELECT id FROM reviews WHERE request_id = ?");
                    $checkRev->execute([$row['id']]);
                    $reviewed = $checkRev->fetch();
        ?>
        
        <div class="completed-card p-5 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 group transition-all hover:border-emerald-500/30">
            <div class="flex-1 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-3 mb-2"> 
                    <span class="bg-emerald-500/10 text-emerald-400 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-widest border border-emerald-500/20">Finished</span>
                    <span class="text-slate-500 text-[10px] font-mono">#REQ-<?= $row['id'] ?></span>
                </div>
                <h3 class="text-lg font-bold text-white mb-3"><?= htmlspecialchars($row['title']) ?></h3> 
                
                <div class="flex justify-center md:justify-start">
                    <?php 
                    if (!empty($row['delivery_link'])): 
                        $dLink = $row['delivery_link'];
                        if (!preg_match("~^(?:f|ht)tps?://~i", $dLink)) {
                            $dLink = "https://" . $dLink;
                        }
                    ?>
                    <a href="<?= htmlspecialchars($dLink) ?>" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-blue-500/10 text-blue-400 text-[10px] font-bold border border-blue-500/20 hover:bg-blue-600 hover:text-white transition-all inline-flex items-center">
                        <i class="fa-solid fa-download mr-2 text-[12px]"></i> Access Project Files
                    </a>
                    <?php else: ?>
                        <span class="text-[9px] text-slate-600 italic font-bold uppercase tracking-widest">Processing Delivery Link...</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="w-full md:w-auto flex justify-center">
                <?php if (!$reviewed): ?>
                    <a href="submit_review.php?req_id=<?= $row['id'] ?>&serv_id=<?= $row['s_id'] ?>&title=<?= urlencode($row['title']) ?>" 
                       class="px-6 py-3 rounded-xl bg-emerald-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-emerald-500 transition-all active:scale-95 shadow-lg shadow-emerald-900/20 text-center">
                        Give Review
                    </a>
                <?php else: ?>
                    <div class="flex items-center gap-2 px-5 py-2.5 bg-white/5 rounded-xl border border-white/5">
                        <i class="fa-solid fa-heart text-emerald-500 text-[10px]"></i>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Reviewed</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php 
                } 
            } else {
                echo '<p class="text-center text-slate-500 py-10 font-bold italic uppercase tracking-widest text-xs">No completed projects found in your matrix.</p>';
            }
        } catch (PDOException $e) { 
            echo "<div class='p-4 bg-red-500/10 text-red-400 rounded-xl text-xs'>System Error: " . htmlspecialchars($e->getMessage()) . "</div>"; 
        } 
        ?>
    </div>
</div>

<div id="review-modal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4">
    <div class="glass-modal w-full max-w-sm p-8 rounded-[2.5rem] relative">
        <button onclick="closeReviewModal()" class="absolute top-6 right-6 text-slate-500">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
</div>

<script>
    // জাভাস্ক্রিপ্ট ফাংশনগুলো রাখা হয়েছে যাতে পেজ লোডে কোনো সমস্যা না হয়
    function openReviewModal(reqId, servId, title) {
        window.location.href = "submit_review.php?req_id=" + reqId + "&serv_id=" + servId + "&title=" + encodeURIComponent(title);
    }
    function closeReviewModal() {
        document.getElementById('review-modal').classList.add('hidden');
    }
</script>