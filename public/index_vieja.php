<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetConnect - Consultas Veterinarias Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">🐾 VetConnect</h1>
            <a href="profesionales/" class="text-sm text-gray-600 hover:text-gray-800">
                ¿Sos veterinario? Accedé aquí
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-4xl mx-auto px-4 py-16 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
            Atención veterinaria online<br/><span class="text-blue-600">rápida y accesible</span>
        </h2>
        <p class="text-xl text-gray-600 mb-8">
            Tu mascota no se siente bien? Consultá con veterinarios certificados desde tu casa.
        </p>
        
        <a href="buscar_turno.php" 
           class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-blue-700 transition shadow-lg">
            🚀 Reservar turno ahora
        </a>
        
        <p class="text-sm text-gray-500 mt-4">
            Sin esperas • Sin desplazamientos • Desde $5.000 ARS
        </p>
    </div>

    <!-- Cómo Funciona -->
    <div class="max-w-6xl mx-auto px-4 py-16">
        <h3 class="text-2xl font-bold text-gray-800 text-center mb-12">¿Cómo funciona?</h3>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="text-4xl mb-4">📅</div>
                <h4 class="font-bold text-lg mb-2">1. Elegí fecha</h4>
                <p class="text-gray-600">Seleccioná cuándo necesitás la consulta o pedí el turno más próximo disponible.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="text-4xl mb-4">💳</div>
                <h4 class="font-bold text-lg mb-2">2. Pagá online</h4>
                <p class="text-gray-600">Pago seguro con Mercado Pago. Recibís confirmación inmediata.</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="text-4xl mb-4">📹</div>
                <h4 class="font-bold text-lg mb-2">3. Consultá por video</h4>
                <p class="text-gray-600">Recibís un link y hablás con el veterinario desde tu celular o computadora.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-gray-400">© 2026 VetConnect. Todos los derechos reservados.</p>
            <p class="text-sm text-gray-500 mt-2">
                <a href="profesionales/" class="hover:text-white">Acceso Profesionales</a>
            </p>
        </div>
    </footer>

</body>
</html>