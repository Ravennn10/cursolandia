<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CursoController.php';

$action = $_GET['action'] ?? 'home';

switch ($action) {
    // --- INICIO ---
    case 'home':
        CursoController::home();
        break;

    // --- AUTENTICACIÓN ---
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

    // --- CURSOS ---
    case 'catalogo':
        CursoController::catalogo();
        break;

    case 'crear_curso':
        CursoController::mostrarCrearCurso();
        break;

    case 'guardar_curso':
        CursoController::guardar();
        break;

    case 'detalle_curso':
        CursoController::detalle();
        break;

    case 'editar_curso':
        CursoController::mostrarEditarCurso();
        break;

    case 'actualizar_curso':
        CursoController::editarCurso();
        break;

    case 'eliminar_curso':
        CursoController::eliminar();
        break;

    case 'subir_material':
        CursoController::subirMaterial();
        break;

    case 'descargar_material':
        CursoController::descargarMaterial();
        break;

    default:
        CursoController::home();
        break;
}
?>