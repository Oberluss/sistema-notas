<?php
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin/login.php');
    exit();
}

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
            clientes.telefono as cliente_telefono,
            clientes.id as cliente_id
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

// Obtener items de la nota
$sql_items = "SELECT * FROM nota_items WHERE nota_id = ? ORDER BY id ASC";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $nota_id);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);

// Si se envía el formulario para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Actualizar cliente
        $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, direccion = ?, telefono = ? WHERE id = ?");
        $stmt->bind_param("sssi", 
            $_POST['nombre'],
            $_POST['direccion'],
            $_POST['telefono'],
            $nota['cliente_id']
        );
        $stmt->execute();

        // Actualizar nota
        $stmt = $conn->prepare("UPDATE notas SET tipo = ?, fecha = ?, total = ? WHERE id = ?");
        $stmt->bind_param("ssdi", 
            $_POST['tipo'],
            $_POST['fecha'],
            $_POST['total'],
            $nota_id
        );
        $stmt->execute();

        // Eliminar items antiguos
        $stmt = $conn->prepare("DELETE FROM nota_items WHERE nota_id = ?");
        $stmt->bind_param("i", $nota_id);
        $stmt->execute();

        // Insertar nuevos items
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
        $error = "Error al actualizar la nota: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar <?php echo ucfirst($nota['tipo']); ?> #<?php echo $nota['id']; ?></title>
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
                Editar <?php echo ucfirst($nota['tipo']); ?> #<?php echo $nota['id']; ?>
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
                            <option value="nota" <?php echo $nota['tipo'] == 'nota' ? 'selected' : ''; ?>>Nota</option>
                            <option value="presupuesto" <?php echo $nota['tipo'] == 'presupuesto' ? 'selected' : ''; ?>>Presupuesto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                        <input type="date" name="fecha" value="<?php echo $nota['fecha']; ?>" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Datos del Cliente -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold mb-4">Datos del Cliente</h2>
                <div class="space-y-4">
                    <input type="text" name="nombre" placeholder="Nombre" 
                           value="<?php echo htmlspecialchars($nota['cliente_nombre']); ?>" 
                           class="input-field">
                    <input type="text" name="direccion" placeholder="Dirección" 
                           value="<?php echo htmlspecialchars($nota['cliente_direccion']); ?>" 
                           class="input-field">
                    <input type="text" name="telefono" placeholder="Teléfono" 
                           value="<?php echo htmlspecialchars($nota['cliente_telefono']); ?>" 
                           class="input-field">
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
                            <?php 
                            // Mostrar items existentes
                            if(!empty($items)) {
                                foreach ($items as $index => $item): ?>
                                <tr>
                                    <td>
                                        <input type="number" 
                                               name="items[<?php echo $index; ?>][unidades]"
                                               value="<?php echo htmlspecialchars($item['unidades']); ?>"
                                               class="input-field item-input" 
                                               step="any"
                                               onchange="updateTotal(<?php echo $index; ?>)">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="items[<?php echo $index; ?>][descripcion]"
                                               value="<?php echo htmlspecialchars($item['descripcion']); ?>"
                                               class="input-field">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="items[<?php echo $index; ?>][precio]"
                                               value="<?php echo htmlspecialchars($item['precio']); ?>"
                                               class="input-field item-input"
                                               step="any"
                                               onchange="updateTotal(<?php echo $index; ?>)">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="items[<?php echo $index; ?>][total]"
                                               value="<?php echo htmlspecialchars($item['total']); ?>"
                                               class="input-field row-total"
                                               readonly>
                                    </td>
                                </tr>
                                <?php endforeach;
                            } ?>
                            
                            <!-- Fila vacía siempre visible -->
                            <tr class="border-t border-gray-200">
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo isset($index) ? $index + 1 : 0; ?>][unidades]"
                                           value=""
                                           class="input-field item-input" 
                                           step="any"
                                           onchange="updateTotal(<?php echo isset($index) ? $index + 1 : 0; ?>)">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="items[<?php echo isset($index) ? $index + 1 : 0; ?>][descripcion]"
                                           value=""
                                           class="input-field">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo isset($index) ? $index + 1 : 0; ?>][precio]"
                                           value=""
                                           class="input-field item-input"
                                           step="any"
                                           onchange="updateTotal(<?php echo isset($index) ? $index + 1 : 0; ?>)">
                                </td>
                                <td>
                                    <input type="number" 
                                           name="items[<?php echo isset($index) ? $index + 1 : 0; ?>][total]"
                                           value="0"
                                           class="input-field row-total"
                                           readonly>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <span class="font-bold">Total: </span>
                    <input type="number" name="total" id="totalGeneral" 
                           value="<?php echo $nota['total']; ?>" 
                           class="input-field w-32" readonly>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                    Guardar Cambios
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
