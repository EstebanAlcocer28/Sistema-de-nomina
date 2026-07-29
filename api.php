<?php
/**
 * API REST para operaciones CRUD
 * Payroll System - Eden
 *
 * NOTA: Este sistema está diseñado para uso local.
 * Si se expone a internet, es indispensable implementar
 * autenticación y autorización (sesiones, tokens, etc.).
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// Solo se permiten los métodos declarados en cada acción
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$conn = getConnection();

// ── Funciones auxiliares ─────────────────────────────
function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Formato de datos no válido']);
        exit;
    }
    return $data ?? [];
}

function validarId($id): int {
    $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID no válido']);
        exit;
    }
    return $id;
}

function sanitizarTexto($str): string {
    return trim(strip_tags($str));
}

function errorLogYResponder($mensaje, $detalle = '') {
    if ($detalle) error_log($detalle);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

// ── Enrutamiento ─────────────────────────────────────
switch ($action) {

    // ==================== SUCURSALES ====================
    case 'getSucursales':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $sql = "SELECT * FROM sucursales ORDER BY nombre";
        $result = $conn->query($sql);
        $sucursales = [];
        while ($row = $result->fetch_assoc()) {
            $sucursales[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $sucursales]);
        break;

    case 'createSucursal':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $data = getJsonInput();
        $nombre = sanitizarTexto($data['nombre'] ?? '');
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre de la sucursal es obligatorio']);
            break;
        }
        $direccion = isset($data['direccion']) ? sanitizarTexto($data['direccion']) : null;
        $telefono  = isset($data['telefono'])  ? sanitizarTexto($data['telefono'])  : null;
        $result = executeQuery($conn, 
            "INSERT INTO sucursales (nombre, direccion, telefono) VALUES (?, ?, ?)",
            'sss', [$nombre, $direccion, $telefono]
        );
        echo json_encode($result);
        break;

    case 'updateSucursal':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $data = getJsonInput();
        $id = validarId($data['id'] ?? 0);
        $nombre = sanitizarTexto($data['nombre'] ?? '');
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre de la sucursal es obligatorio']);
            break;
        }
        $direccion = isset($data['direccion']) ? sanitizarTexto($data['direccion']) : null;
        $telefono  = isset($data['telefono'])  ? sanitizarTexto($data['telefono'])  : null;
        $result = executeQuery($conn,
            "UPDATE sucursales SET nombre = ?, direccion = ?, telefono = ? WHERE id = ?",
            'sssi', [$nombre, $direccion, $telefono, $id]
        );
        echo json_encode($result);
        break;

    case 'deleteSucursal':
        // Solo se permite POST para operaciones destructivas
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido (use POST)']);
            break;
        }
        $data = getJsonInput();
        $id = validarId($data['id'] ?? 0);

        // Verificar empleados vinculados (consulta preparada)
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as total FROM empleados WHERE sucursal_id = ?");
        if (!$stmtCheck) {
            error_log('Error preparando verificación deleteSucursal: ' . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Error interno']);
            break;
        }
        $stmtCheck->bind_param('i', $id);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $count = $resCheck->fetch_assoc()['total'];
        $stmtCheck->close();

        if ($count > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar. La sucursal tiene empleados asignados.'
            ]);
        } else {
            $result = executeQuery($conn, "DELETE FROM sucursales WHERE id = ?", 'i', [$id]);
            echo json_encode($result);
        }
        break;

    // ==================== EMPLEADOS ====================
    case 'getEmpleados':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
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
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $sucursal_id = validarId($_GET['sucursal_id'] ?? 0);
        $sql = "SELECT * FROM empleados WHERE sucursal_id = ? AND activo = 1 ORDER BY nombre";
        $result = executeQuery($conn, $sql, 'i', [$sucursal_id]);
        if ($result['success']) {
            $stmt = $result['stmt'];
            $res = $stmt->get_result();
            $empleados = [];
            while ($row = $res->fetch_assoc()) {
                $empleados[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $empleados]);
        } else {
            echo json_encode($result);
        }
        break;

    case 'createEmpleado':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $data = getJsonInput();
        $nombre = sanitizarTexto($data['nombre'] ?? '');
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del empleado es obligatorio']);
            break;
        }
        $sueldo_base = filter_var($data['sueldo_base'] ?? 0, FILTER_VALIDATE_FLOAT);
        if ($sueldo_base === false || $sueldo_base < 0) {
            echo json_encode(['success' => false, 'message' => 'Sueldo base no válido']);
            break;
        }
        $sucursal_id = validarId($data['sucursal_id'] ?? 0);
        $fecha_ingreso = $data['fecha_ingreso'] ?? null;
        if ($fecha_ingreso && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha no válido']);
            break;
        }
        $result = executeQuery($conn,
            "INSERT INTO empleados (nombre, sueldo_base, sucursal_id, fecha_ingreso) VALUES (?, ?, ?, ?)",
            'sdis', [$nombre, $sueldo_base, $sucursal_id, $fecha_ingreso]
        );
        echo json_encode($result);
        break;

    case 'updateEmpleado':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
        }
        $data = getJsonInput();
        $id = validarId($data['id'] ?? 0);
        $nombre = sanitizarTexto($data['nombre'] ?? '');
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del empleado es obligatorio']);
            break;
        }
        $sueldo_base = filter_var($data['sueldo_base'] ?? 0, FILTER_VALIDATE_FLOAT);
        if ($sueldo_base === false || $sueldo_base < 0) {
            echo json_encode(['success' => false, 'message' => 'Sueldo base no válido']);
            break;
        }
        $sucursal_id = validarId($data['sucursal_id'] ?? 0);
        $fecha_ingreso = $data['fecha_ingreso'] ?? null;
        if ($fecha_ingreso && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ingreso)) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha no válido']);
            break;
        }
        $result = executeQuery($conn,
            "UPDATE empleados SET nombre = ?, sueldo_base = ?, sucursal_id = ?, fecha_ingreso = ? WHERE id = ?",
            'sdisi', [$nombre, $sueldo_base, $sucursal_id, $fecha_ingreso, $id]
        );
        echo json_encode($result);
        break;

    case 'deleteEmpleado':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido (use POST)']);
            break;
        }
        $data = getJsonInput();
        $id = validarId($data['id'] ?? 0);
        // Borrado físico (hard delete)
        $result = executeQuery($conn, "DELETE FROM empleados WHERE id = ?", 'i', [$id]);
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
}

closeConnection($conn);