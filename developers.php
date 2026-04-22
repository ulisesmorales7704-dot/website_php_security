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
    <title>Our Team | Digital Portfolio</title>
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

        /* Mobile Menu Panel Design - Same as other pages */
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
    </style>
</head>

<body class="min-h-screen">

    <nav class="fixed top-0 w-full z-[100] px-6 py-4 flex flex-col items-center">
        <div class="w-full max-w-6xl glass rounded-full px-8 py-3 flex justify-between items-center text-sm font-semibold border-white/10">
            <a href="#" class="text-xl font-bold tracking-tight">PORTFOLIO</a>
            
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
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black mb-4 tracking-tighter italic leading-[1.1] py-2 overflow-visible">
                Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 px-2 pb-2 mt-1 inline-block overflow-visible" style="vertical-align: top;">Global Team</span>
            </h1>
            <p class="text-slate-400 max-w-2xl text-lg leading-relaxed">
                A collective of world-class developers and designers dedicated to building the future of the web.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM developers ORDER BY id ASC");
                while($dev = $stmt->fetch()) { 
                    $dName = $dev['name'] ?? 'Member Name';
                    $dPost = $dev['post'] ?? $dev['role'] ?? 'Developer';
                    $dBio  = $dev['bio']  ?? null; 
                    $dMail = $dev['email'] ?? 'info@example.com';
                    $dImg  = $dev['image'] ?? 'https://via.placeholder.com/300';
            ?>
            <div class="glass p-6 rounded-[2.5rem] text-center flex flex-col items-center border border-white/10 hover:bg-white/[0.05] transition-all duration-500 group">
                
                <div class="w-40 h-40 rounded-full border-4 border-slate-700/50 p-1 mb-6 overflow-hidden glass shadow-2xl group-hover:border-blue-500/50 transition-colors shrink-0">
                    <img src="<?= htmlspecialchars($dImg) ?>" 
                         class="w-full h-full rounded-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700 scale-110 group-hover:scale-100"
                         onerror="this.src='https://via.placeholder.com/200/1e293b/64748b?text=User'">
                </div>

                <h4 class="text-xl font-bold text-white mb-1 tracking-tight"><?= htmlspecialchars($dName) ?></h4>
                <p class="text-blue-500 text-xs font-bold uppercase tracking-widest mb-4 italic">
                    <?= htmlspecialchars($dPost) ?>
                </p>

                <?php if (!empty($dBio)): ?>
                <p class="text-slate-400 text-[13px] leading-relaxed mb-6 px-2 line-clamp-2">
                    <?= htmlspecialchars($dBio) ?>
                </p>
                <?php endif; ?>
                
                <a href="mailto:<?= htmlspecialchars($dMail) ?>" class="text-blue-400 text-sm hover:underline font-medium transition italic <?= empty($dBio) ? 'mt-4' : '' ?>">
                    <?= htmlspecialchars($dMail) ?>
                </a>
            </div>
            <?php 
                } 
            } catch (PDOException $e) {
                echo "<p class='text-slate-500 italic'>Loading team roster...</p>";
            }
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
            
            if (menu.classList.contains('active')) {
                icon.innerHTML = '✕';
            } else {
                icon.innerHTML = '☰';
            }
        }

        // Close menu when clicking outside
        window.onclick = function(event) {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');
            if (!event.target.closest('nav') && menu.classList.contains('active')) {
                menu.classList.remove('active');
                icon.innerHTML = '☰';
            }
        }
    </script>

</body>
</html>