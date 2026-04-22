<?php
session_start();
require 'db.php';

// ১. অথেন্টিকেশন চেক
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$req_id = $_GET['req_id'] ?? '';
$title  = $_GET['title'] ?? 'Service Feedback';

// ২. রিভিউ সাবমিশন লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rev'])) {
    $user_id    = $_SESSION['user_id'];
    $request_id = $_POST['request_id'];
    $rating     = $_POST['star'] ?? 5;
    $comment    = htmlspecialchars($_POST['comment']);

    try {
        $userStmt = $pdo->prepare("SELECT u_name FROM user WHERE id = ?");
        $userStmt->execute([$user_id]);
        $client_name = $userStmt->fetchColumn() ?: 'Client';

        $sql = "INSERT INTO reviews (client_name, comment, rating, request_id) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$client_name, $comment, $rating, $request_id])) {
            header("Location: dashboard.php?tab=completed&review=success");
            exit();
        }
    } catch (PDOException $e) {
        $message = "Matrix Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Feedback Hub | Studio Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        html, body { 
            height: 100%; 
            overflow: hidden; 
            margin: 0; 
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 30px;
            color: #1e293b;
            cursor: pointer;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { 
            color: #fbbf24; 
            text-shadow: 0 0 15px rgba(251, 191, 36, 0.4);
            transform: scale(1.1);
        }

        /* ইনপুট ফন্ট সাইজ আপডেট */
        .big-font-input {
            font-size: 1.1rem !important; /* ফন্ট সাইজ বাড়ানো হয়েছে */
            line-height: 1.6;
            font-weight: 500;
            letter-spacing: -0.01em;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div class="glass-card w-full max-w-md rounded-[3rem] p-10 relative overflow-hidden animate-in fade-in zoom-in duration-500">
        
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/10 blur-[80px] rounded-full"></div>
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-500/20">
                <i class="fa-solid fa-star-half-stroke text-2xl text-emerald-500"></i>
            </div>
            <h2 class="text-xl font-black text-white italic tracking-tighter uppercase leading-none">
                <?= htmlspecialchars($title) ?>
            </h2>
            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.4em] mt-3">Transmission Feedback</p>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-[10px] font-bold text-center italic">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($req_id) ?>">
            
            <div class="space-y-3">
                <label class="block text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Performance Score</label>
                <div class="star-rating">
                    <?php for($i=5; $i>=1; $i--): ?>
                        <input type="radio" id="st<?= $i ?>" name="star" value="<?= $i ?>" required <?= $i==5 ? 'checked' : '' ?>/>
                        <label for="st<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Observations</label>
                <textarea name="comment" required 
                          class="big-font-input w-full bg-slate-950/40 border border-white/5 rounded-2xl p-5 text-slate-200 outline-none focus:border-emerald-500/50 transition-all resize-none placeholder:text-slate-600 placeholder:text-sm" 
                          rows="4" maxlength="150"
                          placeholder="How was your experience?"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <a href="dashboard.php" class="py-4 rounded-2xl bg-white/5 border border-white/5 text-slate-400 text-[10px] font-black uppercase tracking-widest text-center hover:bg-white/10 transition-all">
                    Abort
                </a>
                <button type="submit" name="submit_rev" class="py-4 rounded-2xl bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-900/30 hover:bg-emerald-500 transition-all active:scale-95 italic">
                    Transmit
                </button>
            </div>
        </form>

        <div class="mt-10 text-center">
            <p class="text-[8px] text-slate-600 font-bold uppercase tracking-[0.3em]">End-to-End Encrypted Review Layer</p>
        </div>
    </div>

</body>
</html>