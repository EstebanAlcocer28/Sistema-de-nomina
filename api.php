<?php
/**
 * API REST para operaciones CRUD
 * Payroll System
 */

header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$conn = getConnection();

switch ($action) {
    // ==================== SUCURSALES ====================
    case 'getSucursales':
        $sql = "SELECT * FROM sucursales ORDER BY nombre";
        $result = $conn->query($sql);
        $sucursales = [];
        while ($row = $result->fetch_assoc()) {
            $sucursales[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $sucursales]);
        break;
        
    case 'createSucursal':
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "INSERT INTO sucursales (nombre, direccion, telefono) VALUES (?, ?, ?)";
        $result = executeQuery($conn, $sql, 'sss', [
            $data['nombre'],
            $data['direccion'] ?? null,
            $data['telefono'] ?? null
        ]);
        echo json_encode($result);
        break;
        
    case 'updateSucursal':
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "UPDATE sucursales SET nombre = ?, direccion = ?, telefono = ? WHERE id = ?";
        $result = executeQuery($conn, $sql, 'sssi', [
            $data['nombre'],
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['id']
        ]);
        echo json_encode($result);
        break;
        
    case 'deleteSucursal':
        $id = $_GET['id'] ?? 0;
        // Verificar si tiene empleados
        $check = $conn->query("SELECT COUNT(*) as total FROM empleados WHERE sucursal_id = $id");
        $count = $check->fetch_assoc()['total'];
        
        if ($count > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar. La sucursal tiene empleados asignados.'
            ]);
        } else {
            $sql = "DELETE FROM sucursales WHERE id = ?";
            $result = executeQuery($conn, $sql, 'i', [$id]);
            echo json_encode($result);
        }
        break;
        
    // ==================== EMPLEADOS ====================
    case 'getEmpleados':
        $sql = "SELECT e.*, s.nombre as sucursal_nombre 
                FROM empleados e 
                LEFT JOIN sucursales s ON e.sucursal_id = s.id 
                WHERE e.activo = 1
                ORDER BY e.nombre";
        $result = $conn->query($sql);
        $empleados = [];
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $empleados]);
        break;
        
    case 'getEmpleadosBySucursal':
        $sucursal_id = $_GET['sucursal_id'] ?? 0;
        $sql = "SELECT * FROM empleados WHERE sucursal_id = ? AND activo = 1 ORDER BY nombre";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $sucursal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleados = [];
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $empleados]);
        break;
        
    case 'createEmpleado':
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "INSERT INTO empleados (nombre, sueldo_base, sucursal_id, fecha_ingreso) 
                VALUES (?, ?, ?, ?)";
        $result = executeQuery($conn, $sql, 'sdis', [
            $data['nombre'],
            $data['sueldo_base'],
            $data['sucursal_id'],
            $data['fecha_ingreso'] ?? null
        ]);
        echo json_encode($result);
        break;
        
    case 'updateEmpleado':
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "UPDATE empleados 
                SET nombre = ?, sueldo_base = ?, sucursal_id = ?, fecha_ingreso = ? 
                WHERE id = ?";
        $result = executeQuery($conn, $sql, 'sdisi', [
            $data['nombre'],
            $data['sueldo_base'],
            $data['sucursal_id'],
            $data['fecha_ingreso'] ?? null,
            $data['id']
        ]);
        echo json_encode($result);
        break;
        
   case 'deleteEmpleado':
    $id = $_GET['id'] ?? 0;
    // Hard delete - eliminación permanente
    $sql = "DELETE FROM empleados WHERE id = ?";
    $result = executeQuery($conn, $sql, 'i', [$id]);
    echo json_encode($result);
    break;

  
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
}

closeConnection($conn);
?>