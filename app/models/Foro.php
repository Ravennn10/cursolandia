<?php
require_once __DIR__ . '/../../config/conexion.php';

class Foro {

    // - - - - - FORO - - - - -

    //obtener preguntas y respuestas de un curso
    public static function obtenerPreguntasYRespuestas($cursoId) {
        $db= conectar();
        $sql= "SELECT p.*, u.nombre, u.apellido FROM preguntas p JOIN usuarios u ON p.autor_id = u.id
                WHERE p.curso_id = ? ORDER BY p.destacada DESC, p.fecha_creada DESC";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);
        $preguntas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        //carga respuesta a cada pregunta
        foreach($preguntas as &$preg) {
            $sqlResp = "SELECT r.*, u.nombre, u.apellido FROM respuestas r JOIN usuarios u ON r.autor_id = u.id
                        WHERE r.pregunta_id = ? ORDER BY r.fecha_creada ASC";

            $stmtResp = mysqli_prepare($db, $sqlResp);
            mysqli_stmt_bind_param($stmtResp, "i", $preg['id']);
            mysqli_stmt_execute($stmtResp);
            $preg['respuestas'] = mysqli_fetch_all(mysqli_stmt_get_result($stmtResp), MYSQLI_ASSOC);
            mysqli_stmt_close($stmtResp);
             
        }
        unset($preg); //se pone & para que tome todos los valores del foreach por referencia, se usa unset para poder usarla mas adelante sin problemas.

        return $preguntas;
    }

    //craer pregunta
    public static function crearPregunta($cursoId, $autorId, $titulos, $contenido, $destacada) {
        $db = conectar();
        $sql = "INSERT INTO preguntas (curso_id, autor_id, titulo, contenido, destacada) VALUES (?, ?, ?, ? ,?)";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iissi", $cursoId, $autorId, $titulos, $contenido, $destacada);
        $ok = mysqli_stmt_execute($stmt);
        $nuevoId = mysqli_insert_id($db); //devuelve el último id que se generó en la bdd(id de la nueva pregunta)
        mysqli_stmt_close($stmt);

        return $ok ? $nuevoId : false; //si la consulta fue exitosa, devuelve el id de la pregunta, si no false
    }


    //responder una pregunta
    public static function crearRespuesta($preguntaId, $autorId, $contenido) {
        $db = conectar();
        $sql = "INSERT INTO respuestas (pregunta_id, autor_id, contenido) VALUES (?, ?, ? )";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iis", $preguntaId, $autorId, $contenido);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;

    }

    //marcar respuesta aceptada/destacada
    public static function aceptarRespuesta($preguntaId, $respuestaId) {
        $db = conectar();
        $sql = "UPDATE preguntas SET respuesta_aceptada_id = ? WHERE id = ?";

        $stmt= mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $respuestaId, $preguntaId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }


    //devulve pregunta por id
    public static function obtenerPreguntaPorId($preguntaId) {
        $db = conectar();
        $stmt = mysqli_prepare($db, "SELECT * FROM preguntas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $preguntaId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //devuelve un respuesta especifica (para aceptar respuesta)
    public static function obtenerRespuestaPorId($respuestaId) {
        $db = conectar();
        $stmt = mysqli_prepare($db, "SELECT * FROM respuestas WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $respuestaId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //devuelve los participantes del curso(creador y alumnpos confirmados)
    public static function obtenerParticipantesCurso($cursoId) {
        $db = conectar();
        //UNION : fusiona datos de tablas distintas en una sola lista/fila 
        // busca al creador en cursos y a los usuarios en usuarios
        $sql = "SELECT creador_id AS usuario_id FROM cursos WHERE id = ?
                UNION
                SELECT usuario_id AS usuario_id FROM inscripciones WHERE curso_id = ? AND estado = 'confirmada'";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $cursoId, $cursoId);
        mysqli_stmt_execute($stmt);
        $filas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return array_column($filas, 'usuario_id'); //transforma un array asociativo en uno simple con solo los valores(usuario ids)
    }

    //participantes de una pregunta(el uqe la hixo y los que respondienron)
    public static function obtenerParticipantesPregunta($preguntaId) {
        $db = conectar();
        $sql = "SELECT autor_id AS usuario_id FROM preguntas WHERE id = ?
                UNION
                SELECT autor_id AS usuario_id FROM respuestas WHERE pregunta_id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $preguntaId, $preguntaId);
        mysqli_stmt_execute($stmt);
        $filas = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return array_column($filas, 'usuario_id');
    }

    //eliminar una pregunta completa(con sus respuestas)
    public static function eliminarPregunta($preguntaId) {
        $db = conectar();

        //desvincula notificaciones(por fk)
        $stmtNotif = mysqli_prepare($db, "UPDATE notificaciones SET pregunta_id = NULL WHERE pregunta_id = ?");
        mysqli_stmt_bind_param($stmtNotif, "i", $preguntaId);
        mysqli_stmt_execute($stmtNotif);
        mysqli_stmt_close($stmtNotif);

        //quita referencia de respuesta aceptada para evitar bloqueo por fk
        $stmt = mysqli_prepare($db, "UPDATE preguntas SET respuesta_aceptada_id = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $preguntaId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        //borra respuestas asociadas
        $stmtResp = mysqli_prepare($db, "DELETE FROM respuestas WHERE pregunta_id = ?");
        mysqli_stmt_bind_param($stmtResp, "i", $preguntaId);
        mysqli_stmt_execute($stmtResp);
        mysqli_stmt_close($stmtResp);

        // Borrar la pregunta
        $stmtPreg = mysqli_prepare($db, "DELETE FROM preguntas WHERE id = ?");
        mysqli_stmt_bind_param($stmtPreg, "i", $preguntaId);
        $ok = mysqli_stmt_execute($stmtPreg);
        mysqli_stmt_close($stmtPreg);

        return $ok;
    }


    // - - - - - NOTIFICACIONES - - - - -

    //nueva notificacion (general de cualquier tipo)
    public static function crearNotificacion ($usuarioId, $actorId, $cursoId, $preguntaId, $tipo, $mensaje) {
        $db = conectar();
        $sql = "INSERT INTO notificaciones (usuario_id, actor_id, curso_id, pregunta_id, tipo, mensaje)
                VALUES (?, ?, ?, ? , ?, ?)";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iiiiss", $usuarioId, $actorId, $cursoId, $preguntaId, $tipo, $mensaje);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    
        return $ok;
    }

    //obtener las notificaciones de un usuario
    public static function obtenerNotificacionesUsuario($usuarioId) {
        $db = conectar();
        $sql = "SELECT n.*, c.titulo AS curso_titulo FROM notificaciones n JOIN cursos c ON n.curso_id = c.id
                WHERE n.usuario_id = ? ORDER BY n.leida ASC, n.fecha DESC LIMIT 10";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $lista = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $lista;

    }

    //devulve cantidad de notif. no leidas(para el header)
    public static function contarNoLeidas($usuarioId) {
        $db = conectar();
        $sql = "SELECT COUNT(*) AS total FROM notificaciones WHERE usuario_id = ? AND leida = 0";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)($fila['total'] ?? 0);
    }

    //marca todas leidas
    public static function marcarTodasLeidas($usuarioId) {
        $db=conectar();
        $stmt=mysqli_prepare($db, "UPDATE notificaciones SET leida = 1 WHERE usuario_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }





}


?>