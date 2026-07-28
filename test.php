<?php
$conn = new mysqli('localhost', 'root', '', 'Eden');

// Verificar que sucursal_id=13 existe en sucursales
$r = $conn->query("SELECT * FROM sucursales WHERE id = 13");
echo 'Sucursal 13: ' . $r->num_rows . ' filas<br>';

// El query exacto de la API
$r2 = $conn->query("SELECT e.*, s.nombre as sucursal_nombre 
    FROM empleados e 
    LEFT JOIN sucursales s ON e.sucursal_id = s.id 
    WHERE e.activo = 1
    ORDER BY e.nombre");

echo $r2 ? 'JOIN OK: ' . $r2->num_rows . ' filas' : 'Error JOIN: ' . $conn->error;
?>