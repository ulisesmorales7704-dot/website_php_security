<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
session_start();
require 'db.php';

$message = "";
$message_type = "error";

// ২. নির্দিষ্ট ডেভেলপারের ডাটা নিয়ে আসা (ID অনুযায়ী)
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM developers WHERE id = ?");
        $stmt->execute([$id]);
        $dev = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dev) {
            header("Location: manage_developers.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: manage_developers.php");
    exit();
}

// ৩. আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_dev'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $post = $_POST['post'] ?? '';
    $bio = $_POST['bio'] ?? null;
    $image = $_POST['image'] ?? ''; 
    $id = (int)$_POST['id'];

    if (empty($name) || empty($email) || empty($post)) {
        $message = "Please fill in the required fields.";
    } else {
        try {
            $sql = "UPDATE developers SET name=?, post=?, email=?, bio=?, image=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$name, $post, $email, $bio, $image, $id]);

            if ($result) {
                $message = "Unit Synchronized Successfully!";
                $message_type = "success";
                
                $stmt = $pdo->prepare("SELECT * FROM developers WHERE id = ?");
                $stmt->execute([$id]);
                $dev = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Developer | Secure Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a; 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); 
            background-attachment: fixed; 
        }
        .glass { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); 
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-lg p-8 rounded-3xl relative">
        
        <div class="flex justify-between items-center mb-6">
            <a href="manage_developers.php" class="relative z-50 text-slate-500 hover:text-blue-400 transition-all p-2 -ml-2">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-xl font-bold text-white italic">Update <span class="text-blue-500">Developer</span></h2>
            <div class="text-[10px] font-bold text-slate-600 font-mono">ID: #<?= $dev['id'] ?></div>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-3 text-center rounded-lg border text-xs 
                <?= $message_type == 'success' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?>">
                <i class="fa-solid <?= $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mr-1"></i> 
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?= $dev['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Full Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-user text-[10px]"></i>
                        </span>
                        <input type="text" name="name" value="<?= htmlspecialchars($dev['name'] ?? '') ?>" required 
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Designation</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-briefcase text-[10px]"></i>
                        </span>
                        <input type="text" name="post" value="<?= htmlspecialchars($dev['post'] ?? '') ?>" required 
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Public Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-envelope text-[10px]"></i>
                    </span>
                    <input type="email" name="email" value="<?= htmlspecialchars($dev['email'] ?? '') ?>" required 
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Image URL</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-image text-[10px]"></i>
                    </span>
                    <input type="text" name="image" value="<?= htmlspecialchars($dev['image'] ?? '') ?>" 
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Bio</label>
                <textarea name="bio" rows="3" class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-3 text-sm outline-none text-white resize-none leading-relaxed"><?= htmlspecialchars($dev['bio'] ?? '') ?></textarea>
            </div>

            <div class="flex gap-3 mt-4">
                <a href="manage_developers.php" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-white py-3 rounded-xl font-bold text-sm transition active:scale-95">
                    Cancel
                </a>
                <button type="submit" name="update_dev" class="flex-[2] bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/40 active:scale-95 italic">
                    Confirm Sync
                </button>
            </div>
        </form>

    </div>

</body>
</html>