<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// ২. ডাটাবেস থেকে রিয়েল ডাটা কাউন্ট করা
try {
    $serviceCount = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $devCount     = $pdo->query("SELECT COUNT(*) FROM developers")->fetchColumn();
    
    $pendingCount   = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
    $activeCount    = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'active'")->fetchColumn(); 
    $completedCount = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'completed'")->fetchColumn();
    $totalOrders    = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();

} catch (PDOException $e) {
    $serviceCount = $devCount = $pendingCount = $activeCount = $completedCount = $totalOrders = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Studio Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
            background-attachment: fixed;
            color: #f1f5f9;
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .sidebar-link { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            transform: translateX(8px);
        }
        .active-link {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-right: 4px solid #3b82f6;
        }

        /* Mobile Sidebar Transitions */
        #sidebar { transition: transform 0.3s ease-in-out; }
        .sidebar-open { transform: translateX(0) !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col lg:flex-row p-4 md:p-6 gap-6">

    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] hidden lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-6 left-6 w-72 glass rounded-[2.5rem] flex flex-col z-[100] -translate-x-[120%] lg:translate-x-0 lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)] overflow-hidden transition-transform duration-300">
        <div class="p-10 flex justify-between items-center">
            <h1 class="text-2xl font-black tracking-tighter italic uppercase">Studio <span class="text-blue-500">Admin</span></h1>
            <button onclick="toggleSidebar()" class="lg:hidden text-white text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <nav class="flex-grow px-6 space-y-3 mt-4 overflow-y-auto">
            <a href="admin.php" class="sidebar-link active-link flex items-center p-4 rounded-2xl font-bold text-sm tracking-tight">
                <i class="fa-solid fa-chart-pie mr-4 w-5 text-lg"></i> Dashboard
            </a>
            <a href="manage_services.php" class="sidebar-link flex items-center p-4 rounded-2xl text-slate-400 font-bold text-sm tracking-tight">
                <i class="fa-solid fa-layer-group mr-4 w-5 text-lg"></i> Services
            </a>
            <a href="manage_top_services.php" class="sidebar-link flex items-center p-4 rounded-2xl text-slate-400 font-bold text-sm tracking-tight">
                <i class="fa-solid fa-star mr-4 w-5 text-lg"></i> Top Services
            </a>
            <a href="manage_developers.php" class="sidebar-link flex items-center p-4 rounded-2xl text-slate-400 font-bold text-sm tracking-tight">
                <i class="fa-solid fa-code mr-4 w-5 text-lg"></i> Developers
            </a>
            <a href="manage_orders.php" class="sidebar-link flex items-center p-4 rounded-2xl text-slate-400 font-bold text-sm tracking-tight">
                <i class="fa-solid fa-cart-shopping mr-4 w-5 text-lg"></i> Orders
            </a>
            <a href="manage_users.php" class="sidebar-link flex items-center p-4 rounded-2xl text-slate-400 font-bold text-sm tracking-tight">
                <i class="fa-solid fa-users mr-4 w-5 text-lg"></i> Clients
            </a>
        </nav>

        <div class="p-8 border-t border-white/5">
            <a href="logout.php" class="flex items-center text-red-400 text-xs font-black uppercase tracking-[0.2em] p-4 hover:bg-red-500/10 rounded-2xl transition-all">
                <i class="fa-solid fa-arrow-right-from-bracket mr-4"></i> Logout
            </a>
        </div>
    </aside>

    <main class="flex-grow space-y-8 w-full">
        <div class="glass w-full rounded-[2.5rem] p-6 md:p-8 flex flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden w-12 h-12 glass rounded-2xl flex items-center justify-center text-blue-500 text-xl">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div>
                    <h2 class="text-xl md:text-3xl font-black text-white italic tracking-tight">Executive Overview</h2>
                    <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-1 hidden sm:block">Real-time control matrix</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4 md:gap-6 glass px-4 md:px-6 py-2 md:py-3 rounded-2xl border-white/10 bg-white/5">
                <div class="text-right hidden xs:block">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Master Admin</p>
                    <p class="text-xs md:text-sm font-bold text-white italic">Fahim Shakil</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center font-black shadow-lg shadow-blue-500/40 text-lg md:text-xl">A</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <a href="add_service.php" class="glass p-8 md:p-10 rounded-[2.5rem] relative overflow-hidden group hover:scale-[1.03] transition-all border-blue-500/20">
                <div class="absolute -right-6 -top-6 text-blue-500/5 text-8xl md:text-9xl group-hover:scale-110 transition-transform"><i class="fa-solid fa-plus-circle"></i></div>
                <p class="text-slate-500 text-[10px] uppercase tracking-[0.3em] font-black mb-4">Marketplace</p>
                <h3 class="text-xl md:text-2xl font-black italic text-white flex items-center gap-3">Add Service <i class="fa-solid fa-chevron-right text-blue-500 text-sm"></i></h3>
                <div class="mt-6 text-blue-400 text-[11px] font-bold uppercase"><?= $serviceCount ?> Active Items</div>
            </a>

            <a href="add_developer.php" class="glass p-8 md:p-10 rounded-[2.5rem] relative overflow-hidden group hover:scale-[1.03] transition-all border-emerald-500/20">
                <div class="absolute -right-6 -top-6 text-emerald-500/5 text-8xl md:text-9xl group-hover:scale-110 transition-transform"><i class="fa-solid fa-user-plus"></i></div>
                <p class="text-slate-500 text-[10px] uppercase tracking-[0.3em] font-black mb-4">Talent Matrix</p>
                <h3 class="text-xl md:text-2xl font-black italic text-white flex items-center gap-3">Add Developer <i class="fa-solid fa-chevron-right text-emerald-500 text-sm"></i></h3>
                <div class="mt-6 text-emerald-400 text-[11px] font-bold uppercase"><?= $devCount ?> Team Members</div>
            </a>

            <a href="manage_reviews.php" class="glass p-8 md:p-10 rounded-[2.5rem] relative overflow-hidden group hover:scale-[1.03] transition-all border-red-500/20">
                <div class="absolute -right-6 -top-6 text-red-500/5 text-8xl md:text-9xl group-hover:scale-110 transition-transform"><i class="fa-solid fa-trash-can"></i></div>
                <p class="text-slate-500 text-[10px] uppercase tracking-[0.3em] font-black mb-4">Log Cleanup</p>
                <h3 class="text-xl md:text-2xl font-black italic text-white flex items-center gap-3">Remove Review <i class="fa-solid fa-chevron-right text-red-500 text-sm"></i></h3>
                <div class="mt-6 text-red-400 text-[11px] font-bold uppercase">Feedback Management</div>
            </a>
        </div>

        <div class="glass rounded-[2.5rem] p-8 md:p-10 flex flex-col xl:flex-row justify-between items-center bg-white/[0.01] gap-6 border-white/5">
            <div class="text-center xl:text-left">
                <h4 class="font-black text-2xl italic uppercase tracking-tighter">System Pulse</h4>
                <p class="text-slate-500 text-[10px] font-bold tracking-[0.3em] uppercase mt-1">Live surveillance active • <?= date('H:i:s T') ?></p>
            </div>
            <div class="flex flex-wrap justify-center gap-4">
                <div class="glass px-4 md:px-6 py-3 md:py-4 rounded-2xl text-orange-400 text-[10px] font-black uppercase tracking-[0.2em] border-orange-500/20 flex items-center gap-3 shadow-xl shadow-orange-950/20">
                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                    Pending: <?= $pendingCount ?>
                </div>
                <div class="glass px-4 md:px-6 py-3 md:py-4 rounded-2xl text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] border-blue-500/20 flex items-center gap-3 shadow-xl shadow-blue-950/20">
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                    Active: <?= $activeCount ?>
                </div>
                <div class="glass px-4 md:px-6 py-3 md:py-4 rounded-2xl text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em] border-emerald-500/20 flex items-center gap-3 shadow-xl shadow-emerald-950/20">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    Completed: <?= $completedCount ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>
</html>