<?php
require_once 'config.php';

$sql = "
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    fecha_registro TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lista_todo (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    fecha_creacion TEXT DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio TEXT,
    fecha_fin TEXT,
    meta INTEGER DEFAULT 1,
    progreso INTEGER DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lista_actividades (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    descripcion TEXT,
    fecha_creacion TEXT DEFAULT CURRENT_TIMESTAMP,
    dias TEXT,
    hora TEXT,
    fecha_inicio TEXT,
    fecha_fin TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lista_habitos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    id_usuario INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    descripcion TEXT,
    fecha_inicio TEXT,
    meta_dias INTEGER DEFAULT 30,
    dias_completados INTEGER DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);
";

try {
    $pdo->exec($sql);
    echo "✅ Tablas creadas correctamente";
    echo "<br><br>";
    echo "📋 Ve a: <a href='login.html'>login.html</a>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>