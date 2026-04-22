<?php
session_start();
require 'db.php';

// Hides notices to keep UI clean
error_reporting(E_ALL & ~E_NOTICE);

try {
    // Fetch all reviews from the database
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Could not retrieve reviews.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews | Premium Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a; 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); 
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
                <a href="all_reviews.php" class="hover:text-blue-400 transition text-blue-400">Reviews</a>
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
        
        <div class="mb-16 relative">
            <h2 class="text-5xl font-black text-white tracking-tight italic">Client Feedback</h2>
            <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-4">Transparent testimonials from our global users</p>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="glass p-20 text-center rounded-[2.5rem]">
                <i class="fa-regular fa-comment-dots text-5xl text-slate-700 mb-4"></i>
                <p class="text-slate-500 italic">No reviews have been published yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($reviews as $rev): ?>
                <div class="glass p-8 rounded-[2.5rem] flex flex-col sm:flex-row gap-6 items-start border border-white/5 hover:border-blue-500/30 transition-all duration-300">
                    <img src="<?= htmlspecialchars($rev['client_image']) ?>" 
                         class="w-16 h-16 rounded-full object-cover border-2 border-white/10 shadow-xl shrink-0" 
                         onerror="this.src='https://via.placeholder.com/100/020617/64748b?text=User'">
                    
                    <div class="flex-grow">
                        <div class="flex flex-col mb-3">
                            <h4 class="text-base font-bold text-white tracking-wide">
                                <?= htmlspecialchars($rev['client_name']) ?>
                            </h4>
                            <div class="text-yellow-500 text-[10px] mt-1">
                                <?php 
                                for($i = 0; $i < 5; $i++) {
                                    echo ($i < $rev['rating']) ? '★' : '<span class="text-slate-700">★</span>';
                                } 
                                ?>
                            </div>
                        </div>
                        <p class="text-slate-400 text-sm italic leading-relaxed font-medium">
                            "<?= htmlspecialchars($rev['comment']) ?>"
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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