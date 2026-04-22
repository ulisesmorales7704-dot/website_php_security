<?php 
// ১. ডাটাবেস কানেকশন গ্লোবাল করা যাতে ড্যাশবোর্ড থেকে পায়
global $pdo;

// যদি কোনো কারণে $pdo না পায়, তবে সরাসরি db.php থেকে কানেক্ট করবে
if (!isset($pdo)) {
    require 'db.php';
}
?>

<style>
    /* Premium Studio Glass Design - Matches Home Page */
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        isolation: isolate;
    }

    .glass-card:hover {
        transform: scale(1.03);
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }

    /* Modal Styling - Unchanged */
    #serviceModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: rgba(2, 6, 23, 0.9);
        backdrop-filter: blur(12px);
    }

    .modal-animate {
        animation: modalFade 0.3s ease-out;
    }

    @keyframes modalFade {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM services WHERE status = 'available' ORDER BY id DESC");
        $services = $stmt->fetchAll();

        if (count($services) > 0) {
            foreach ($services as $row) {
    ?>
    <div class="glass-card flex flex-col rounded-[2.5rem] overflow-hidden group">
        
        <div class="relative h-48 w-full overflow-hidden shrink-0">
            <img src="<?= htmlspecialchars($row['image']) ?>" 
                 alt="Service Image" 
                 onerror="this.src='https://via.placeholder.com/400x300/0f172a/64748b?text=Service+Image'"
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
            
            <div class="absolute top-5 right-5 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">Available</span>
            </div>
        </div>
        
        <div class="p-8 flex flex-col flex-grow">
            <h3 class="text-xl font-bold text-white mb-2 tracking-tight group-hover:text-blue-400 transition-colors">
                <?= htmlspecialchars($row['title']) ?>
            </h3>
            <p class="text-slate-400 text-[13px] mb-8 line-clamp-2 leading-relaxed opacity-80">
                <?= htmlspecialchars($row['description']) ?>
            </p>
            
            <div class="mt-auto flex justify-between items-center">
                <div class="text-xs font-medium text-slate-300">
                    <span class="block text-blue-500 font-black text-xl mb-0.5">$<?= number_format($row['price'], 2) ?></span>
                    <span class="text-slate-500 text-[10px] uppercase tracking-widest flex items-center gap-1">
                        <i class="fa-solid fa-clock"></i> <?= htmlspecialchars($row['delivery_time']) ?> Days
                    </span>
                </div>
                
                <a href="view_more.php?id=<?= $row['id'] ?>" 
                   class="bg-[#1e293b] hover:bg-blue-600 text-white px-6 py-3 rounded-xl text-[11px] font-bold uppercase tracking-widest border border-white/5 transition-all shadow-lg">
                    View More
                </a>
            </div>
        </div>
    </div>
    <?php 
            }
        } else {
            echo "<div class='col-span-full py-20 text-center text-slate-500'>No services available at the moment.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='col-span-full p-6 glass-card text-red-400'>Error loading services: " . $e->getMessage() . "</div>";
    }
    ?>
</div>

<div id="serviceModal">
    <div class="modal-animate flex flex-col lg:flex-row gap-6 max-w-5xl w-full">
        <div class="glass-card flex-[2.5] p-8 md:p-12 rounded-[2.5rem] relative border-blue-500/20 bg-[#0f172a]/95">
            <button onclick="closeModal()" class="absolute top-8 right-8 text-slate-500 hover:text-white transition">
                <i class="fa-solid fa-circle-xmark text-3xl"></i>
            </button>

            <h2 id="mTitle" class="text-4xl font-black text-white mb-6 bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent"></h2>
            <p id="mDesc" class="text-slate-400 text-lg mb-10 leading-relaxed"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Premium Assets Included</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Commercial Use License</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> 24/7 Priority Support</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Multiple Revisions</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Source Files Provided</div>
            </div>
        </div>

        <div class="glass-card flex-1 p-8 rounded-[2.5rem] flex flex-col justify-center items-center text-center border-emerald-500/20 bg-[#0f172a]/95">
            <div class="p-4 bg-emerald-500/10 rounded-full mb-4">
                <i class="fa-solid fa-wallet text-2xl text-emerald-400"></i>
            </div>
            <span class="text-slate-500 uppercase text-[11px] tracking-widest font-bold mb-1">Total Package</span>
            <div id="mPrice" class="text-5xl font-black text-white mb-2"></div>
            <div id="mTime" class="text-slate-400 text-xs mb-10 font-medium italic"></div>
            
            <form action="process_order.php" method="POST" class="w-full">
                <input type="hidden" name="service_id" id="mId">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-blue-900/40 hover:scale-[1.02] active:scale-[0.98]">
                    Order Now
                </button>
            </form>
            <p class="text-[10px] text-slate-500 mt-4 px-4 leading-tight">Securely order this service using our encrypted portal.</p>
        </div>
    </div>
</div>

<script>
    // These functions remain for dashboard use elsewhere or modal support
    function showDetails(data) {
        document.getElementById('mTitle').innerText = data.title;
        document.getElementById('mDesc').innerText = data.description;
        document.getElementById('mPrice').innerText = '$' + parseFloat(data.price).toLocaleString();
        document.getElementById('mTime').innerText = 'Expected delivery in ' + data.delivery_time + ' days';
        document.getElementById('mId').value = data.id;
        
        const modal = document.getElementById('serviceModal');
        modal.style.display = 'flex'; 
        document.body.style.overflow = 'hidden'; 
    }

    function closeModal() {
        const modal = document.getElementById('serviceModal');
        modal.style.display = 'none'; 
        document.body.style.overflow = 'auto'; 
    }

    window.onclick = function(event) {
        const modal = document.getElementById('serviceModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>