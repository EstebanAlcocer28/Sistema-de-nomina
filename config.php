<?php
/**
 * Configuración de base de datos y funciones auxiliares.
 *
 * CREDENCIALES: Se toman de variables de entorno.
 * En producción, defínelas en el servidor (Apache/Nginx) o en un .env.
 * Para desarrollo local se usan los valores por defecto (NO USAR EN PRODUCCIÓN).
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'Eden');

/**
 * Devuelve una conexión mysqli con charset utf8mb4.
 * En caso de error, registra el motivo y detiene la ejecución con un JSON genérico.
 */
function getConnection(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF); // evita warnings públicos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        error_log('Error de conexión BD: ' . $conn->connect_error);
        die(json_encode([
            'success' => false,
            'message' => 'Error interno del servidor. Intente más tarde.'
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Ejecuta una consulta preparada de forma segura.
 *
 * @param mysqli  $conn   Conexión activa
 * @param string  $sql    Sentencia SQL con marcadores ?
 * @param string  $types  Tipos de los parámetros (ej: 'ssi')
 * @param array   $params Valores a enlazar
 * @return array          ['success' => bool, 'message' => string, 'stmt' => mysqli_stmt|null, 'insert_id' => int]
 */
function executeQuery(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    // Verificar coherencia entre tipos y número de parámetros
    if (strlen($types) !== count($params)) {
        error_log('Error en executeQuery: número de parámetros no coincide con los tipos.');
        return [
            'success' => false,
            'message' => 'Error interno al procesar la solicitud.'
        ];
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Error en preparación de consulta: ' . $conn->error);
        return [
            'success' => false,
            'message' => 'Error interno al procesar la solicitud.'
        ];
    }

    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $result = $stmt->execute();
    if (!$result) {
        error_log('Error en ejecución de consulta: ' . $stmt->error);
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Error interno al procesar la solicitud.'
        ];
    }

    return [
        'success'   => true,
        'stmt'      => $stmt,
        'insert_id' => $conn->insert_id
    ];
}

/**
 * Cierra la conexión si está abierta.
 */
function closeConnection($conn): void
{
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}