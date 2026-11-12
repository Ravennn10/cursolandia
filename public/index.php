<?php
session_start();

require_once __DIR__ . '/../app/controllers/AuthController.php';

$action = $_GET['action'] ?? 'login';

switch ($action){
    case 'login':
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;

    case 'procesar_login':
        AuthController::login();
        break;

    case 'registro':
        require_once __DIR__ . '/../app/views/auth/registro.php';
        break;

    case 'procesar_registro':
        AuthController::registro();
        break;
        
    case 'logout':
        AuthController::logout();
        break;

    case 'catalogo':
        // Vista temporal después del login exitoso
        echo "<h2>¡Bienvenido " . htmlspecialchars($_SESSION['usuario_nom']) . "!</h2>";
        echo "<p>Rol: " . htmlspecialchars($_SESSION['rol_nombre']) . "</p>";
        echo "<a href='index.php?action=logout'>Cerrar Sesión</a>";
        break;

    default:
        require_once __DIR__ . '/../app/views/auth/login.php';
        break;    
}


?>