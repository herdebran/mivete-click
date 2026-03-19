<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new AuthController($db);

    $data = [
        'nombre' => htmlspecialchars($_POST['nombre']),
        'apellido' => htmlspecialchars($_POST['apellido']),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'telefono' => htmlspecialchars($_POST['telefono']),
        'dni' => htmlspecialchars($_POST['dni']),
        'matricula' => htmlspecialchars($_POST['matricula']),
        'password' => $_POST['password']
    ];

    $resultado = $auth->register($data);
    $mensaje = $resultado['message'];
    $tipo = $resultado['success'] ? 'success' : 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Profesionales - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen py-12 px-4">
    
    <div class="max-w-2xl mx-auto">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="../index.php" class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-sm px-6 py-3 rounded-2xl">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-purple-600 w-6 h-6"></i>
                </div>
                <span class="font-bold text-xl text-white">Vete a un Click</span>
            </a>
            <p class="text-white/80 mt-4">Sumate como profesional</p>
        </div>

        <!-- Card de Registro -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Crear Cuenta</h1>
                <p class="text-gray-500">Completá tus datos para comenzar</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl <?php echo $tipo === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?> flex items-center gap-3">
                <i data-lucide="<?php echo $tipo === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 flex-shrink-0"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-purple-500"></i>
                            Nombre
                        </label>
                        <input type="text" name="nombre" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Juan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-purple-500"></i>
                            Apellido
                        </label>
                        <input type="text" name="apellido" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Pérez">
                    </div>

                    <div class="md:col-span-2">
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
                            <i data-lucide="phone" class="w-4 h-4 text-purple-500"></i>
                            Teléfono
                        </label>
                        <input type="tel" name="telefono" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="11 1234 5678">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-4 h-4 text-purple-500"></i>
                            DNI
                        </label>
                        <input type="text" name="dni" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="12345678">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="badge-check" class="w-4 h-4 text-purple-500"></i>
                            Matrícula
                        </label>
                        <input type="text" name="matricula" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="Ej: 12345">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4 text-purple-500"></i>
                            Contraseña
                        </label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition"
                               placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Crear Cuenta
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-center text-gray-500 text-sm">
                    ¿Ya tenés cuenta? 
                    <a href="login.php" class="text-purple-600 font-semibold hover:underline">Iniciá sesión</a>
                </p>
            </div>
        </div>

        <!-- Volver -->
        <div class="text-center mt-6">
            <a href="../index.php" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition text-sm">
                <i data-lucide="home" class="w-4 h-4"></i>
                Volver al inicio
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>