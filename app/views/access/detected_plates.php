<?php
/**
 * Vista de placas detectadas por el sistema de reconocimiento
 */

// Esta vista ya está dentro del framework, no necesita require
// Conectar a base de datos
$db = Database::getInstance()->getConnection();

// Obtener filtro de estado
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Construir consulta
$whereClause = '';
$params = [];

if ($statusFilter === 'new') {
    $whereClause = 'WHERE dp.is_match = 0';
} elseif ($statusFilter === 'known') {
    $whereClause = 'WHERE dp.is_match = 1';
}

$sql = "
    SELECT 
        dp.id,
        dp.plate_text,
        dp.captured_at,
        dp.is_match,
        dp.matched_vehicle_id,
        dp.payload_json,
        dp.status,
        dp.notes,
        v.brand,
        v.model,
        v.color
    FROM detected_plates dp
    LEFT JOIN vehicles v ON dp.matched_vehicle_id = v.id
    $whereClause
    ORDER BY dp.captured_at DESC
    LIMIT 100
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$detectedPlates = $stmt->fetchAll();

// Estadísticas
$statsStmt = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_match = 1 THEN 1 ELSE 0 END) as known,
        SUM(CASE WHEN is_match = 0 THEN 1 ELSE 0 END) as new
    FROM detected_plates
");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placas Detectadas - Sistema Residencial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            <i class="fas fa-camera text-blue-600 mr-3"></i>
                            Placas Detectadas
                        </h1>
                        <p class="text-gray-600 mt-1">Sistema de reconocimiento automático de placas</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/access" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Total Detectadas</p>
                            <p class="text-4xl font-bold mt-2"><?php echo $stats['total']; ?></p>
                        </div>
                        <i class="fas fa-car text-6xl opacity-20"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Registradas</p>
                            <p class="text-4xl font-bold mt-2"><?php echo $stats['known']; ?></p>
                        </div>
                        <i class="fas fa-check-circle text-6xl opacity-20"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">No Registradas</p>
                            <p class="text-4xl font-bold mt-2"><?php echo $stats['new']; ?></p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-6xl opacity-20"></i>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
                <div class="flex gap-3">
                    <a href="?status=all" class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                        Todas
                    </a>
                    <a href="?status=new" class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'new' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                        No Registradas
                    </a>
                    <a href="?status=known" class="px-4 py-2 rounded-lg <?php echo $statusFilter === 'known' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                        Registradas
                    </a>
                </div>
            </div>

            <!-- Lista de placas -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if (empty($detectedPlates)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">No hay placas detectadas</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        <?php foreach ($detectedPlates as $plate): 
                            $payload = json_decode($plate['payload_json'], true);
                            $imagePath = isset($payload['image_path']) ? $payload['image_path'] : null;
                            
                            // Construir URL completa de la imagen
                            if ($imagePath) {
                                // Si la ruta empieza con /placas, usar directamente
                                if (strpos($imagePath, '/placas') === 0) {
                                    $imageUrl = 'https://janetzy.shop' . $imagePath;
                                } else {
                                    $imageUrl = BASE_URL . '/' . ltrim($imagePath, '/');
                                }
                            } else {
                                $imageUrl = null;
                            }
                        ?>
                            <div class="border-2 <?php echo $plate['is_match'] ? 'border-green-500' : 'border-orange-500'; ?> rounded-lg overflow-hidden hover:shadow-xl transition">
                                <!-- Imagen -->
                                <?php if ($imageUrl): ?>
                                    <div class="aspect-video bg-gray-900 relative">
                                        <img src="<?php echo $imageUrl; ?>" 
                                             alt="Placa <?php echo htmlspecialchars($plate['plate_text']); ?>"
                                             class="w-full h-full object-contain"
                                             onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-white\'><i class=\'fas fa-image text-4xl opacity-50\'></i></div>'"
                                             onclick="openImageModal('<?php echo $imageUrl; ?>')"
                                             style="cursor: pointer;">
                                        <div class="absolute top-2 right-2">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $plate['is_match'] ? 'bg-green-500' : 'bg-orange-500'; ?> text-white">
                                                <?php echo $plate['is_match'] ? 'REGISTRADA' : 'NUEVA'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="aspect-video bg-gray-800 flex items-center justify-center">
                                        <div class="text-center text-gray-500">
                                            <i class="fas fa-image text-4xl mb-2"></i>
                                            <p class="text-sm">Sin imagen</p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Información -->
                                <div class="p-4">
                                    <div class="text-center mb-3">
                                        <div class="inline-block bg-gray-800 text-white px-6 py-2 rounded-lg">
                                            <span class="text-2xl font-bold tracking-wider"><?php echo htmlspecialchars($plate['plate_text']); ?></span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-clock w-5"></i>
                                            <span><?php echo date('d/m/Y H:i:s', strtotime($plate['captured_at'])); ?></span>
                                        </div>

                                        <?php if ($plate['is_match'] && $plate['brand']): ?>
                                            <div class="flex items-center text-green-700">
                                                <i class="fas fa-car w-5"></i>
                                                <span><?php echo htmlspecialchars($plate['brand'] . ' ' . $plate['model']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($plate['notes']): ?>
                                            <div class="text-gray-500 text-xs mt-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                <?php echo htmlspecialchars($plate['notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para ver imagen completa -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="max-w-5xl w-full">
            <img id="modalImage" src="" alt="Imagen completa" class="w-full h-auto rounded-lg">
        </div>
    </div>

    <script>
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }
    </script>
</body>
</html>
