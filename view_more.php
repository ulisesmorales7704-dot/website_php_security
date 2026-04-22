<?php
// ১. সেশন স্টার্ট করা
session_start();

require 'db.php'; 

// ২. ডাটা রিটার্নিং লজিক
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;

if ($id > 0) {
    // FIXED LOGIC: First, try the main 'services' table (Primary source)
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    // If not found in main services, check 'top_services'
    if (!$service) {
        $stmt = $pdo->prepare("SELECT * FROM top_services WHERE id = ?");
        $stmt->execute([$id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// If no service found in either table, redirect
if (!$service) { 
    header("Location: home.php"); 
    exit; 
}

// ৩. ডায়নামিক ব্যাক ইউআরএল লজিক
$back_url = isset($_SESSION['user_id']) ? "dashboard.php" : "services.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> - Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: #020617; 
            color: white; 
            font-family: 'Inter', sans-serif; 
            background-image: radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                              radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                              radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
            background-attachment: fixed;
        }
        .dark-glass {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12">

    <div class="max-w-4xl mx-auto">
        
        <a href="<?= $back_url ?>" class="inline-flex items-center text-slate-500 hover:text-blue-400 mb-5 text-[12px] uppercase tracking-widest font-bold transition group">
            <i class="fa-solid fa-chevron-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to <?= isset($_SESSION['user_id']) ? "Dashboard" : "services" ?>
        </a>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            
            <div class="dark-glass flex-[2] p-8 rounded-[2rem] border-white/5">
                
                <div class="relative h-[220px] md:h-[320px] w-full rounded-2xl overflow-hidden mb-8 shadow-2xl">
                    <?php 
                        $img_path = (!empty($service['image'])) ? $service['image'] : 'default_service.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($img_path) ?>" 
                         onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800'"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#020617]/40 to-transparent"></div>
                </div>

                <h2 class="text-3xl md:text-4xl font-black text-white mb-4 tracking-tight italic">
                    <?= htmlspecialchars($service['title']) ?>
                </h2>
                
                <p class="text-slate-400 text-sm md:text-base mb-8 leading-relaxed opacity-90">
                    <?= nl2br(htmlspecialchars($service['description'])) ?>
                </p>

                <?php 
                if (!empty(trim($service['features']))): 
                    $feature_list = explode(',', $service['features']);
                ?>
                <div class="mb-10 space-y-3 bg-white/5 p-6 rounded-2xl border border-white/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-500 mb-2">Key Specifications</h4>
                    <?php foreach ($feature_list as $point): 
                        if (trim($point) !== ""):
                    ?>
                    <div class="flex items-center gap-3 text-slate-300 text-sm">
                        <i class="fa-solid fa-circle-check text-blue-500 text-[12px]"></i>
                        <span><?= htmlspecialchars(trim($point)) ?></span>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="pt-6 border-t border-white/10 flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-blue-400 text-xs"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Secure Delivery</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-headset text-blue-400 text-xs"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Global Support</span>
                    </div>
                </div>
            </div>

            <div class="dark-glass w-full md:w-[300px] p-8 rounded-[2rem] text-center border-white/5 md:sticky md:top-10">
                <p class="text-slate-500 uppercase text-[10px] tracking-[0.3em] font-black mb-2">Total Price</p>
                <div class="text-5xl font-black text-white mb-2 tracking-tighter italic">
                    $<?= number_format($service['price'], 2) ?>
                </div>
                <div class="text-blue-400 text-[12px] font-bold mb-10 flex items-center justify-center gap-2 uppercase tracking-widest">
                    <i class="fa-regular fa-clock"></i> 
                    <?= htmlspecialchars($service['delivery_time']) ?> Day Turnaround
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="process_order.php" method="POST" class="w-full">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-2xl shadow-blue-900/40">
                            Confirm Order
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="inline-block w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl text-[12px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-2xl shadow-blue-900/40 text-center">
                        Login to Purchase
                    </a>
                <?php endif; ?>
                
                <div class="mt-8 opacity-40">
                    <p class="text-[9px] uppercase font-bold tracking-[0.2em] text-slate-500">Encrypted Transaction</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>