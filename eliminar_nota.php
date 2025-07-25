<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $nota_id = (int)$_GET['id'];
    
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("DELETE FROM nota_items WHERE nota_id = ?");
        $stmt->bind_param("i", $nota_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM notas WHERE id = ?");
        $stmt->bind_param("i", $nota_id);
        $stmt->execute();
        
        $conn->commit();
        
        $stmt->close();
        $conn->close();
        
        header("Location: vernotas.php?mensaje=eliminada");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $stmt->close(); 
        $conn->close();
        echo "Error al eliminar la nota: " . $e->getMessage();
        echo "Información de depuración:<br>";
        echo "Nota ID: " . $nota_id . "<br>";
        echo "Query Items: " . $stmt->error . "<br>";
        echo "Código de error: " . $stmt->errno . "<br>";
    }
} else {
    header("Location: vernotas.php");
    exit;
}
?>
