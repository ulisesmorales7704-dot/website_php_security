<?php
session_start();
require 'db.php';

$message = "";
$message_type = "error";

// Only process if the form is actually submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    // Fixed: Added ?? '' to prevent "Undefined array key" warnings
    $title         = $_POST['title'] ?? '';
    $description   = $_POST['description'] ?? '';
    $features      = $_POST['features'] ?? ''; 
    $price         = $_POST['price'] ?? 0;
    $delivery_time = $_POST['delivery_time'] ?? 0;
    $status        = $_POST['status'] ?? 'available';
    $image         = !empty($_POST['image']) ? $_POST['image'] : 'default_service.jpg'; 

    if (empty($title) || empty($price) || empty($delivery_time)) {
        $message = "Please fill in all required fields.";
    } else {
        try {
            $sql = "INSERT INTO services (title, description, features, price, delivery_time, status, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$title, $description, $features, $price, $delivery_time, $status, $image])) {
                $message = "New service successfully deployed to marketplace!";
                $message_type = "success";
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
    <title>Add Service | Secure Admin</title>
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
            -webkit-backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); 
        }
        input, textarea, select { transition: all 0.3s ease; }
        input:focus, textarea:focus, select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
        }
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-2xl p-8 rounded-3xl">
        
        <div class="flex justify-between items-center mb-6">
            <a href="admin.php" class="text-slate-500 hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="text-xl font-bold text-white italic">Deploy <span class="text-blue-500">Service</span></h2>
            <div class="w-4"></div>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-3 text-center rounded-lg border text-xs 
                <?= $message_type == 'success' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?>">
                <i class="fa-solid <?= $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mr-1"></i> 
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Service Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-layer-group text-[10px]"></i>
                    </span>
                    <input type="text" name="title" required placeholder="e.g. Modern UI/UX Design"
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Price ($)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-dollar-sign text-[10px]"></i>
                        </span>
                        <input type="number" step="0.01" name="price" required placeholder="99.00"
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Delivery Time (Days)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-clock text-[10px]"></i>
                        </span>
                        <input type="number" name="delivery_time" required placeholder="5"
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Availability Status</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-toggle-on text-[10px]"></i>
                        </span>
                        <select name="status" class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white cursor-pointer">
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Service Thumbnail URL</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-image text-[10px]"></i>
                        </span>
                        <input type="text" name="image" placeholder="https://unsplash.com/..."
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Key Features (Comma Separated)</label>
                <div class="relative">
                    <span class="absolute top-3 left-0 pl-3 text-slate-500">
                        <i class="fa-solid fa-list-ul text-[10px]"></i>
                    </span>
                    <input type="text" name="features" placeholder="Feature 1, Feature 2, Feature 3"
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Full Description</label>
                <textarea name="description" rows="4" placeholder="Describe what this service offers in detail..."
                          class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-3 text-sm outline-none text-white resize-none"></textarea>
            </div>

            <button type="submit" name="add_service" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/40 mt-2 active:scale-95">
                Authorize Marketplace Entry
            </button>
        </form>
    </div>
</body>
</html>