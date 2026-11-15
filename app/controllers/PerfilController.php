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

}

?>