<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Eden');

// Crear conexión
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Función para ejecutar consultas preparadas
function executeQuery($conn, $sql, $types = '', $params = []) {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Error en la preparación: ' . $conn->error
        ];
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    
    if (!$result) {
        return [
            'success' => false,
            'message' => 'Error en la ejecución: ' . $stmt->error
        ];
    }
    
    return [
        'success' => true,
        'stmt' => $stmt,
        'insert_id' => $conn->insert_id
    ];
}

// Función para cerrar conexión
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>