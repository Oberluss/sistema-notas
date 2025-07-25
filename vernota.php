<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    header('Location: vernotas.php');
    exit;
}

$nota_id = (int)$_GET['id'];

// Obtener datos de la nota y cliente
$sql = "SELECT 
            notas.*,
            clientes.nombre as cliente_nombre,
            clientes.direccion as cliente_direccion,
            clientes.telefono as cliente_telefono
        FROM notas 
        INNER JOIN clientes ON notas.cliente_id = clientes.id 
        WHERE notas.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $nota_id);
$stmt->execute();
$nota = $stmt->get_result()->fetch_assoc();

if (!$nota) {
    header('Location: vernotas.php');
    exit;
}

// Consulta de items
$sql_items = "SELECT * FROM nota_items WHERE nota_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $nota_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = [];

while ($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($nota['tipo']); ?> #<?php echo $nota['id']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; }
            .container { max-width: none; padding: 0; }
            .no-print { display: none !important; }
            .shadow-lg { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado con botones -->
        <div class="flex justify-between items-center mb-6 no-print">
            <h1 class="text-2xl font-bold text-gray-800">
                <?php echo ucfirst($nota['tipo']); ?> #<?php echo $nota['id']; ?>
            </h1>
            <div>
                <a href="vernotas.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">Volver</a>
                <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Imprimir</button>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <!-- Detalles de la nota -->
            <div class="mb-6 text-right">
                <div class="text-gray-600">Fecha: <?php echo date('d/m/Y', strtotime($nota['fecha'])); ?></div>
                <div class="text-xl font-bold"><?php echo ucfirst($nota['tipo']); ?> #<?php echo $nota['id']; ?></div>
            </div>

            <!-- Información de empresa y cliente -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Datos de la empresa -->
                <!--div>
                    <h2 class="text-lg font-semibold mb-2">Datos de la Empresa</h2>
                    <div class="text-gray-600">
                        <p class="font-semibold">Tu Empresa S.L.</p>
                        <p>Dirección de la empresa</p>
                        <p>Teléfono: XXX-XXX-XXX</p>
                    </div>
                </div-->
                
                <!-- Datos del cliente -->
                <div>
                    <h2 class="text-lg font-semibold mb-2">Cliente</h2>
                    <div class="text-gray-600">
                        <p class="font-semibold"><?php echo htmlspecialchars($nota['cliente_nombre']); ?></p>
                        <p><?php echo htmlspecialchars($nota['cliente_direccion']); ?></p>
                        <p>Teléfono: <?php echo htmlspecialchars($nota['cliente_telefono']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Tabla de items -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unidades</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($item['unidades']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                    <td class="px-6 py-4 text-right"><?php echo number_format($item['precio'], 2); ?> €</td>
                                    <td class="px-6 py-4 text-right"><?php echo number_format($item['total'], 2); ?> €</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay items en esta nota</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right font-bold">Total:</td>
                            <td class="px-6 py-4 text-right font-bold"><?php echo number_format($nota['total'], 2); ?> €</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

