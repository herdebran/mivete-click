<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago No Aprobado - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
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
            <div class="w-24 h-24 bg-gradient-to-br from-orange-400 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="alert-circle" class="text-white w-12 h-12"></i>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-4">Pago No Aprobado</h1>
            <p class="text-gray-600 mb-8">
                Hubo un problema con tu pago. No te preocupes, podés intentar de nuevo o elegir otro método de pago.
            </p>

            <!-- Posibles causas -->
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-6 mb-8 text-left">
                <h3 class="font-bold text-orange-800 mb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5"></i>
                    Posibles causas:
                </h3>
                <ul class="space-y-2 text-orange-700 text-sm">
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle" class="w-3 h-3 mt-1 flex-shrink-0"></i>
                        Fondos insuficientes en tu cuenta
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle" class="w-3 h-3 mt-1 flex-shrink-0"></i>
                        Límite de tarjeta excedido
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle" class="w-3 h-3 mt-1 flex-shrink-0"></i>
                        Datos de la tarjeta incorrectos
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="circle" class="w-3 h-3 mt-1 flex-shrink-0"></i>
                        El banco rechazó la transacción
                    </li>
                </ul>
            </div>

            <!-- Acciones -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php" 
                   class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    Intentar de Nuevo
                </a>
                <a href="index.php" 
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Volver al Inicio
                </a>
            </div>

            <!-- Ayuda -->
            <div class="mt-8 pt-8 border-t border-gray-100">
                <p class="text-gray-500 text-sm mb-2">¿Necesitás ayuda?</p>
                <a href="mailto:soporte@veteaunclick.com" class="text-purple-600 hover:underline text-sm font-medium">
                    Contactar soporte
                </a>
            </div>
        </div>

        <!-- Info de seguridad -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-xs flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                Tu reserva fue cancelada. No se realizó ningún cobro.
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>