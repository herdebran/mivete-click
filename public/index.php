<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vete a un Click - Consultas Veterinarias Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <!-- ESPACIO PARA TU LOGO -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                </div>
                <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Vete a un Click
                </h1>
            </div>
            
            <a href="profesionales/" class="text-sm text-gray-600 hover:text-purple-600 transition flex items-center gap-2">
                <i data-lucide="user-circle" class="w-4 h-4"></i>
                Soy Veterinario
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="gradient-bg text-white py-20">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full mb-6">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span class="text-sm">Atención veterinaria 100% online</span>
            </div>
            
            <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                Tu mascota merece<br/>
                <span class="text-yellow-300">la mejor atención</span>
            </h2>
            
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                Consultas con veterinarios certificados desde tu casa. 
                Sin esperas, sin estrés, sin desplazamientos.
            </p>
            
            <a href="buscar_turno.php" 
               class="inline-flex items-center gap-2 bg-white text-purple-600 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-gray-100 transition shadow-lg card-hover">
                <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                Reservar turno ahora
            </a>


            <p class="text-sm text-white/70 mt-4 flex items-center justify-center gap-4">
                <span class="flex items-center gap-1"><i data-lucide="check-circle" class="w-4 h-4"></i> Sin esperas</span>
                <span class="flex items-center gap-1"><i data-lucide="check-circle" class="w-4 h-4"></i> Desde $5.000</span>
                <span class="flex items-center gap-1"><i data-lucide="check-circle" class="w-4 h-4"></i> 100% Online</span>
            </p>
        </div>
    </div>

    <!-- Cómo Funciona -->
    <div class="max-w-6xl mx-auto px-4 py-20">
        <h3 class="text-3xl font-bold text-gray-800 text-center mb-4">¿Cómo funciona?</h3>
        <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">
            Tres simples pasos para que tu mascota reciba la atención que necesita
        </p>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="calendar" class="text-purple-600 w-7 h-7"></i>
                </div>
                <h4 class="font-bold text-xl mb-3 text-gray-800">1. Elegí cuándo</h4>
                <p class="text-gray-600">Seleccioná la fecha que necesitás o pedí el turno más próximo disponible.</p>
            </div>
            
            <div class="bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="credit-card" class="text-indigo-600 w-7 h-7"></i>
                </div>
                <h4 class="font-bold text-xl mb-3 text-gray-800">2. Pagá online</h4>
                <p class="text-gray-600">Pago seguro con Mercado Pago. Recibís confirmación al instante.</p>
            </div>
            
            <div class="bg-white p-8 rounded-3xl shadow-lg card-hover border border-gray-100">
                <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="video" class="text-pink-600 w-7 h-7"></i>
                </div>
                <h4 class="font-bold text-xl mb-3 text-gray-800">3. Consultá por video</h4>
                <p class="text-gray-600">Recibís un link y hablás con el veterinario desde tu dispositivo.</p>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="stethoscope" class="text-white w-6 h-6"></i>
                    </div>
                    <span class="font-bold text-lg">Vete a un Click</span>
                </div>
                <p class="text-gray-400 text-sm">© 2026 Todos los derechos reservados.</p>
                <a href="retomar_reserva.php" class="text-sm text-gray-500 hover:text-purple-600 transition">
                    ¿Ya pagaste? Retomar reserva
                </a>

            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>