<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/CursoController.php';
require_once __DIR__ . '/../app/controllers/InscripcionController.php';
require_once __DIR__ . '/../app/controllers/PerfilController.php';

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


    // --- PERFIL ---
    case 'perfil':
        PerfilController::miPerfil();
        break;

    case 'mis_cursos':
        PerfilController::misCursos();
        break;

    case 'actualizar_perfil';
        PerfilController::actualizarPerfil();
        break;

    case 'actualizar_password':
        PerfilController::actualizarPass();

    case 'guardar_intereses':
        PerfilController::guardarIntereses();
        break;

    case 'solicitar_pro':
        PerfilController::solicitarPro();
        break;

    case 'gestionar_solicitud_pro':
        PerfilController::gestionarSolicitudPro();
        break;

    case 'ver_comprobante':
        PerfilController::verComprobante();
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


    // --- INSCRIPCIONES ---
    case 'inscribirse':
        InscripcionController::inscribir();
        break;

    case 'cambiar_estado_inscripcion':
        InscripcionController::cambiarEstado();
        break;


    // --- FORO ---
     case 'crear_pregunta':
        ForoController::crearPregunta();
        break;

    case 'crear_respuesta':
        ForoController::crearRespuesta();
        break;

    case 'aceptar_respuesta':
        ForoController::aceptarRespuesta();
        break;

    case 'eliminar_pregunta':
        ForoController::eliminarPregunta();
        break;

    case 'leer_notificaciones':
        ForoController::leerNotificaciones();
        break;
        
        
    default:
        CursoController::home();
        break;
}
?>