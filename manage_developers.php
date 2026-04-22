<?php
// ১. সেশন এবং ডাটাবেস কানেকশন
session_start();
require 'db.php';

// ২. ডিলিট লজিক (নিরাপত্তার জন্য PDO ব্যবহার করা হয়েছে)
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        
        // ছবি ডিলিট করার জন্য প্রথমে নাম খুঁজে বের করা
        $imgStmt = $pdo->prepare("SELECT image FROM developers WHERE id = ?");
        $imgStmt->execute([$delete_id]);
        $devData = $imgStmt->fetch();

        if ($devData && !empty($devData['image'])) {
            $filePath = "uploads/" . $devData['image'];
            if (file_exists($filePath)) {
                unlink($filePath); // সার্ভার থেকে ফাইল ডিলিট
            }
        }

        // ডাটাবেস থেকে ডিলিট
        $deleteStmt = $pdo->prepare("DELETE FROM developers WHERE id = ?");
        $deleteStmt->execute([$delete_id]);

        header("Location: manage_developers.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        die("Deletion Failed: " . $e->getMessage());
    }
}

// ৩. ডেভেলপার ডাটা নিয়ে আসা
try {
    $query = "SELECT * FROM developers ORDER BY id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $developers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Query Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Developers | Admin Panel</title>
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
        .table-row-hover:hover {
            background: rgba(59, 130, 246, 0.05);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen p-4 md:p-10 flex justify-center">

    <main class="w-full max-w-6xl space-y-8">
        
        <div class="glass w-full rounded-[2.5rem] p-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-6">
                <a href="admin.php" class="glass w-12 h-12 rounded-2xl flex items-center justify-center hover:bg-white/10 transition-all border-white/10 group">
                    <i class="fa-solid fa-arrow-left text-blue-500 group-hover:-translate-x-1 transition-transform"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-white italic tracking-tight">Developer Matrix</h2>
                    <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-1">Personnel Management System</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                
                <div class="hidden md:flex items-center gap-4 glass px-6 py-3 rounded-2xl border-white/10">
                    <i class="fa-solid fa-code text-blue-500"></i>
                    <span class="text-sm font-bold italic"><?= count($developers) ?> total members</span>
                </div>
            </div>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5">
                            <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500">Member</th>
                            <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500">Assignment (Post)</th>
                            <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500">Communication</th>
                            <th class="p-6 text-[11px] font-black uppercase tracking-[0.3em] text-slate-500 text-right">Operation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach($developers as $row): ?>
                        <tr class="table-row-hover">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>" 
                                               class="w-14 h-14 rounded-2xl object-cover border-2 border-white/10 shadow-xl"
                                               onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=random'">
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-[#0f172a] rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="text-white font-black italic tracking-tight"><?= htmlspecialchars($row['name']) ?></p>
                                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">ID: #DEV-0<?= $row['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-widest">
                                    <?= htmlspecialchars($row['post']) ?>
                                </span>
                            </td>
                            <td class="p-6">
                                <p class="text-sm text-slate-300 font-medium"><?= htmlspecialchars($row['email']) ?></p>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="update_developer.php?id=<?= $row['id'] ?>" 
                                         class="inline-flex items-center justify-center w-10 h-10 glass rounded-xl text-blue-500 hover:bg-blue-600 hover:text-white transition-all group">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <a href="manage_developers.php?delete_id=<?= $row['id'] ?>" 
                                         onclick="return confirm('Attention: Are you sure you want to terminate this developer record? This action is irreversible.');"
                                         class="inline-flex items-center justify-center w-10 h-10 glass rounded-xl text-red-500 hover:bg-red-600 hover:text-white transition-all group">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(empty($developers)): ?>
            <div class="p-20 text-center">
                <i class="fa-solid fa-user-slash text-6xl text-white/5 mb-4"></i>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">No developers found in the matrix</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex justify-between items-center px-10 text-[10px] font-bold text-slate-600 uppercase tracking-[0.4em]">
            <span>System Secure: AES-256</span>
            <span>Local Node: <?= date('d M Y') ?></span>
        </div>
    </main>

</body>
</html>