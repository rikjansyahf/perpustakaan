<?php
include "koneksi.php";
include "includes/app_settings.php";

if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));

    $data = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($data);

    if($cek > 0) {
        $_SESSION['user'] = mysqli_fetch_array($data);
        echo '<script>alert("Selamat datang, Login Berhasil!"); location.href="index.php"</script>';
    } else {
        echo '<script>alert("Username/Password salah!"); location.href="login.php";</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cfg['app_name']) ?> - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-lg mb-4 p-1">
                <img src="<?= htmlspecialchars($cfg['app_logo']) ?>" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-white text-2xl font-bold"><?= htmlspecialchars($cfg['app_name']) ?></h1>
            <p class="text-blue-200 text-sm mt-1"><?= htmlspecialchars($cfg['app_subtitle']) ?></p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-gray-800 text-xl font-semibold text-center mb-6">Masuk ke Akun</h2>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <i class="fas fa-user text-sm"></i>
                        </span>
                        <input type="text" name="username" required
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            placeholder="Masukkan username">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <i class="fas fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition duration-200 text-sm mt-2">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="mt-4 text-center">
                <p class="text-sm text-gray-500">Belum punya akun?
                    <a href="register.php" class="text-blue-600 hover:underline font-medium">Daftar sekarang</a>
                </p>
            </div>
        </div>

        <p class="text-center text-blue-200 text-xs mt-6"><?= htmlspecialchars($cfg['footer_text']) ?></p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
