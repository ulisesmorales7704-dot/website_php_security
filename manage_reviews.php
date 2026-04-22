<?php
session_start();
require 'db.php';

// --- ১. ডিলিট লজিক (Single and Multiple) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['status' => 'error', 'message' => 'Invalid action'];

    if ($_POST['action'] === 'delete_reviews' && isset($_POST['ids'])) {
        $ids = is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']];
        $ids = array_map('intval', $ids); 

        if (!empty($ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) {
                    $response = ['status' => 'success'];
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- ২. ডাটা রিড লজিক ---
try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Matrix Sync Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Control Center | Premium Glass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
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

        .custom-check { 
            width: 20px; 
            height: 20px; 
            cursor: pointer; 
            accent-color: #ef4444; 
        }

        /* Scannability improvement */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
    </style>
</head>
<body class="min-h-screen p-4 md:p-12">

    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="admin.php" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-slate-500 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter">
                    Manage <span class="text-red-500">Review</span>
                </h2>
            </div>
            
            <button id="batchDeleteBtn" onclick="openModal('multiple')" class="hidden w-full md:w-auto bg-red-600 hover:bg-red-500 text-white px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-900/40">
                Delete all selected (<span id="countDisplay">0</span>)
            </button>
        </div>

        <div class="md:hidden glass mb-6 p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAllMobile" class="custom-check">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Select All</span>
            </div>
        </div>

        <div class="glass rounded-[2rem] md:rounded-[2.5rem] overflow-hidden">
            <table class="hidden md:table w-full text-left border-collapse">
                <thead>
                    <tr class="text-slate-500 text-[10px] uppercase tracking-[0.3em] font-black bg-white/[0.02]">
                        <th class="px-8 py-6 w-10"><input type="checkbox" id="selectAll" class="custom-check"></th>
                        <th class="px-8 py-6">Identity</th>
                        <th class="px-8 py-6">Message</th>
                        <th class="px-8 py-6 text-right">Operation</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-600 font-bold uppercase tracking-widest text-[10px]">Matrix is empty</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $row): ?>
                        <tr class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-8 py-6"><input type="checkbox" value="<?= $row['id'] ?>" class="review-id-cb custom-check"></td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($row['client_image']) ?>" class="w-8 h-8 rounded-full border border-white/10 object-cover" onerror="this.src='https://via.placeholder.com/100/020617/64748b?text=U'">
                                    <span class="font-bold text-white italic"><?= htmlspecialchars($row['client_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-slate-400 text-xs italic">
                                <div class="line-clamp-1 max-w-xs">"<?= htmlspecialchars($row['comment']) ?>"</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button onclick="openModal(<?= $row['id'] ?>)" class="text-slate-600 hover:text-red-500 transition-colors">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="md:hidden">
                <?php if (empty($reviews)): ?>
                    <div class="p-20 text-center text-slate-600 font-bold uppercase tracking-widest text-[10px]">Matrix is empty</div>
                <?php else: ?>
                    <div class="divide-y divide-white/5">
                        <?php foreach ($reviews as $row): ?>
                        <div class="p-6 flex flex-col gap-4 hover:bg-white/[0.02] transition-colors">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" value="<?= $row['id'] ?>" class="review-id-cb custom-check">
                                    <img src="<?= htmlspecialchars($row['client_image']) ?>" class="w-10 h-10 rounded-full border border-white/10 object-cover" onerror="this.src='https://via.placeholder.com/100/020617/64748b?text=U'">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white italic text-base"><?= htmlspecialchars($row['client_name']) ?></span>
                                        <span class="text-[9px] text-slate-500 uppercase tracking-widest font-mono italic">#REQ-<?= $row['id'] ?></span>
                                    </div>
                                </div>
                                <button onclick="openModal(<?= $row['id'] ?>)" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-red-500/50 hover:text-red-500">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <div class="bg-white/[0.02] p-4 rounded-2xl border border-white/5">
                                <p class="text-slate-400 text-xs italic leading-relaxed line-clamp-2">"<?= htmlspecialchars($row['comment']) ?>"</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
        <div class="glass max-w-sm w-full p-8 md:p-10 rounded-[2.5rem] md:rounded-[3rem] text-center border-red-500/20 shadow-2xl">
            <div class="w-16 h-16 md:w-20 md:h-20 bg-red-500/10 rounded-full flex items-center justify-center text-red-500 text-2xl md:text-3xl mx-auto mb-6">
                <i class="fa-solid fa-skull-crossbones"></i>
            </div>
            <h3 class="text-lg md:text-xl font-black text-white italic mb-2">Confirm Destruction?</h3>
            <p class="text-slate-400 text-[10px] md:text-xs leading-relaxed mb-8">This data will be purged from the matrix permanently.</p>
            <div class="flex gap-4">
                <button onclick="closeModal()" class="flex-1 px-4 md:px-6 py-4 rounded-2xl glass text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-white transition">Abort</button>
                <button id="confirmBtn" class="flex-1 px-4 md:px-6 py-4 rounded-2xl bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-red-900/40">Execute</button>
            </div>
        </div>
    </div>

    <script>
    const selectAll = document.getElementById('selectAll');
    const selectAllMobile = document.getElementById('selectAllMobile');
    const cbs = document.querySelectorAll('.review-id-cb');
    const batchBtn = document.getElementById('batchDeleteBtn');
    let currentTarget = null;

    // ১. চেকিবক্স কন্ট্রোল (Sync Desktop & Mobile)
    const handleSelectAll = (checked) => {
        cbs.forEach(cb => cb.checked = checked);
        if(selectAll) selectAll.checked = checked;
        if(selectAllMobile) selectAllMobile.checked = checked;
        updateUI();
    };

    if(selectAll) selectAll.onchange = (e) => handleSelectAll(e.target.checked);
    if(selectAllMobile) selectAllMobile.onchange = (e) => handleSelectAll(e.target.checked);
    
    cbs.forEach(cb => {
        cb.onchange = updateUI;
    });

    function updateUI() {
        const checkedCount = document.querySelectorAll('.review-id-cb:checked').length;
        document.getElementById('countDisplay').innerText = checkedCount;
        batchBtn.classList.toggle('hidden', checkedCount === 0);
    }

    // ২. মোডাল কন্ট্রোল
    function openModal(id) {
        currentTarget = id;
        document.getElementById('confirmModal').classList.replace('hidden', 'flex');
        document.getElementById('confirmBtn').onclick = processDelete;
    }

    function closeModal() {
        document.getElementById('confirmModal').classList.replace('flex', 'hidden');
    }

    // ৩. ডিলিট প্রসেসিং
    function processDelete() {
        let ids = (currentTarget === 'multiple') 
                  ? Array.from(document.querySelectorAll('.review-id-cb:checked')).map(cb => cb.value)
                  : [currentTarget];

        let formData = new URLSearchParams();
        formData.append('action', 'delete_reviews');
        ids.forEach(id => formData.append('ids[]', id));

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') location.reload();
            else alert(data.message);
        })
        .catch(err => console.error('Error:', err));
    }
    </script>
</body>
</html>