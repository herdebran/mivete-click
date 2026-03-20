<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Pendiente - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                </div>
                <span class="font-bold text-lg bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Vete a un Click
                </span>
            </a>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4 py-16">
        <div class="bg-white rounded-3xl shadow-lg p-8 border border-gray-100 text-center">
            
            <!-- Icono -->
            <div class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="clock" class="text-white w-12 h-12"></i>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-4">Pago Pendiente</h1>
            <p class="text-gray-600 mb-8">
                Tu reserva está creada pero el pago aún no fue confirmado. Una vez que abones, recibirás un email con los datos para ingresar a la consulta.
            </p>

            <!-- Información importante -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 mb-8 text-left">
                <h3 class="font-bold text-yellow-800 mb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5"></i>
                    ¿Qué pasa ahora?
                </h3>
                <ul class="space-y-3 text-yellow-700 text-sm">
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle-check" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                        <span>Tu turno está <strong>reservado temporalmente</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle-check" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                        <span>Tenés <strong>24 horas</strong> para completar el pago</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle-check" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                        <span>Una vez aprobado, recibirás el link de la videollamada por email</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle-check" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                        <span>Si no completás el pago, la reserva se cancelará automáticamente</span>
                    </li>
                </ul>
            </div>

            <!-- Instrucciones para pago en efectivo -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8 text-left">
                <h3 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                    Si elegiste pago en efectivo:
                </h3>
                <ol class="space-y-3 text-blue-700 text-sm list-decimal list-inside">
                    <li>Imprimí o guardá el cupón de pago que te dio Mercado Pago</li>
                    <li>Acercate a cualquier punto de pago (Rapipago, PagoFácil, etc.)</li>
                    <li>Aboná antes de que venza el cupón</li>
                    <li>La confirmación llega automáticamente en minutos</li>
                </ol>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php" 
                   class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Volver al Inicio
                </a>
                <a href="mailto:soporte@veteaunclick.com" 
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    Contactar Soporte
                </a>
            </div>

            <!-- Info de la reserva -->
            <?php if (isset($_SESSION['appointment_id'])): ?>
            <div class="mt-8 pt-8 border-t border-gray-100">
                <p class="text-gray-500 text-sm">
                    <strong>Nº de Reserva:</strong> #<?php echo $_SESSION['appointment_id']; ?>
                </p>
                <p class="text-gray-400 text-xs mt-2">
                    Guardá este número para cualquier consulta
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info de seguridad -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-xs flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                Tu reserva está segura. No pierdas el cupón de pago.
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>