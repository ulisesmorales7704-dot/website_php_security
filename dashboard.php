<?php
session_start();
if (file_exists('db.php')) {
    require 'db.php';
} else {
    die("Error: db.php file not found.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tab = $_GET['tab'] ?? 'market';
$userName = $_SESSION['user_name'] ?? 'User'; // u_name এর জায়গায় user_name ফিক্স করা হয়েছে আপনার আগের সেশন অনুযায়ী
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Studio Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');

        /* --- Updated Home Page Background --- */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a; 
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); 
            background-attachment: fixed; 
            min-height: 100vh;
            color: white;
            margin: 0;
            padding: 0;
        }

        /* --- Desktop Layout --- */
        .glass-sidebar-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 2.5rem;
            box-shadow: 20px 20px 50px rgba(0, 0, 0, 0.3);
            margin: 1.5rem;
            height: calc(100vh - 3rem);
            position: sticky;
            top: 1.5rem;
        }

        .glass-main-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 2.5rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            margin: 1.5rem 1.5rem 1.5rem 0;
            min-height: calc(100vh - 3rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* --- Mobile Responsive Fixes --- */
        @media (max-width: 768px) {
            body { overflow-x: hidden; }
            .glass-main-card { 
                margin: 0.75rem; 
                min-height: calc(100vh - 1.5rem); 
                border-radius: 1.5rem; 
            }
            .inner-nav-glass { padding: 0 1rem; }
        }

        /* Mobile Slide Menu */
        #mobile-menu {
            background: rgba(11, 15, 26, 0.98);
            backdrop-filter: blur(30px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        #mobile-menu.active {
            max-height: 400px;
            padding-bottom: 1.5rem;
        }

        .inner-nav-glass {
            background: rgba(255, 255, 255, 0.01);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            min-height: 80px;
        }

        .nav-link { transition: all 0.3s ease; }
        .nav-link:hover:not(.active-tab) { background: rgba(255, 255, 255, 0.05); }
        
        .active-tab {
            background: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
            border: 1px solid rgba(59, 130, 246, 0.2) !important;
        }

        .profile-chip { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            max-width: 180px;
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.2); border-radius: 10px; }
    </style>
</head>
<body class="flex flex-col md:flex-row">

    <div class="md:hidden flex items-center justify-between p-5 bg-white/5 backdrop-blur-xl border-b border-white/10 z-[110]">
        <h1 class="text-xl font-black italic tracking-tighter uppercase">Studio <span class="text-blue-500">Matrix</span></h1>
        <button id="menu-btn" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/5 text-white border border-white/10 active:scale-95 transition-transform">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
    </div>

    <div id="mobile-menu" class="md:hidden w-full z-[100] px-5">
        <nav class="flex flex-col gap-2 pt-4">
            <a href="?tab=market" class="p-4 rounded-xl flex items-center gap-4 text-sm font-bold <?= $tab=='market'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-store"></i> Marketplace
            </a>
            <a href="?tab=requested" class="p-4 rounded-xl flex items-center gap-4 text-sm font-bold <?= $tab=='requested'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Requested
            </a>
            <a href="?tab=completed" class="p-4 rounded-xl flex items-center gap-4 text-sm font-bold <?= $tab=='completed'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-circle-check"></i> Completed
            </a>
        </nav>
    </div>

    <aside class="hidden md:flex flex-col w-72 glass-sidebar-card p-8 shrink-0">
        <div class="mb-12 px-2">
            <h1 class="text-2xl font-black tracking-tighter italic uppercase">Studio <span class="text-blue-500">Matrix</span></h1>
            <p class="text-[9px] text-slate-500 uppercase tracking-[0.5em] font-black mt-1 opacity-60">Control Panel</p>
        </div>
        <nav class="flex flex-col gap-2">
            <a href="?tab=market" class="nav-link p-4 rounded-[1.5rem] flex items-center gap-4 font-bold text-sm <?= $tab=='market'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-store"></i> Marketplace
            </a>
            <a href="?tab=requested" class="nav-link p-4 rounded-[1.5rem] flex items-center gap-4 font-bold text-sm <?= $tab=='requested'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Requested
            </a>
            <a href="?tab=completed" class="nav-link p-4 rounded-[1.5rem] flex items-center gap-4 font-bold text-sm <?= $tab=='completed'?'active-tab':'text-slate-400' ?>">
                <i class="fa-solid fa-circle-check"></i> Completed
            </a>
        </nav>
        <div class="mt-auto p-4 opacity-20 text-[9px] font-black tracking-widest text-center uppercase">Studio Matrix v2.0</div>
    </aside>

    <div class="flex-1 glass-main-card">
        <header class="inner-nav-glass flex items-center justify-between px-4 md:px-10 sticky top-0 z-50">
            <div class="flex-1">
                <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] hidden sm:block">
                    System / <span class="text-white"><?= ucfirst($tab) ?></span>
                </h2>
                <h2 class="text-xs font-black text-white sm:hidden truncate"><?= ucfirst($tab) ?></h2>
            </div>
            
            <div class="flex items-center gap-3 md:gap-4">
                <a href="profile.php" class="profile-chip py-1.5 pl-1.5 pr-3 md:pr-5 rounded-full flex items-center gap-2 md:gap-3 transition-all hover:bg-white/10 shrink-0">
                    <div class="h-7 w-7 md:h-8 md:w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center font-black text-[10px] text-white shrink-0">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="flex flex-col leading-tight overflow-hidden">
                        <span class="text-[9px] md:text-[10px] font-black text-white truncate max-w-[60px] md:max-w-[100px]"><?= htmlspecialchars($userName) ?></span>
                        <span class="text-[7px] text-blue-400 font-black uppercase tracking-tighter">Verified</span>
                    </div>
                </a>
                <div class="h-6 w-[1px] bg-white/10 shrink-0"></div>
                <a href="logout.php" class="text-slate-500 hover:text-red-400 transition-colors shrink-0">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </a>
            </div>
        </header>

        <main class="p-4 md:p-10 flex-1 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <?php 
                    $file = $tab . '.php';
                    if (file_exists($file)) { include $file; } 
                    else { echo "<div class='p-12 text-center text-slate-500 font-bold uppercase italic tracking-widest text-xs'>Node Interface Not Detected</div>"; }
                ?>
            </div>
        </main>
    </div>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            mobileMenu.classList.toggle('active');
            const icon = menuBtn.querySelector('i');
            if (mobileMenu.classList.contains('active')) {
                icon.classList.replace('fa-bars-staggered', 'fa-xmark');
            } else {
                icon.classList.replace('fa-xmark', 'fa-bars-staggered');
            }
        });

        document.addEventListener('click', (e) => {
            if (mobileMenu.classList.contains('active') && !mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
                menuBtn.querySelector('i').classList.replace('fa-xmark', 'fa-bars-staggered');
            }
        });
    </script>
</body>
</html>