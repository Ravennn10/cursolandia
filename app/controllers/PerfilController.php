<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Inscripcion.php';

class PerfilController {

    private static function verificarSesion() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    // para la view de mis cursos (creados - inscritos)
    public static function misCursos() {
        self::verificarSesion();
        $usuarioId = $_SESSION['usuario_id'];

        $cursosCreados   = Curso::obtenerCursosCreados($usuarioId);
        $cursosInscripto = Inscripcion::obtenerCursosPorAlumno($usuarioId);

        require_once __DIR__ . '/../views/cursos/mis_cursos.php';
    }

    //para la view de mi perfil(datos, intereses, solicitud pro y panel de admin)
    public static function miPerfil() {
        self::verificarSesion();
        $usuarioId = $_SESSION['usuario_id'];

        $usuario = Usuario::ObtenerPorId($usuarioId);
        $intereses = Usuario::obtenerIntereses($usuarioId);
        $ultimaSolicitud = Usuario::obtenerUltimaSolicitudPro($usuarioId);
        $esAdmin = ((int)($usuario['rol_id'] ?? 0) === 2); //valida si es admin por el id de rol

        $solicitudesPendientes = $esAdmin ? Usuario::obtenerSolicitudesProPendientes() : [];

        require_once __DIR__ . '/../views/perfil/perfil_usuario.php';

    }

    //gaurdar datos y foto de perfil
    public static function actualizarPerfil() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=perfil");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $antecedentes = trim($_POST['antecedentes'] ?? '');

        if(empty($nombre) || empty($apellido)) {
            $_SESSION['error'] = "El nombre y el apellido son obligatorios";
            header("Location: index.php?action=perfil");
            exit;
        }

        $rutaImagen = NULL;
        if(isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen_perfil']['name'], PATHINFO_EXTENSION));
            if(!in_array($ext,['jpg','jpeg','png','webp'])) {
                $_SESSION['error'] = "Formato de imagen inválido.(use JPG, PNG o WEBP)";
                header("Location: index.php?action=perfil");
                exit;
            }

            $dirDestino = __DIR__ . '/../../public/uploads/';
            if(!is_dir($dirDestino)){
                mkdir($dirDestino, 0777, true);
            }

            $nombreArchivo = 'perfil' . $usuarioId . '_' . uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $dirDestino . $nombreArchivo)){
                $rutaImagen = 'uploads/' . $nombreArchivo;
            }
        }

        if(Usuario::actualizarPerfil($usuarioId, $nombre, $apellido, $antecedentes, $rutaImagen)) {
            $_SESSION['usuario_nombre'] = $nombre; 
            $_SESSION['exito'] = "Perfil actualizado!";
        } else {
            $_SESSION['error'] = "Error al actualizar el perfil.";
        }

        header("Location: index.php?action=perfil");
        exit;

    }


    //cambiar contraseña
    public static function actualizarPass() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=perfil");
            exit;
        }

        $nueva = $_POST['password_nueva'] ?? '';
        $confirmar = $_POST['password_confirmar'] ?? '';

        if(strlen($nueva) < 6 || $nueva !== $confirmar) {
            $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres y coincidir.";
            header("Location: index.php?action=perfil");
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        if(Usuario::actualizarContraseña($_SESSION['usuario_id'], $hash)) {
            $_SESSION['exito'] = "COntraseña actualizada.";
        } else {
            $_SESSION['error'] = "Error al cambair contraseña.";
        }

        header("Location: index.php?action=perfil");
        exit;
    }


    //gaurdar etiquetas de interes
    public static function guardarIntereses() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $etiquetas = trim($_POST['intereses'] ?? '');
            Usuario::actualizarIntereses($_SESSION['usuario_id'], $etiquetas);
            $_SESSION['exito'] = "intereses guardados";
        }
        header("Location: index.php?action=perfil");
        exit;
    }


    //enviar solicitud  PRO
    public static function solicitarPro() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=perfil");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $mensaje = trim($_POST['mensaje'] ?? '');

        $ultimaSolicitud = Usuario::obtenerUltimaSolicitudPro($usuarioId);
        if($ultimaSolicitud && $ultimaSolicitud['estado'] === 'pendiente'){
            $_SESSION['error'] = "Ya tiene una solicitud pendiente.";
            header("Location: index.php?action=perfil");
            exit;
        }

        if(!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Debe adjuntar el comprobante...";
            header("Location: index.php?action=perfil");
            exit;
        }

        $dirDestino = __DIR__ . '/../../storage/pagos/';
        if(!is_dir($dirDestino)){
            mkdir($dirDestino, 0777, true);
        }

        $nombreOriginal = basename($_FILES['comprobante']['name']);
        $nombreUnico = uniqid('pago_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

        if(move_uploaded_file($_FILES['comprobante']['tmp_name'], $dirDestino. $nombreUnico)){
            Usuario::crearSolicitudPro($usuarioId, $mensaje, 'storage/pagos/' . $nombreUnico );
            $_SESSION['exito'] = "Solicitud enviada(pendiente de revisión)";
        } else {
            $_SESSION['error'] = "Erros al subir el comprobante.";
        }
        header("Location: index.php?action=perfil");
        exit;

    }

    //admin aprueba o rechaza solicitud
    public static function gestionarSolicitudPro() {
        self::verificarSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=perfil");
            exit;
        }

        $usuarioActual = Usuario::ObtenerPorId($_SESSION['usuario_id']);
        if((int)($usuarioActual['rol_id'] ?? 0) !== 2) {
            $_SESSION['error'] = "Acceso denegado! solo el administrador puede gestionar solicitudes.";
            header("Location: index.php?action=perfil");
            exit;
        }

        $solicitudId = (int)($_POST['solicitud_id'] ?? 0);
        $accion = $_POST['accion'] ?? '';
        $solicitud = Usuario::obtenerSolicitudProPorId($solicitudId);

        if (!$solicitud || $solicitud['estado'] !== 'pendiente' || !in_array($accion, ['aprobada', 'rechazada'])) {
            $_SESSION['error'] = "Solicitud no válida o ya gestionada.";
            header("Location: index.php?action=perfil");
            exit;
        }

        if(Usuario::resolverSolicitudPro($solicitudId, $solicitud['usuario_id'], $_SESSION['usuario_id'], $accion )){
            $_SESSION['exito'] = ($accion === 'aprobada') ? "Solicitud aprobada! Tienes plan PRO por 30 días" :
            "Solicitud rechazada.";
        } else {
            $_SESSION['error'] = "no se pudo procesar la solicitud";
        }

        header("Location: index.php?action=perfil");
        exit;

    }


    //ver comprobante(solo el q solicitp o el admin)
    public static function verComprobante(){
        self::verificarSesion();
        $solicitudId = (int)($_GET['id'] ?? 0);
        $solicitud = Usuario::obtenerSolicitudProPorId($solicitudId);
        $usuarioActual = Usuario::ObtenerPorId($_SESSION['usuario_id']);
        $esAdmin = ((int)($usuarioActual['rol_id'] ?? 0) === 2);

        //verifica que es el usuario de la solicitud o el admin (si es otro usuario entra al if y lo saca de la pagina)
        if(!$solicitud || ($solicitud['usuario_id'] != $_SESSION['usuario_id'] && !$esAdmin)) {
            $_SESSION['error'] = "No tenés permiso para ver este comprobante.";
            header("Location: index.php?action=perfil");
            exit;
        }

        $rutaFisica = __DIR__ . '/../../' . $solicitud['ruta_comprobante'];
        if(file_exists($rutaFisica)){
            //ordenan al navegador a no abrir nada, solo descargar
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($solicitud['ruta_comprobante']) . '"');
            header('Content-Length: ' . filesize($rutaFisica));
            readfile($rutaFisica);
            exit;
        }
        
        $_SESSION['error'] = "El comprobante no está en el server.";
        header("Location: index.php?action=perfil");
        exit;
    }
    

}

?>