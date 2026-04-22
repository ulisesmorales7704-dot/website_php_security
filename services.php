<?php 
require 'db.php'; 
error_reporting(E_ALL & ~E_NOTICE); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Services | Digital Portfolio</title>
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
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        #mobile-menu {
            transition: all 0.3s ease-in-out;
            transform: translateY(-20px);
            opacity: 0;
            pointer-events: none;
            width: calc(100% - 3rem); 
            max-width: 400px;
        }
        #mobile-menu.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        
        /* মেইন ফিক্স: কার্ডের পুরোটা লিঙ্ক করার জন্য */
        .service-card {
            position: relative;
            isolation: auto; /* isolate সমস্যা সমাধান */
        }
        .full-link {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 40; /* সবার উপরে থাকবে */
            cursor: pointer;
        }
    </style>
</head>

<body class="min-h-screen">

    <nav class="fixed top-0 w-full z-[100] px-6 py-4 flex flex-col items-center">
        <div class="w-full max-w-6xl glass rounded-full px-8 py-3 flex justify-between items-center text-sm font-semibold border-white/10">
            <a href="home.php" class="text-xl font-bold tracking-tight">PORTFOLIO</a>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="hover:text-blue-400 transition">Home</a>
                <a href="services.php" class="hover:text-blue-400 transition">Services</a>
                <a href="developers.php" class="hover:text-blue-400 transition">Team</a>
                <a href="all_reviews.php" class="hover:text-blue-400 transition">Reviews</a>
                <a href="login.php" class="border border-white/20 px-6 py-2 rounded-full hover:bg-blue-600 hover:text-white transition">Sign In</a>
            </div>

            <button onclick="toggleMenu()" class="md:hidden text-2xl focus:outline-none">
                <span id="menu-icon">☰</span>
            </button>
        </div>

        <div id="mobile-menu" class="mt-4 glass rounded-3xl p-6 md:hidden flex flex-col space-y-4 text-center shadow-2xl">
            <a href="home.php" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Home</a>
            <a href="services.php" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Services</a>
            <a href="developers.php" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Team</a>
            <a href="all_reviews.php" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Reviews</a>
            <a href="login.php" class="bg-blue-600 py-3 rounded-2xl text-white font-bold transition">Sign In</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 pt-40 pb-20">
        
        <div class="mb-12">
            <h1 class="text-5xl md:text-6xl font-black mb-4 tracking-tighter italic leading-[1.1] py-2">
                Full <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500">Solutions</span>
            </h1>
            <p class="text-slate-400 max-w-2xl text-lg leading-relaxed">
                Browse our complete list of technical and creative services tailored for your digital success.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
                $i = 0; 
                while ($row = $stmt->fetch()) { 
                    $isAvailable = ($row['status'] === 'available');
                    $zoomClass = ($i < 2 && $isAvailable) ? 'group-hover:scale-110' : '';
                ?>
                
                <div class="service-card flex flex-col rounded-[2.5rem] overflow-hidden transition-all duration-300 hover:scale-[1.03] group shadow-xl border border-transparent hover:border-blue-500 bg-[#0f172a]">
                    
                    <a href="view_more.php?id=<?= $row['id'] ?>" class="full-link"></a>

                    <div class="h-52 relative bg-[#1e293b] overflow-hidden">
                        <img src="<?= htmlspecialchars($row['image']) ?>" 
                             class="w-full h-full object-cover transition-transform duration-700 <?= $zoomClass ?> <?= !$isAvailable ? 'grayscale opacity-50' : '' ?>" 
                             onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=500&q=80'">
                        
                        <div class="absolute top-5 right-5 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 flex items-center gap-2">
                            <?php if ($isAvailable): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">Available</span>
                            <?php else: ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                <span class="text-[9px] font-bold text-red-500 uppercase tracking-widest">Unavailable</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="glass p-8 flex flex-col flex-grow rounded-b-[2.5rem] border-t-0 -mt-1 relative transition-colors duration-300">
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-blue-400 transition-colors tracking-tight">
                            <?= htmlspecialchars($row['title']) ?>
                        </h3>
                        
                        <p class="text-slate-400 text-[13px] leading-relaxed mb-8 line-clamp-2 opacity-80">
                            <?= htmlspecialchars($row['description']) ?>
                        </p>
                        
                        <div class="mt-auto flex justify-between items-center">
                            <div>
                                <p class="text-blue-500 text-xl font-black mb-1">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                                <p class="text-slate-500 text-[10px] uppercase tracking-widest flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($row['delivery_time'] ?? 5) ?> Days
                                </p>
                            </div>

                            <div class="bg-[#1e293b] text-white px-6 py-3 rounded-xl text-[11px] font-bold uppercase tracking-widest border border-white/5 shadow-lg group-hover:bg-blue-600 transition-colors duration-300">
                                View More
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $i++; 
                } 
            } catch (PDOException $e) { } 
            ?>
        </div>
    </main>

    <footer class="border-t border-white/5 py-12 text-center text-slate-600 text-[10px] tracking-[0.4em] uppercase font-bold">
        &copy; 2026 DIGITAL PORTFOLIO. ALL RIGHTS RESERVED.
    </footer>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');
            menu.classList.toggle('active');
            icon.innerHTML = menu.classList.contains('active') ? '✕' : '☰';
        }

        window.onclick = function(event) {
            const menu = document.getElementById('mobile-menu');
            if (!event.target.closest('nav') && menu.classList.contains('active')) {
                menu.classList.remove('active');
                document.getElementById('menu-icon').innerHTML = '☰';
            }
        }
    </script>
</body>
</html>