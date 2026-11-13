<?php 
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../models/Curso.php';

class CursoController {
    //controlar sesion para cosas que solo puede hacer un usuario logeado(no un invitado)
    private static function verificarSesion() {
        if(!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    //muestra catalogo y cursos recomendados
    public static function catalogo() {
        self::verificarSesion();
        $busqueda = trim($_GET['q'] ?? ''); //q = abreviacion de query (enlace limpio)

        $cursos = Curso::obtenerCatalogo($busqueda);
        //si no busca nada, le recomienda - si busca algo, no le recomienda
        $recomendados = ($busqueda === '') ? Curso::obtenerRecomendados($_SESSION['usuario_id']) : [];

        require_once __DIR__ . '/../views/cursos/catalogo.php';
    }

    //muestra formulario de creacion de curso
    public static function mostrarCrearCurso() {
        self::verificarSesion();
        $plan = Curso::obtenerPlanUsuario($_SESSION['usuario_id']); 
        $cursosActivos = Curso::contarCursosActivos($_SESSION['usuario_id']);
        //para usar en la view(controlar que si es gratis tiene limite de crear maximo 3 cursos activos y creados)
        require_once __DIR__ . '/../views/cursos/crear.php';
    }

      


}


?>