<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin/login.php');
    exit();
}

require_once 'conexion.php';

// Consulta para obtener todas las notas con datos del cliente
$sql = "SELECT 
            notas.id,
            notas.tipo,
            notas.fecha,
            notas.total,
            clientes.nombre as cliente_nombre,
            clientes.direccion,
            clientes.telefono
        FROM notas 
        INNER JOIN clientes ON notas.cliente_id = clientes.id 
        ORDER BY notas.fecha DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Notas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media screen and (max-width: 640px) {
            .container {
                padding: 1rem !important;
            }
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -1rem;
                padding: 0 1rem;
            }
            .mobile-text {
                font-size: 0.875rem !important;
            }
            .mobile-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }
            .mobile-button {
                width: 100%;
                text-align: center;
            }
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }
            .action-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
                border-radius: 0.25rem;
                text-align: center;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-4 sm:py-8">
        <!-- Banner de bienvenida -->
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 sm:mb-6 rounded-r">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                    <p class="font-medium text-sm sm:text-base">
                        Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </p>
                    <span class="hidden sm:inline">|</span>
                    <span class="text-blue-800 font-medium text-sm sm:text-base">
                        Administrador
                    </span>
                </div>
                <a href="admin/logout.php" 
                   class="text-red-500 hover:text-red-700 text-sm sm:text-base font-medium">
                    Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Encabezado y botones principales -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 sm:mb-6 gap-4">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                Listado de Notas
            </h1>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="index.php" 
                   class="flex-1 sm:flex-none text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm sm:text-base transition-colors">
                    Nueva Nota
                </a>
            </div>
        </div>

        <!-- Tabla de notas -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="table-wrapper">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm"><?php echo $row['id']; ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['tipo'] == 'nota' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo ucfirst($row['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['cliente_nombre']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['telefono']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php echo number_format($row['total'], 2); ?> €
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                            <a href="vernota.php?id=<?php echo $row['id']; ?>" 
                                               class="action-button bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                                                Ver Nota
                                            </a>
                                            <a href="editarnota.php?id=<?php echo $row['id']; ?>" 
                                               class="action-button bg-yellow-100 text-yellow-700 hover:bg-yellow-200">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-sm text-center text-gray-500">
                                    No hay notas registradas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
