<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = trim($_POST['note']);
    $delivery_address = trim($_POST['delivery_address']);
    $delivery_time_slot = $_POST['delivery_time_slot'];
    $user_id = $_SESSION['user_id'];

    if (empty($delivery_time_slot)) $errors[] = "Please select a delivery time slot.";
    if (empty($delivery_address)) $errors[] = "Please provide a delivery address.";

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFiles = 5;
    $uploadDir = '../assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > $maxFiles)
        $errors[] = "You can upload maximum {$maxFiles} images.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO prescriptions (user_id, note, delivery_address, delivery_time_slot) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $note, $delivery_address, $delivery_time_slot]);
            $prescription_id = $pdo->lastInsertId();

            for ($i = 0; $i < $maxFiles; $i++) {
                if (!empty($_FILES['images']['name'][$i])) {
                    $fileTmpPath = $_FILES['images']['tmp_name'][$i];
                    $fileType = mime_content_type($fileTmpPath);
                    if (in_array($fileType, $allowedTypes)) {
                        $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                        $destPath = $uploadDir . $fileName;
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $stmt = $pdo->prepare('INSERT INTO prescription_images (prescription_id, image_path) VALUES (?, ?)');
                            $stmt->execute([$prescription_id, $fileName]);
                        } else {
                            $errors[] = "Failed to upload file: " . htmlspecialchars($_FILES['images']['name'][$i]);
                        }
                    } else {
                        $errors[] = "File type not allowed: " . htmlspecialchars($_FILES['images']['name'][$i]);
                    }
                }
            }
            if (empty($errors)) $success = "Prescription uploaded successfully!";
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Upload Prescription</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .file-drop-area {
        transition: 0.3s;
    }
    .file-drop-area.dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
</style>
</head>
<body class="bg-gradient-to-r from-cyan-50 via-blue-50 to-purple-50 min-h-screen flex items-center justify-center p-6 font-sans">

<div class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl p-10">
    <h2 class="text-4xl font-bold text-gray-800 mb-8 text-center">Upload Your Prescription</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 p-4 mb-6 rounded-xl text-green-800 border border-green-200 shadow-sm"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 p-4 mb-6 rounded-xl text-red-800 border border-red-200 shadow-sm">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <a href="dashboard.php" class="inline-block mb-6 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 hover:shadow-lg transition">
    ← Back to Dashboard
</a>


    <form method="POST" enctype="multipart/form-data" class="space-y-6">

        <!-- Drag & Drop File Upload -->
        <div id="drop-area" class="file-drop-area border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
            <p class="text-gray-500 mb-2 text-lg">Drag & drop images here or click to select</p>
            <input type="file" name="images[]" id="file-input" accept="image/*" multiple class="hidden">
            <div id="preview" class="flex flex-wrap gap-3 mt-4"></div>
        </div>

        <!-- Note -->
        <div class="relative">
            <textarea name="note" rows="3" class="peer w-full p-4 border rounded-xl focus:ring-2 focus:ring-cyan-400 focus:border-transparent resize-none"></textarea>
            <label class="absolute left-4 top-2 text-gray-400 peer-focus:text-cyan-500 transition-all">Note (optional)</label>
        </div>

        <!-- Delivery Address -->
        <div class="relative">
            <textarea name="delivery_address" required rows="2" class="peer w-full p-4 border rounded-xl focus:ring-2 focus:ring-cyan-400 focus:border-transparent resize-none"></textarea>
            <label class="absolute left-4 top-2 text-gray-400 peer-focus:text-cyan-500 transition-all">Delivery Address</label>
        </div>

        <!-- Time Slot -->
        <div class="relative">
            <select name="delivery_time_slot" required class="peer w-full p-4 border rounded-xl focus:ring-2 focus:ring-cyan-400 focus:border-transparent">
                <option value="">Select a time slot</option>
                <option value="08:00-10:00">08:00 - 10:00</option>
                <option value="10:00-12:00">10:00 - 12:00</option>
                <option value="12:00-14:00">12:00 - 14:00</option>
                <option value="14:00-16:00">14:00 - 16:00</option>
                <option value="16:00-18:00">16:00 - 18:00</option>
                <option value="18:00-20:00">18:00 - 20:00</option>
            </select>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-3 rounded-2xl text-lg font-semibold hover:scale-105 transform transition shadow-lg hover:shadow-xl">Submit Prescription</button>
    </form>
</div>

<script>
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('file-input');
const preview = document.getElementById('preview');

dropArea.addEventListener('click', () => fileInput.click());

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    showPreview();
});

fileInput.addEventListener('change', showPreview);

function showPreview() {
    preview.innerHTML = '';
    for (let i = 0; i < fileInput.files.length; i++) {
        const file = fileInput.files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-24 h-24 object-cover rounded-xl border shadow-sm';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>

</body>
</html>
