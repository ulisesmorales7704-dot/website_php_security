<?php
session_start();
require 'db.php'; // Ensure your DB connection is correct

$message = "";
$message_type = "error";

if (isset($_POST['add_dev'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $post = $_POST['post'];
    $image = $_POST['image'];
    $description = $_POST['description'];

    if (empty($name) || empty($email) || empty($post)) {
        $message = "Please fill in the required fields.";
    } else {
        try {
            $sql = "INSERT INTO developers (name, post, bio, email, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$name, $post, $description, $email, $image])) {
                $message = "Developer integrated successfully!";
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
    <title>Add Developer | Secure Admin</title>
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
        input, textarea {
            transition: all 0.3s ease;
        }
        input:focus, textarea:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-lg p-8 rounded-3xl">
        
        <div class="flex justify-between items-center mb-6">
            <a href="admin.php" class="text-slate-500 hover:text-white transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 class="text-xl font-bold text-white italic">Add <span class="text-blue-500">Developer</span></h2>
            <div class="w-4"></div> </div>

        <?php if($message): ?>
            <div class="mb-6 p-3 text-center rounded-lg border text-xs 
                <?= $message_type == 'success' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?>">
                <i class="fa-solid <?= $message_type == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> mr-1"></i> 
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Full Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-user text-[10px]"></i>
                        </span>
                        <input type="text" name="name" required placeholder="Fahim Shakil"
                               class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Designation</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                            <i class="fa-solid fa-briefcase text-[10px]"></i>
                        </span>
                        <input type="text" name="post" required placeholder="Lead Developer"
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
                    <input type="email" name="email" required placeholder="fahim@studio.com"
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Image URL</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-image text-[10px]"></i>
                    </span>
                    <input type="text" name="image" placeholder="https://unsplash.com/photo..."
                           class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-2.5 pl-10 text-sm outline-none text-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1">Talent Description</label>
                <textarea name="description" rows="4" placeholder="Briefly describe the expertise..."
                          class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-3 text-sm outline-none text-white resize-none"></textarea>
            </div>

            <button type="submit" name="add_dev" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/40 mt-2 active:scale-95">
                Integrate Talent
            </button>
        </form>

    </div>

</body>
</html>