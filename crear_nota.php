<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin/login.php');
    exit();
}

require_once 'conexion.php';

// Si se envía el formulario para crear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Crear cliente nuevo
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, direccion, telefono) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", 
            $_POST['nombre'],
            $_POST['direccion'],
            $_POST['telefono']
        );
        $stmt->execute();
        $cliente_id = $conn->insert_id;

        // Crear nota nueva
        $stmt = $conn->prepare("INSERT INTO notas (cliente_id, tipo, fecha, total) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", 
            $cliente_id,
            $_POST['tipo'],
            $_POST['fecha'],
            $_POST['total']
        );
        $stmt->execute();
        $nota_id = $conn->insert_id;

        // Insertar items
        $stmt = $conn->prepare("INSERT INTO nota_items (nota_id, unidades, descripcion, precio, total) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['items'] as $item) {
            if (!empty($item['unidades']) || !empty($item['descripcion']) || !empty($item['precio'])) {
                $total = floatval($item['unidades']) * floatval($item['precio']);
                $stmt->bind_param("idsdd", 
                    $nota_id,
                    $item['unidades'],
                    $item['descripcion'],
                    $item['precio'],
                    $total
                );
                $stmt->execute();
            }
        }

        $conn->commit();
        header('Location: vernota.php?id=' . $nota_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error al crear la nota: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Nota</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media screen and (max-width: 640px) {
            .container { padding: 0.5rem !important; }
            .input-field { font-size: 16px !important; }
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
            }
        }

        .input-field {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }

        table td {
            padding: 0.5rem;
            vertical-align: top;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php if (isset($error)): ?>
        <div class="max-w-4xl mx-auto mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Crear Nueva Nota
            </h1>
            <a href="vernotas.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Volver</a>
        </div>

        <form method="POST" class="space-y-6">
            <!-- Tipo y Fecha -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento</label>
                        <select name="tipo" class="input-field">
                            <option value="nota">Nota</option>
                            <option value="presupuesto">Presupuesto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Datos del Cliente -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-4">Datos del Cliente</h2>
                <div class="space-y-4">
                    <input type="text" name="nombre" placeholder="Nombre" class="input-field">
                    <input type="text" name="direccion" placeholder="Dirección" class="input-field">
                    <input type="text" name="telefono" placeholder="Teléfono" class="input-field">
                </div>
            </div>

            <!-- Tabla de Items -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-4">Items</h2>
                <div class="table-responsive">
                    <table class="w-full" id="itemsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left p-3">Unidades</th>
                                <th class="text-left p-3">Descripción</th>
                                <th class="text-left p-3">Precio</th>
                                <th class="text-left p-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <tr>
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo $i; ?>][unidades]"
                                           class="input-field item-input" 
                                           step="any"
                                           onchange="updateTotal(<?php echo $i; ?>)">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="items[<?php echo $i; ?>][descripcion]"
                                           class="input-field">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo $i; ?>][precio]"
                                           class="input-field item-input"
                                           step="any"
                                           onchange="updateTotal(<?php echo $i; ?>)">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo $i; ?>][total]"
                                           value="0"
                                           class="input-field row-total"
                                           readonly>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <span class="font-bold">Total: </span>
                    <input type="number" name="total" id="totalGeneral" 
                           value="0" 
                           class="input-field w-32" readonly>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Crear Nota
                </button>
                <a href="vernotas.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        function updateTotal(index) {
            const unidades = parseFloat(document.getElementsByName(`items[${index}][unidades]`)[0].value) || 0;
            const precio = parseFloat(document.getElementsByName(`items[${index}][precio]`)[0].value) || 0;
            const totalField = document.getElementsByName(`items[${index}][total]`)[0];
            
            const total = unidades * precio;
            totalField.value = total.toFixed(2);
            
            calculateGeneralTotal();
        }

        function calculateGeneralTotal() {
            const totals = document.getElementsByClassName('row-total');
            let sum = 0;
            
            for (let total of totals) {
                sum += parseFloat(total.value) || 0;
            }
            
            document.getElementById('totalGeneral').value = sum.toFixed(2);
        }

        // Calcular total inicial
        calculateGeneralTotal();
    </script>
</body>
</html>
<?php $conn->close(); ?>