<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Turno - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .option-card { transition: all 0.3s ease; cursor: pointer; }
        .option-card:hover { transform: translateY(-4px); }
        .option-card.selected { border-color: #7c3aed; background: linear-gradient(135deg, #7c3aed10 0%, #a855f710 100%); }
    </style>
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

    <!-- Progress Indicator -->
    <div class="max-w-4xl mx-auto px-4 pt-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">1</div>
                <span class="text-purple-600 font-medium">Cuándo</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center font-bold text-sm">2</div>
                <span class="text-gray-500 font-medium">Quién</span>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-3">¿Cuándo necesitás el turno?</h1>
            <p class="text-gray-600">Seleccioná una opción para continuar</p>
        </div>

        <form action="ver_veterinarios.php" method="GET" id="searchForm">
            <input type="hidden" name="tipo" id="tipoSeleccionado" value="">
            <input type="hidden" name="fecha" id="fechaSeleccionada" value="">
            
            <div class="space-y-4 mb-8">
                <!-- Opción 1: Lo antes posible -->
                <label class="option-card block p-6 bg-white border-2 border-gray-200 rounded-2xl" onclick="selectOption('proximo')">
                    <input type="radio" name="tipo_radio" value="proximo" class="hidden">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="zap" class="text-white w-7 h-7"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-gray-800 mb-1">Lo antes posible</h3>
                            <p class="text-gray-600 text-sm">Mostrame los veterinarios con los turnos más próximos disponibles. Ideal para urgencias.</p>
                        </div>
                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full check-icon"></div>
                    </div>
                </label>

                <!-- Opción 2: Fecha específica -->
                <label class="option-card block p-6 bg-white border-2 border-gray-200 rounded-2xl" onclick="selectOption('fecha')">
                    <input type="radio" name="tipo_radio" value="fecha" class="hidden">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="calendar" class="text-white w-7 h-7"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg text-gray-800 mb-1">Fecha específica</h3>
                            <p class="text-gray-600 text-sm">Elegí el día exacto que preferís para la consulta.</p>
                        </div>
                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full check-icon"></div>
                    </div>
                </label>
            </div>

            <!-- Selector de Fecha (oculto por defecto) -->
            <div id="fechaContainer" class="hidden mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccioná la fecha</label>
                <input type="date" id="fechaInput" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"
                       class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition text-lg">
            </div>

            <button type="submit" id="submitBtn" disabled
                    class="w-full bg-gray-300 text-white p-4 rounded-xl font-bold text-lg transition cursor-not-allowed">
                Continuar
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver al inicio
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function selectOption(tipo) {
            // Actualizar radio buttons
            document.querySelectorAll('input[name="tipo_radio"]').forEach(radio => {
                radio.checked = radio.value === tipo;
            });

            // Actualizar estilo de cards
            document.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('.check-icon').classList.remove('bg-purple-600', 'border-purple-600');
            });
            
            event.currentTarget.classList.add('selected');
            event.currentTarget.querySelector('.check-icon').classList.add('bg-purple-600', 'border-purple-600');

            // Mostrar/ocultar selector de fecha
            const fechaContainer = document.getElementById('fechaContainer');
            const fechaInput = document.getElementById('fechaInput');
            
            if (tipo === 'fecha') {
                fechaContainer.classList.remove('hidden');
                fechaInput.required = true;
            } else {
                fechaContainer.classList.add('hidden');
                fechaInput.required = false;
            }

            // Guardar selección
            document.getElementById('tipoSeleccionado').value = tipo;
            document.getElementById('fechaSeleccionada').value = fechaInput.value;

            // Habilitar botón
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
            submitBtn.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-indigo-600', 'hover:from-purple-700', 'hover:to-indigo-700');
        }

        // Actualizar fecha si cambia
        document.getElementById('fechaInput').addEventListener('change', function() {
            document.getElementById('fechaSeleccionada').value = this.value;
        });
    </script>
</body>
</html>