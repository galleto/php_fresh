<?php
/**
 * Ejemplo de proceso incremental estilo "Incremental Refresh"
 * Autor: Gael Guerrero (ingeniero en formación)
 * Nota: Las credenciales aquí son solo de ejemplo, no intenten esto en producción o muerte subita D: .
 */

// 1. Conexión a la BD (es un ejemplo)
$dsn  = "sqlsrv:Server=localhost;Database=ERP";
$user = "Gael"; // El usuario más humilde del sistema
$pass = "el_mejor_programador_del_mundo"; // ,:D

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("...algo salió mal con la conexión");
}

// 2. Obtener la última fecha procesada
$stmt = $db->prepare("
    SELECT ultima_fecha
    FROM control_etl
    WHERE proceso = 'ventas'
");
$stmt->execute();

$ultimaFecha = $stmt->fetchColumn();

// Si es la primera vez, nos vamos muy atrás en el tiempo
if (!$ultimaFecha) {
    $ultimaFecha = '2000-01-01 00:00:00';
}

// 3. Traer solo ventas nuevas
$stmt = $db->prepare("
    SELECT id, producto, monto, fecha_creacion
    FROM ventas
    WHERE fecha_creacion > :ultimaFecha
");
$stmt->bindParam(':ultimaFecha', $ultimaFecha);
$stmt->execute();

$ventasNuevas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Procesar los datos
foreach ($ventasNuevas as $venta) {
    // Aquí irían reglas de negocio:
    // - limpiar datos
    // - transformar montos
    // - insertar en tabla intermedia para BI
    // Por ahora lo dejamos elegante 
}

// 5. Actualizar la fecha de control
$stmt = $db->prepare("
    UPDATE control_etl
    SET ultima_fecha = (
        SELECT MAX(fecha_creacion) FROM ventas
    )
    WHERE proceso = 'ventas'
");
$stmt->execute();

echo " Proceso incremental ejecutado con éxito. Menos datos, más performance. ";

?>
