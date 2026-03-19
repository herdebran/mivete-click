<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AppointmentController($db);

$tipo = $_GET['tipo'] ?? 'proximo';
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$veterinariosDisponibles = [];

if ($tipo === 'fecha' && !empty($fecha)) {
    $veterinariosDisponibles = $controller->getProfessionalsWithAvailability($fecha);
} else {
    $veterinariosDisponibles = $controller->getProfessionalsWithNextAvailability();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarios Disponibles - Vete a un Click</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .vet-card { transition: all 0.3s ease; }
        .vet-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
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
                <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <span class="text-green-600 font-medium">Cuándo</span>
            </div>
            <div class="w-12 h-0.5 bg-purple-600"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">2</div>
                <span class="text-purple-600 font-medium">Quién</span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-3">
                <?php if ($tipo === 'proximo'): ?>
                    Veterinarios con turnos próximos
                <?php else: ?>
                    Veterinarios disponibles
                <?php endif; ?>
            </h1>
            <p class="text-gray-600">
                <?php if ($tipo === 'fecha'): ?>
                    Para el <?php echo date('d/m/Y', strtotime($fecha)); ?>
                <?php else: ?>
                    Ordenados por disponibilidad más cercana
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($veterinariosDisponibles)): ?>
        <!-- Mensaje de error mejorado -->
        <div class="bg-white rounded-3xl shadow-lg p-12 text-center border border-gray-100">
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="calendar-x" class="text-orange-500 w-10 h-10"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">No hay turnos disponibles</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                <?php if ($tipo === 'fecha'): ?>
                    No encontramos veterinarios disponibles para esa fecha. Probá con otra fecha o seleccioná "Lo antes posible".
                <?php else: ?>
                    No hay turnos disponibles en este momento. Por favor, volvé a intentar más tarde.
                <?php endif; ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="buscar_turno.php" 
                   class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    Buscar otra vez
                </a>
                <a href="index.php" 
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Volver al inicio
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Lista de veterinarios -->
        <div class="space-y-4 mb-8">
            <?php foreach ($veterinariosDisponibles as $vet): ?>
            <div class="vet-card bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user-circle" class="text-white w-8 h-8"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">
                                Dr./Dra. <?php echo htmlspecialchars($vet['nombre'] . ' ' . $vet['apellido']); ?>
                            </h3>
                            <p class="text-sm text-gray-500 flex items-center gap-1">
                                <i data-lucide="badge-check" class="w-4 h-4"></i>
                                Matrícula: <?php echo htmlspecialchars($vet['matricula']); ?>
                            </p>
                            <?php if (isset($vet['primer_turno'])): ?>
                            <p class="text-sm text-green-600 font-medium flex items-center gap-1 mt-1">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                Próximo: <?php echo htmlspecialchars($vet['primer_turno']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- ✅ CORREGIDO: vet_id en lugar de vat_id -->
                    <a href="reservar.php?vet_id=<?php echo $vet['id']; ?>&fecha=<?php echo $fecha; ?>&tipo=<?php echo $tipo; ?>" 
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:from-purple-700 hover:to-indigo-700 transition whitespace-nowrap">
                        Reservar turno
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <a href="buscar_turno.php" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver a buscar
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>