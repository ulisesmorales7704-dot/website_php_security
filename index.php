<?php 
require 'db.php'; 
// Hides notices on screen to keep the UI clean while developing
error_reporting(E_ALL & ~E_NOTICE); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio | Premium Glassmorphism</title>
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

        /* Mobile Menu Panel Design */
        #mobile-menu {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .animate-float { animation: float 5s ease-in-out infinite; }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }

        .service-card {
            position: relative;
        }
        .full-link {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10; /* Lowered to prevent blocking header links */
            cursor: pointer;
        }
    </style>
</head>

<body class="min-h-screen">

    <nav class="fixed top-0 w-full z-[100] px-6 py-4 flex flex-col items-center">
        <div class="w-full max-w-6xl glass rounded-full px-8 py-3 flex justify-between items-center text-sm font-semibold border-white/10">
            <a href="home.php" class="text-xl font-bold tracking-tight">PORTFOLIO</a>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="home.php" class="hover:text-blue-400 transition">Home</a>
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
            <a href="home.php" onclick="toggleMenu()" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Home</a>
            <a href="services.php" onclick="toggleMenu()" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Services</a>
            <a href="developers.php" onclick="toggleMenu()" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Team</a>
            <a href="all_reviews.php" onclick="toggleMenu()" class="py-2 hover:text-blue-400 border-b border-white/5 transition">Reviews</a>
            <a href="login.php" class="bg-blue-600 py-3 rounded-2xl text-white font-bold transition">Sign In</a>
        </div>
    </nav>

    <header class="relative pt-56 pb-24 px-6 overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[120px]"></div>
        <div class="max-w-6xl mx-auto flex flex-col-reverse md:flex-row gap-12 items-center relative z-10">
            <div class="text-center md:text-left flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 mb-8 glass rounded-full border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-[0.2em]">Available for Projects</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-black mb-6 tracking-tight leading-tight">
                    Crafting <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-500">
                        Digital Experiences
                    </span>
                </h1>
                <p class="text-base md:text-lg text-slate-400 mb-10 leading-relaxed max-w-lg">
                    Visualizing complex ideas into elegant reality with high-end glassmorphism and modern clean code.
                </p>
                <div class="flex justify-center md:justify-start">
                    <a href="registration.php" class="bg-blue-600 text-white px-8 py-3.5 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 transform hover:-translate-y-1">
                        Get Started <i class="fa-solid fa-chevron-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>
            <div class="relative flex justify-center items-center shrink-0">
                <div class="relative w-72 h-72 md:w-[400px] md:h-[400px] animate-float">
                    <div class="absolute -top-4 -left-4 glass p-3 rounded-xl z-20 border-white/20 shadow-xl">
                        <i class="fa-solid fa-code text-blue-400 text-sm"></i>
                    </div>
                    <div class="absolute -bottom-4 -right-4 glass p-3 rounded-xl z-20 border-white/20 shadow-xl">
                        <i class="fa-solid fa-bolt text-yellow-400 text-sm"></i>
                    </div>
                    <div class="relative w-full h-full rounded-[2.5rem] border border-white/10 p-2 glass overflow-hidden shadow-2xl">
                        <img src="test.png" class="w-full h-full object-cover rounded-[2rem]">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 pb-20">
        <section id="services" class="py-16">
            <div class="flex justify-between items-end mb-12 relative z-50"> <div>
                    <h2 class="text-4xl md:text-5xl font-black text-white tracking-tight italic">Featured Work</h2>
                    <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-2">Selected Services</p>
                </div>
                <a href="services.php" class="relative z-50 text-slate-500 text-xs font-bold uppercase tracking-widest hover:text-white transition py-2 px-1">
                    Explore All <i class="fa-solid fa-arrow-right-long ml-2"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM services WHERE top > 0 ORDER BY top ASC LIMIT 3");
                    while ($row = $stmt->fetch()) { ?>
                    
                    <div class="service-card flex flex-col rounded-[2.5rem] overflow-hidden transition-all duration-300 hover:scale-[1.03] group shadow-xl border border-transparent hover:border-blue-500 bg-[#0f172a]/40">
                        <a href="view_more.php?id=<?= $row['id'] ?>" class="full-link"></a>
                        
                        <div class="h-52 relative bg-[#1e293b] overflow-hidden">
                            <img src="<?= htmlspecialchars($row['image'] ?? '') ?>" class="w-full h-full object-cover transition-transform duration-700" onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=500&q=80'">
                            <div class="absolute top-5 right-5 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 flex items-center gap-2 z-20">
                                <?php if(($row['status'] ?? '') == 'available'): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">Available</span>
                                <?php else: ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span class="text-[9px] font-bold text-red-500 uppercase tracking-widest">Unavailable</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="glass p-8 flex flex-col flex-grow rounded-b-[2.5rem] border-t-0 -mt-1 relative transition-colors duration-300">
                            <h3 class="text-xl font-bold text-white mb-3 group-hover:text-blue-400 transition-colors tracking-tight"><?= htmlspecialchars($row['title'] ?? '') ?></h3>
                            <p class="text-slate-400 text-[13px] leading-relaxed mb-8 line-clamp-2 opacity-80"><?= htmlspecialchars($row['description'] ?? '') ?></p>
                            <div class="mt-auto flex justify-between items-center">
                                <div>
                                    <p class="text-blue-500 text-xl font-black mb-1">$<?= number_format($row['price'] ?? 0, 2) ?></p>
                                    <p class="text-slate-500 text-[10px] uppercase tracking-widest flex items-center gap-1">
                                        <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($row['delivery_time'] ?? 5) ?> Days
                                    </p>
                                </div>
                                <div class="bg-[#1e293b] text-white px-6 py-3 rounded-xl text-[11px] font-bold uppercase tracking-widest border border-white/5 shadow-lg group-hover:bg-blue-600 transition-colors duration-300">View More</div>
                            </div>
                        </div>
                    </div>
                <?php } } catch (PDOException $e) { } ?>
            </div>
        </section>

        <section id="developers" class="py-16 mt-20">
            <div class="mb-12">
                <h2 class="text-5xl font-black text-white tracking-tight italic">Expert Team</h2>
                <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-2">Core Developers</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM developers LIMIT 3");
                    while($dev = $stmt->fetch()) { ?>
                    <div class="glass p-8 rounded-[2.5rem] text-center flex flex-col items-center border border-white/10 hover:bg-white/[0.05] transition-all duration-500 group">
                        <div class="w-40 h-40 rounded-full border-4 border-slate-700/50 p-1 mb-6 overflow-hidden glass shadow-2xl group-hover:border-blue-500/50 transition-colors shrink-0">
                            <img src="<?= htmlspecialchars($dev['image'] ?? '') ?>" class="w-full h-full rounded-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700" onerror="this.src='https://via.placeholder.com/200/1e293b/64748b?text=User'">
                        </div>
                        <h4 class="text-xl font-bold text-white mb-1"><?= htmlspecialchars($dev['name'] ?? '') ?></h4>
                        <p class="text-blue-500 text-xs font-bold uppercase tracking-widest mb-4 italic"><?= htmlspecialchars($dev['post'] ?? $dev['role'] ?? '') ?></p>
                        <a href="mailto:<?= htmlspecialchars($dev['email'] ?? '') ?>" class="text-blue-400 text-sm hover:underline font-medium transition italic"><?= htmlspecialchars($dev['email'] ?? '') ?></a>
                    </div>
                <?php } } catch (PDOException $e) { } ?>
            </div>
            <div class="mt-12 text-center">
                <a href="developers.php" class="inline-block border border-white/20 px-10 py-3 rounded-full text-sm font-bold uppercase tracking-widest hover:bg-blue-600 hover:border-blue-600 transition-all duration-300">See All Team</a>
            </div>
        </section>

        <section id="reviews" class="py-16 mt-20">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-5xl font-black text-white tracking-tight italic">Recent Feedback</h2>
                    <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-2">Client Testimonials</p>
                </div>
                <a href="all_reviews.php" class="hidden md:inline-block text-slate-500 text-xs font-bold uppercase tracking-widest hover:text-white transition py-2 px-1">
                    View All Reviews <i class="fa-solid fa-arrow-right-long ml-2"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM reviews LIMIT 4");
                    while ($rev = $stmt->fetch()) { ?>
                <div class="glass p-8 rounded-[2.5rem] flex flex-col sm:flex-row gap-6 items-start border border-white/5 hover:border-blue-500 transition-all duration-300">
                    <img src="<?= htmlspecialchars($rev['client_image'] ?? '') ?>" class="w-14 h-14 rounded-full object-cover border-2 border-white/10 shadow-xl shrink-0" onerror="this.src='https://via.placeholder.com/100/020617/64748b?text=User'">
                    <div class="flex-grow">
                        <div class="flex flex-col mb-3">
                            <h4 class="text-sm font-bold text-white tracking-wide"><?= htmlspecialchars($rev['client_name'] ?? '') ?></h4>
                            <div class="text-yellow-500 text-[10px] mt-1">
                                <?php for($i = 0; $i < 5; $i++) { echo ($i < ($rev['rating'] ?? 0)) ? '★' : '<span class="text-slate-700">★</span>'; } ?>
                            </div>
                        </div>
                        <p class="text-slate-400 text-[13px] italic leading-relaxed font-medium">"<?= htmlspecialchars($rev['comment'] ?? '') ?>"</p>
                    </div>
                </div>
                <?php } } catch (PDOException $e) { } ?>
            </div>
            <div class="mt-12 text-center md:hidden">
                <a href="all_reviews.php" class="inline-block border border-white/20 px-10 py-3 rounded-full text-sm font-bold uppercase tracking-widest hover:bg-blue-600 hover:border-blue-600 transition-all duration-300">See All Reviews</a>
            </div>
        </section>
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

        // Close menu when clicking outside
        window.onclick = function(event) {
            const menu = document.getElementById('mobile-menu');
            const nav = document.querySelector('nav');
            if (menu.classList.contains('active') && !nav.contains(event.target)) {
                menu.classList.remove('active');
                document.getElementById('menu-icon').innerHTML = '☰';
            }
        }
    </script>
</body>
</html>