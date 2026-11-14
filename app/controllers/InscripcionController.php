<?php 
//CONTROLADOR: verifica la inscripcion, permite al creador cambiar el estado de la misma, permite al aluno darse de baja

if(session_status() === PHP_SESSION_NONE ) {
    session_start();
}

require_once __DIR__ . '/../models/Inscripcion.php';
require_once __DIR__ . '/../models/Curso.php';

class InscripcionController {
    //verifica la sesion
    private static function verificarSesion() {
        if(!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    //precesar inscripcion
    public static function inscribir() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $cursoId = (int)($_POST['curso_id'] ?? 0);
        $curso = Curso::obtenerPorId($cursoId);

        if(!$curso) {
            $_SESSION['error'] = "EL curso no existe.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        //verifica q el creador no puede inscribirse a su propio curso
        if($curso['creador_id'] == $usuarioId) {
            $_SESSION['error'] = "NO puedes inscribirte a tu propio curso.";
            header("Location: index.php?action=detalle_curso&id=". $cursoId);
            exit;
        }

        //verifica que el curso no haya finalizado
        if($curso['fecha_fin'] < date('Y-m-d')) {
            $_SESSION['error'] = "El curso ya ha terminado.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //verifica si ya esta inscripto a ese curso
        $existente = Inscripcion::obtenerInscripcion($usuarioId,$cursoId);
        if($existente && in_array($existente['estado'],['confirmada', 'pendiente'])) {
            $_SESSION['error'] = "Ya te inscribiste a este curso.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //verifica cupo
        if($curso['cupo'] !== NULL) {
            $ocupados = Inscripcion::contarInscriptosCurso($cursoId);
            if($ocupados >= (int)$curso['cupo']) {
                $_SESSION['error'] = "Este curso ya alcanzó su límite de cupos!";
                header("Location: index.php?action=detalle_curso&id=" . $cursoId);
                exit;
            }
        }

        //verifica limite de inscripciones segun el plan del usuario
        $plan = Curso::obtenerPlanUsuario($usuarioId);
        $limitePlan = $plan['max_inscripciones_activas'] ?? ($plan['plan_id'] == 1 ? 3 : NULL); 
        $activasUsuario = Inscripcion::contarInscipcionesActivasUsuario($usuarioId);

        if($limitePlan !== NULL && $activasUsuario >= (int)$limitePlan) {
            $_SESSION['error'] = "Has alcanzado el límite de {$limitePlan} inscripciones. Solicita PRO para tener inscripciones ilimitadas";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //curso gratis = directamente 'confirmada' - pago = 'pendiente' hasta que el creador la confirme
        $estado = ((float)$curso['costo'] > 0) ? 'pendiente' : 'confirmada';

        if($existente) {
            //si ya existia y fue rechazada, se reutiliza la inscripcion y se actualiza el estado
            $ok = Inscripcion::actualizarEstado($existente['id'], $estado);
        } else {
            $ok = Inscripcion::inscribir($usuarioId, $cursoId, $estado);
        }

        if($ok) {
            if($estado === 'confirmada') {
                $_SESSION['exito'] = "Inscripción confirmada! ya tienes acceso a los amteriales del curso";
            } else {
                $_SESSION['error'] = "SOlicitud enviada(pendiente). Al ser pago debe esperar confirmación.";
            }
        } else {
            $_SESSION['error'] = "Error al procesar la inscripción.";
        }

        header("Location: index.php?action=detalle_curso&id=" . $cursoId);
        exit;

    }
    

    //confirmar o rechazar inscripcion (creador)
    public static function cambiarEstado() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=catalogo");
            exit;
        }

        $inscripcionId = (int)($_POST['inscripcion_id'] ?? 0);
        $nuevoEstado = $_POST['estado'] ?? '';

        //verifica q sea un estado permitido
        if(!in_array($nuevoEstado, ['confirmada', 'rechazada'])) {
            $_SESSION['error'] = "Estado no válido.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        $inscripcion = Inscripcion::obtenerPorId($inscripcionId);

        //verifica q sea el creador del curso
        if(!$inscripcion || $inscripcion['creador_id'] != $_SESSION['usuario_id']) {
            $_SESSION['error'] = "no tienes permiso para gestionar esta inscripción.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        //solo se pueden gestionar inscripciones pendientes
        if($inscripcion['estado'] !== 'pendiente') {
            $_SESSION['error'] = "Esta inscripción ya fue procesada anteriormente.";
            header("Location: index.php?action=detalle_curso&id=" . $inscripcion['curso_id']);
            exit;
        }

        if(Inscripcion::actualizarEstado($inscripcionId, $nuevoEstado)) {
            $_SESSION['exito'] = ($nuevoEstado === 'confirmada') ? "Inscripción confirmada." : "Inscripción rechazada.";
        } else {
            $_SESSION['error'] = "No se pudo actualizar el estado de la inscripción.";
        }

        header("Location: index.php?action=detalle_curso&id=" . $inscripcion['curso_id']);
        exit;

    }

    


}



?>