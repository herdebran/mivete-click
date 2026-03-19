<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/AppointmentController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new AppointmentController($db);
$vets = $controller->getAllProfessionals();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarios Disponibles - VetConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header -->
    <nav class="bg-white shadow-md">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <a href="index.php" class="text-xl font-bold text-gray-800">🐾 VetConnect</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto p-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Veterinarios Disponibles</h1>
        
        <?php if (empty($vets)): ?>
        <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg text-center">
            <p class="text-yellow-800">Aún no hay veterinarios registrados.</p>
        </div>
        <?php else: ?>
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($vets as $vet): ?>
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl">
                        🩺
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">
                            Dr./Dra. <?php echo htmlspecialchars($vet['nombre'] . ' ' . $vet['apellido']); ?>
                        </h3>
                        <p class="text-sm text-gray-500">Matrícula: <?php echo htmlspecialchars($vet['matricula']); ?></p>
                    </div>
                </div>
                
                <a href="reservar.php?vet_id=<?php echo $vet['id']; ?>" 
                   class="block w-full bg-blue-600 text-white text-center p-3 rounded font-bold hover:bg-blue-700 transition">
                    Reservar Turno
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-700">← Volver al inicio</a>
        </div>
    </div>

</body>
</html>