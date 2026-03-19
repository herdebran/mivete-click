<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new AuthController($db);

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $resultado = $auth->login($email, $password);
    
    if ($resultado['success']) {
        header("Location: dashboard.php");
        exit;
    } else {
        $mensaje = $resultado['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Profesionales - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-sm px-6 py-3 rounded-2xl">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-purple-600 w-6 h-6"></i>
                </div>
                <span class="font-bold text-xl text-white">Vete a un Click</span>
            </div>
            <p class="text-white/80 mt-4">Panel de Profesionales</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Bienvenido</h1>
                <p class="text-gray-500">Iniciá sesión para gestionar tu agenda</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-purple-500"></i>
                            Email
                        </label>
                        <input type="email" name="email" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="tu@email.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4 text-purple-500"></i>
                            Contraseña
                        </label>
                        <input type="password" name="password" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="••••••••">
                    </div>

                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Iniciar Sesión
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-center text-gray-500 text-sm">
                    ¿No tenés cuenta? 
                    <a href="registro.php" class="text-purple-600 font-semibold hover:underline">Registrate aquí</a>
                </p>
            </div>
        </div>

        <!-- Volver -->
        <div class="text-center mt-6">
            <a href="../index.php" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver al inicio
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>