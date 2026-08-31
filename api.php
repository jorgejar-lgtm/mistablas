<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$action = $_GET['action'] ?? '';
$tabla = $_GET['tabla'] ?? '';

// ============================================
// REGISTRAR USUARIO
// ============================================
if ($action === 'registrar') {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos || empty($datos['email']) || empty($datos['password'])) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$datos['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'El email ya está registrado']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([
            $datos['nombre'] ?? 'Usuario',
            $datos['email'],
            password_hash($datos['password'], PASSWORD_DEFAULT)
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Usuario registrado']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ============================================
// INICIAR SESIÓN
// ============================================
if ($action === 'login') {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos || empty($datos['email']) || empty($datos['password'])) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$datos['email']]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($datos['password'], $usuario['password'])) {
            echo json_encode([
                'success' => true,
                'usuario' => [
                    'id_usuario' => $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email']
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Email o contraseña incorrectos']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ============================================
// VALIDAR USUARIO - IMPORTANTE: usar GET y POST
// ============================================
$id_usuario = 0;

// Intentar obtener de GET
if (isset($_GET['id_usuario'])) {
    $id_usuario = intval($_GET['id_usuario']);
}

// Si no está en GET, intentar del POST (para guardar)
if ($id_usuario === 0) {
    $datos = json_decode(file_get_contents('php://input'), true);
    if ($datos && isset($datos['id_usuario'])) {
        $id_usuario = intval($datos['id_usuario']);
    }
}

// Si sigue siendo 0, error
if ($id_usuario === 0) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// ============================================
// VERIFICAR TABLA
// ============================================
$tablas_permitidas = ['lista_todo', 'lista_actividades', 'lista_habitos'];
if (!in_array($tabla, $tablas_permitidas)) {
    echo json_encode(['error' => 'Tabla no válida']);
    exit;
}

// ============================================
// OBTENER DATOS
// ============================================
if ($action === 'obtener') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE id_usuario = ? ORDER BY id DESC");
        $stmt->execute([$id_usuario]);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ============================================
// GUARDAR DATOS
// ============================================
if ($action === 'guardar') {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        echo json_encode(['error' => 'Datos inválidos']);
        exit;
    }
    
    // Asegurar que el id_usuario esté en los datos
    $datos['id_usuario'] = $id_usuario;
    
    try {
        $campos = [];
        $valores = [];
        $placeholders = [];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id') {
                $campos[] = $campo;
                $valores[":$campo"] = $valor;
                $placeholders[] = ":$campo";
            }
        }
        
        $sql = "INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'message' => '✅ Guardado']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ============================================
// ELIMINAR DATOS
// ============================================
if ($action === 'eliminar') {
    $id = $_GET['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM $tabla WHERE id = ? AND id_usuario = ?");
        $stmt->execute([$id, $id_usuario]);
        echo json_encode(['success' => true, 'message' => '✅ Eliminado']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
?>