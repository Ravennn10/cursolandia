<?php   //Foro y notificaciones: se crean las preguntas y respuestas y notifica al hacerlo
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Inscripcion.php';
require_once __DIR__ . '/../models/Usuario.php';


class ForoController {

    private static function verificarSession(){
        if(!isset($_SESSION['usuario_id'])){
            header("Location: index.php?action=login");
            exit;
        }
    }


    //verifica si es creador o alumno confirmado
    public static function puedeParticipar($usuarioId, $curso) {
        if($curso['creador_id'] == $usuarioId){
            return true;
        }
        $inscripcion = Inscripcion::obtenerInscripcion($usuarioId, $curso['id']);
        return ($inscripcion && $inscripcion['estado'] === 'confirmada');

    }

    //publicar pregunra (notifica a todos los participantes menos al creador)
    public static function crearPregunta() {
        self::verificarSession();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id']; //pasaria a ser el autor de la pregunta
        $cursoId = (int)($_POST['curso_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $curso = Curso::obtenerPorId($cursoId);

        //si el curso no existe o el usuario no puede participar...
        if(!$curso || self::puedeParticipar($usuarioId, $curso)){
            $_SESSION['error'] = "No puede participar en el foro!";
            header("Location: index.php?action=detalle_curso&id=". $cursoId);
            exit;
        }

        //si hay campos vacios
        if(empty($titulo) || empty($contenido)) {
            $_SESSION['error'] = "El titulo y el contenido de la pregunta son obligatorios";
            header("Location: index.php?action=detalle_curso&id=". $cursoId);
            exit;
        }

        //si tiene plan pro, se destaca la pregunta
        $plan = Curso::obtenerPlanUsuario($usuarioId);
        //si plan_id = 2, la pregunta es destacada (1)
        $destacada = ((int)($plan['plan_id'] ?? 1) === 2 ) ? 1 : 0;

        $preguntaId = Foro::crearPregunta($cursoId, $usuarioId, $titulo, $contenido, $destacada);

        if($preguntaId) {
            //evita notificar al autor de la pregunta
            $nombreAutor = $_SESSION['usuario_nombre'] ?? 'anónimo';
            $participantes = Foro::obtenerParticipantesCurso($cursoId);

            foreach($participantes as $receptorId){
                //si el que recibe la notificaion es != del usuario que la crea...
                if($receptorId != $usuarioId) {
                    $msg = "{$nombreAutor} publicó una pregunta en {$curso['titulo']}";
                    Foro::crearNotificacion($receptorId, $usuarioId, $cursoId, $preguntaId, 'pregunta_nueva', $msg);
                }
            }
            $_SESSION['exito'] = "Pregunta publicada.";
        } else {
            $_SESSION['error']= "no se pudo publicar la pregunta";
        }

        //si llega aca hay un error y lo devuelve al curso
        header("Location: index.php?action=detalle_curso&id=" . $cursoId);
        exit;

    }


    //responder pregunta(notifica al creador de la pregunta y los que han respondido)
    public static function crearRespuesta() {
        self::verificarSession();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $preguntaId = (int)($_POST['pregunta_id'] ?? 0);
        $contenido = trim($_POST['contenido'] ?? '');

        //si la pregunta no existe...
        $pregunta = Foro::obtenerPreguntaPorId($preguntaId);
        if(!$pregunta){
            header("Location: index.php?action=catalogo");
            exit;
        }

        //si no puede participar en el foro del curso...
        $curso = Curso::obtenerPorId($pregunta['curso_id']);
        if(!$curso || !self::puedeParticipar($usuarioId, $curso)){
            $_SESSION['error'] = "No puede participar en este foro";
            header("Location: index.php?action=detalle_curso&id=". $pregunta['curso_id']);
            exit;
        }

        //si la pregunta ya se cerró...
        if (!empty($pregunta['respuesta_aceptada_id'])) {
            $_SESSION['error'] = "Esta pregunta ya está cerrada.";
            header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
            exit;
        }

        //si no tiene contenido(respues vacia)...
        if (empty($contenido)) {
            $_SESSION['error'] = "La respuesta no puede estar vacía.";
            header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
            exit;
        }

        if(Foro::crearRespuesta($preguntaId, $usuarioId, $contenido)){
            $nombreAutor = $_SESSION['usuarion_nombre'] ?? 'anonimo';
            $participantes = Foro::obtenerParticipantesPregunta($preguntaId);

            //notifica solo a lso participantes de la pregunta(menos al autor)
            foreach($participantes as $receptorId){
                if($receptorId != $usuarioId){
                    $msg = "{$nombreAutor} respondió la pregunta: \"{$pregunta['titulo']}\" ";
                    Foro::crearNotificacion($receptorId, $usuarioId, $pregunta['curso_id'], $preguntaId, 'respuesta_nueva', $msg);
                }
            }
            $_SESSION['exito'] = "respuesta publicada";
        } else {
            $_SESSION['error'] = "Error al publicar respuesta";
        }

        header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
        exit;

    }

    //aceptar respuesta(cerrar pregunta) - (notifica a participante y autor de respuesta)
    public static function aceptarRespuesta() {
        self::verificarSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId   = $_SESSION['usuario_id'];
        $preguntaId  = (int)($_POST['pregunta_id'] ?? 0);
        $respuestaId = (int)($_POST['respuesta_id'] ?? 0);

        $pregunta  = Foro::obtenerPreguntaPorId($preguntaId);
        $respuesta = Foro::obtenerRespuestaPorId($respuestaId);

        //si no encuentra la pregunta, ni la respuesta o no coinciden sus id...
        if (!$pregunta || !$respuesta || $respuesta['pregunta_id'] != $preguntaId) {
            header("Location: index.php?action=catalogo");
            exit;
        }

        //si no es autor de la pregunta ni el creador del curso, no la puede cerrar
        $curso = Curso::obtenerPorId($pregunta['curso_id']);
        if ($pregunta['autor_id'] != $usuarioId && $curso['creador_id'] != $usuarioId) {
            $_SESSION['error'] = "No tenés permiso para cerrar esta pregunta.";
            header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
            exit;
        }

        //si se cerró la pregunta exitosamente... notifica a los aprticipantes y al autor de la respuesta aceptada
        if (Foro::aceptarRespuesta($preguntaId, $respuestaId)) {
            $participantes = Foro::obtenerParticipantesPregunta($preguntaId);
            $autorDestacadoId = $respuesta['autor_id'];

            foreach ($participantes as $receptorId) {
                //si el receptor es el mismo que hizo la accion, no le notifica(sale del bucle en esa vuelta)
                if ($receptorId == $usuarioId) {
                    continue;
                }

                if ($receptorId == $autorDestacadoId) {
                    $msg = "¡Tu respuesta fue elegida como solución y cerró la pregunta: \"{$pregunta['titulo']}\" ";
                } else {
                    $msg = "Se cerró la pregunta \"{$pregunta['titulo']}\".";
                }

                Foro::crearNotificacion($receptorId, $usuarioId, $pregunta['curso_id'], $preguntaId, 'pregunta_cerrada', $msg);
            }

            $_SESSION['exito'] = "Pregunta cerrada con respuesta aceptada.";
        }

        header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
        exit;
    }

    //eliminar pregunta (notifica al creador)
    public static function eliminarPregunta() {
        self::verificarSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId  = $_SESSION['usuario_id'];
        $preguntaId = (int)($_POST['pregunta_id'] ?? 0);
        $pregunta   = Foro::obtenerPreguntaPorId($preguntaId);

        if (!$pregunta) {
            header("Location: index.php?action=catalogo");
            exit;
        }

        $curso   = Curso::obtenerPorId($pregunta['curso_id']);
        $usuario = Usuario::ObtenerPorId($usuarioId);
        $esAdmin = ((int)($usuario['rol_id'] ?? 0) === 2);

        //solo puede eliminar el autor de la pregunta, el creador del curso o el admin
        if ($pregunta['autor_id'] != $usuarioId && $curso['creador_id'] != $usuarioId && !$esAdmin) {
            $_SESSION['error'] = "No tenés permiso para eliminar esta pregunta.";
            header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
            exit;
        }

        //
        if (Foro::eliminarPregunta($preguntaId)) {
            //si la eliminó el creador del curso o admin, notifica solo al creador 
            if ($pregunta['autor_id'] != $usuarioId) {
                $msg = "Tu pregunta \"{$pregunta['titulo']}\" fue eliminada del curso {$curso['titulo']}.";
                Foro::crearNotificacion($pregunta['autor_id'], $usuarioId, $pregunta['curso_id'], null, 'pregunta_eliminada', $msg);
            }
            $_SESSION['exito'] = "Pregunta eliminada";
        } else {
            $_SESSION['error'] = "No se pudo eliminar la pregunta.";
        }

        header("Location: index.php?action=detalle_curso&id=" . $pregunta['curso_id']);
        exit;
    }

    //marcar notif leidas
    public static function leerNotificaciones() {
        self::verificarSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Foro::marcarTodasLeidas($_SESSION['usuario_id']);
            $_SESSION['exito'] = "Notificaciones marcadas como leídas.";
        }
        header("Location: index.php?action=perfil");
        exit;
    }



}


?>