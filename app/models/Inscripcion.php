<?php 
// MODELO: acceso a la tabla usuarios.
// Solo consultas a la base: no imprime HTML ni toca la sesión.

require_once __DIR__ . '/../../config/conexion.php';

class Inscripcion {

    //busca si el usuario ya se inscribio a ese curso
    public static function obtenerInscripcion($usuarioId, $cursoId) {
        $db = conectar();
        $sql = "SELECT * FROM inscripciones WHERE usuario_id = ? AND curso_id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $usuarioId, $cursoId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //busca una inscripcion por id y el creador del curso
    public static function obtenerPorId($inscripcionId) {
        $db = conectar();
        $sql = "SELECT i.*, c.creador_id, c.titulo AS curso_titulo FROM inscripciones i JOIN cursos c ON i.curso_id = c.id
                WHERE i.id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $inscripcionId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //cuenta cuantas inscripciones activas tiene el usuario(para verificar su limite)
    public static function contarInscipcionesActivasUsuario($usuarioId) {
        $db = conectar();
        $sql = "SELECT COUNT(*) AS total FROM inscripciones i JOIN cursos c ON i.curso_id = c.id 
                WHERE i.usuario_id = ? AND i.estado IN ('confirmada', 'pendiente') AND c.fecha_fin >= CURDATE()";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)$fila['total'];
    }

    //cuenta inscriptos en el curso(verifica cupo)
    public static function contarInscriptosCurso($cursoId) {
        $db = conectar();
        $sql = "SELECT COUNT(*) AS total FROM inscripciones WHERE curso_id = ? AND estado IN ('confirmada', 'pendiente')";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)$fila['total'];
    }

    //registrar nueva inscripcion
    public static function inscribir($usuarioId, $cursoId, $estado) {
        $db =conectar();
        $sql = "INSERT INTO inscripciones (usuario_id, curso_id, estado) VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iis", $usuarioId, $cursoId, $estado);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    //actualiza estado de inscripcion(confirmada / rechazada / pendiente)
    public static function actualizarEstado($inscripcionId, $nuevoEstado) {
        $db = conectar();
        $sql = "UPDATE inscripciones SET estado = ? WHERE id = ?";

        $stmt = mysqli_prepare($db,$sql);
        mysqli_stmt_bind_param($stmt, "si",$nuevoEstado , $inscripcionId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;

    }

    //lista los inscriptos de un curso (para conocimiento del creador del curso)
    public static function obtenerAlumnosPorCurso($cursoId) {
        $db = conectar();
        $sql = "SELECT i.id, i.usuario_id, i.curso_id, i.estado, u.nombre, u.apellido, u.email, u.plan_id
                FROM inscripciones i JOIN usuarios u ON i.usuario_id = u.id
                WHERE i.curso_id = ? ORDER BY i.id DESC";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);
        $lista = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $lista;
    }

}

?>