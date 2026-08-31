<?php
require_once 'config.php';

// Ver todas las tablas
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$tablas = $tables->fetchAll();

echo "<h1>📊 Base de Datos - Mis Listas</h1>";

foreach ($tablas as $t) {
    $nombre = $t['name'];
    echo "<h2>📋 Tabla: $nombre</h2>";
    
    // Obtener datos
    $stmt = $pdo->query("SELECT * FROM $nombre");
    $datos = $stmt->fetchAll();
    
    if (count($datos) === 0) {
        echo "<p><em>Sin registros</em></p>";
        continue;
    }
    
    // Mostrar tabla
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;margin-bottom:20px;'>";
    echo "<tr>";
    foreach (array_keys($datos[0]) as $col) {
        echo "<th style='background:#1a237e;color:white;'>$col</th>";
    }
    echo "</tr>";
    
    foreach ($datos as $fila) {
        echo "<tr>";
        foreach ($fila as $valor) {
            echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>