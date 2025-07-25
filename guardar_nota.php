<?php
require_once 'conexion.php';

// Recibe los datos JSON
$datos = json_decode(file_get_contents('php://input'), true);

try {
    $conn->begin_transaction();

    // Insertar cliente
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, direccion, telefono) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $datos['nombre'], $datos['direccion'], $datos['telefono']);
    $stmt->execute();
    $cliente_id = $conn->insert_id;

    // Insertar nota
    $stmt = $conn->prepare("INSERT INTO notas (tipo, fecha, cliente_id, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssid", $datos['tipo'], $datos['fecha'], $cliente_id, $datos['total']);
    $stmt->execute();
    $nota_id = $conn->insert_id;

    // Insertar items
    $stmt = $conn->prepare("INSERT INTO nota_items (nota_id, unidades, descripcion, precio, total) VALUES (?, ?, ?, ?, ?)");
    foreach ($datos['items'] as $item) {
        if (!empty($item['units']) || !empty($item['description']) || !empty($item['price'])) {
            $stmt->bind_param("idsdd", 
                $nota_id, 
                $item['units'], 
                $item['description'], 
                $item['price'], 
                $item['total']
            );
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'id' => $nota_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>

