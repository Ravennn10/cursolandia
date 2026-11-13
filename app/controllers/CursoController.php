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

    //cargar home
    public static function home() {
        $cursosDes = Curso::obtenerDestacados(3);
        require_once __DIR__ . '/../views/home.php';
    }

    //muestra catalogo y cursos recomendados
    public static function catalogo() {
        
        $busqueda = trim($_GET['q'] ?? ''); //q = abreviacion de query (enlace limpio)

        $cursos = Curso::obtenerCatalogo($busqueda);
        //si no busca nada, le recomienda - si busca algo, no le recomienda
        $recomendados = ($busqueda === '' && isset($_SESSION['usuario_id'])) ? Curso::obtenerRecomendados($_SESSION['usuario_id']) : [];

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

    //procesa alta curso y subida de material
    public static function guardar() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=crear_curso");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $plan = Curso::obtenerPlanUsuario($usuarioId);
        $cursosActivos = Curso::contarCursosActivos($usuarioId);

        //valida el limite de cursos segun el plan(3 = gratuito - NULL = pro)
        if($plan['max_cursos_activos'] !== NULL && $cursosActivos >= $plan['max_cursos_activos']) {
            $_SESSION['error'] = "Alcanzaste el máximo de {$plan['max_cursos_activos']} cursos activos de tu plan gratuito. Solicita PRO para crear más cursos!";
            header("Location: index.php?action=crear_curso");
            exit;
        }

        //si no es gratis, recuperamos los datos ingresados en el form
        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $etiquetas   = trim($_POST['etiquetas'] ?? '');
        $costo       = ($_POST['costo'] !== '' && (float)$_POST['costo'] > 0) ? (float)$_POST['costo'] : 0.00;
        $cupo        = ($_POST['cupo'] !== '' && (int)$_POST['cupo'] > 0) ? (int)$_POST['cupo'] : null;
        $esPublico   = isset($_POST['es_publico']) ? (int)$_POST['es_publico'] : 1;
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaFin    = $_POST['fecha_fin'] ?? '';

        //verifica q no haya campos vacios
        if (empty($titulo) || empty($descripcion) || empty($fechaInicio) || empty($fechaFin)) {
            $_SESSION['error'] = "Completa los campos obligatorios (título, descripción y fechas).";
            header("Location: index.php?action=crear_curso");
            exit;
        }

        //verifica que la fecha de fin no sea menor a la de inicio
        if ($fechaFin < $fechaInicio) {
            $_SESSION['error'] = "La fecha de fin no puede ser anterior a la fecha de inicio.";
            header("Location: index.php?action=crear_curso");
            exit;
        }

        //validar archivos
        $archivosValidos = []; //donde van los archivos temporalmente
        if(isset($_FILES['materiales']) && !empty($_FILES['materiales']['name'][0])) { //Revisa su se subio algo
            $totalNuevosArchivos = count($_FILES['materiales']['name']);

            //valida el limite de cantidad y revisa si lo subido supera ese limite
            if($plan['max_archivos_por_curso'] !== NULL && $totalNuevosArchivos > $plan['max_archivos_por_curso']) {
                $_SESSION['error'] = "Tu plan permite máximo {$plan['max_archivos_por_curso']} archivos por curso.";
                header("Location: index.php?action=crear_curso");
                exit;
            }

            $bytesUsados = Curso::obtenerBytesTotalesUsuario($usuarioId); //pregunta al modulo cuantos bytes tiene ocupados
            $bytesNuevos = 0;

            for($i = 0; $i < $totalNuevosArchivos; $i++) {
                if($_FILES['materiales']['error'][$i] === UPLOAD_ERR_OK) { //verifica que el archivo llego sano sin errores
                    $peso = (int)$_FILES['materiales']['size'][$i]; //obtiene el peso
                    $nombreOriginal = basename($_FILES['materiales']['name'][$i]);  //basename() limpia la ruta del archivo
                    $tempNombre = $_FILES['materiales']['tmp_name'][$i]; //por el momento lo guarda en la carpeta temporal

                    //verifica si no supera el peso maximo(entra solo si el plan es gratuito)
                    if($plan['max_bytes_por_archivo'] !== NULL && $peso > $plan['max_bytes_por_archivo']) {
                        $_SESSION['error'] = "El archivo '{$nombreOriginal}' supera el límite individual de 5 MB de tu plan.";
                        header("Location: index.php?action=crear_curso");
                        exit;
                    }
                    $bytesNuevos += $peso;
                    //empaqueta los datos
                    $archivosValidos[] = ['nombre' => $nombreOriginal, 'tmp' => $tempNombre, 'peso' => $peso]; 
                }
            }

            //verifica que todos los archivos subidos del curso no superen el limite
            if($plan['max_bytes_total_cursos'] !== NULL && ($bytesUsados + $bytesNuevos) > $plan['max_bytes_total_cursos']){
                $_SESSION['error'] = "Superas el límite total de 10 MB de almacenamiento de tu plan gratuito.";
                header("Location: index.php?action=crear_curso");
                exit;
            } 
        }

        //crea el curso en la bdd
        $cursoId = Curso::crearCurso($usuarioId, $titulo, $descripcion, $costo, $cupo, $esPublico, $fechaInicio, $fechaFin);

        if($cursoId) {
            Curso::asignarEtiquetas($cursoId, $etiquetas);

            //mover acrvhivos fisicos a storage/material/ (fuera de public par q no sea accesible para cualquiera)
            $dirDestino = __DIR__ . '/../../storage/material/';
            //creqa el directorio si no existe
            if(!is_dir($dirDestino)){
                mkdir($dirDestino, 0777, true);
            }

            foreach($archivosValidos as $archivo) {
                $nombreUnico = uniqid('material_') . '_'. preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['nombre']);
                $rutaFisica = $dirDestino . $nombreUnico;
                $rutaRelativa = 'storage/material/' . $nombreUnico;

                if(move_uploaded_file($archivo['tmp'], $rutaFisica)) {
                    Curso::guardarArchivo($cursoId, $archivo['nombre'], $rutaRelativa, $archivo['peso']);
                }
            }

            $_SESSION['exito'] = "¡Curso creado exitosamente!";
            header("Location: index.php?action=detalle_curso&id=".$cursoId);
            exit;
        } else {
            $_SESSION['error'] = "Ocurrió un error al gaurdar el curso.";
            header("Location: index.php?action=crear_curso");
            exit;
        }
    }

    //ver detalle de curso especifico
    public static function detalle() {
        self::verificarSesion();
        $cursoId = (int)($_GET['id'] ?? 0);
        $curso = Curso::obtenerPorId($cursoId);

        if(!$curso) {
            $_SESSION['error'] = "El curso solicitado no existe.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        $archivos = Curso::obtenerArchivos($cursoId);
        $esCreador = ($curso['creador_id'] == $_SESSION['usuario_id']);

        require_once __DIR__ . '/../views/cursos/detalle.php';
    }

    //descarga segura de un archivo(desde storage/material/)
    public static function descargarMaterial() {
        self::verificarSesion();

        $archivoId = (int)($_GET['id'] ?? 0);

        if(!$archivoId) {
            $_SESSION['error'] = "Archivo no válido.";
            header("Location: index.php?action=mis_cursos");
            exit;
        }

        //busca el archivo en la bdd
        $archivo = Curso::obtenerArchivoPorId($archivoId);

        if($archivo) {
            //arma la ruta hacia /storage/
            $rutaFisica = __DIR__ . '/../../' . $archivo['ruta_archivo'];

            //verifica que el archivo existe
            if(file_exists($rutaFisica)) {
                //ordenan al navegador a no abrir nada, solo descargar
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($archivo['nombre_archivo']) . '"');
                header('Content-Length: ' . filesize($rutaFisica));

                //lee el archivo en /storage/ y lo lleva al usuario
                readfile($rutaFisica);
                exit; //detner para no envia HTML basura al archivo descargado
            } else {
                $_SESSION['error'] = "Archivo ya no existe en el servidor.";
            }
        } else {
            $_SESSION['error'] = "Archivo no encontrado.";
        }

        //si llega acá, algo fallo, vuelve al catalogo
        header("Location: index.php?action=catalogo");
        exit;

    }

    //muestra form editar curso
    public static function mostrarEditarCurso() {
        self::verificarSesion();
        $cursoId = (int)($_GET['id'] ?? 0);
        $curso = Curso::obtenerPorId($cursoId);

        if(!$curso || $curso['creador_id'] != $_SESSION['usuario_id']){
            $_SESSION['error'] = "No tienes permiso para editar este curso.";
            header("Location: index.php?action=catalogo");
            exit;
        }
        require_once __DIR__ . '/../views/cursos/editar.php';
        
    }

    //procesa form de edicion de curso(solo texto)
    public static function editarCurso() {
        self::verificarSesion();
        //si no se mandó nada, lo devuelve al catalogo
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=catalogo");
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $cursoId = (int)($_POST['curso_id'] ?? 0);

        //limpia los dtos
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $costo = ($_POST['costo'] !== '' && (float)$_POST['costo'] > 0) ? (float)$_POST['costo'] : 0.00;

        //verifica que un usuario no edite el curso de otro
        $cursoOriginal = Curso::obtenerPorId($cursoId);
        if(!$cursoOriginal || $cursoOriginal['creador_id'] != $usuarioId){
            $_SESSION['error'] = "No tienes permiso para editar este curso.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        //verifica campos
        if (empty($titulo) || empty($descripcion)) {
            $_SESSION['error'] = "El título y la descripción son obligatorios.";
            header("Location: index.php?action=editar_curso&id=" . $cursoId);
            exit;
        }

        //actualiza los datos en la bdd
        $exito = Curso::actualizarCurso($cursoId, $titulo, $descripcion, $costo);

        //dependiendo el resultado lo manda al curso actualizado o lo lleva de nuevo al form de editar el curso
        if ($exito) {
            $_SESSION['exito'] = "Información general del curso actualizada.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
        } else {
            $_SESSION['error'] = "Hubo un error al actualizar los datos en la base de datos.";
            header("Location: index.php?action=editar_curso&id=" . $cursoId);
        }
        exit;
    }


    //subir material extra a curso ya creado(solo creador)
    public static function subirMaterial() {
        self::verificarSesion();
        //si no se mando nada, vuelve al catalogo
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header("Location: index.php?action=catalogo");
            exit;
        }

        //recupera datos
        $usuarioId = $_SESSION['usuario_id'];
        $cursoId = (int)($_POST['curso_id'] ?? 0);
        $curso = Curso::obtenerPorId($cursoId);

        //verifica que el curso exista y el usuario que quiere editar sea el creador
        if (!$curso || $curso['creador_id'] != $usuarioId) {
            $_SESSION['error'] = "No tienes permisos para subir material a este curso.";
            header("Location: index.php?action=catalogo");
            exit;
        }

        if (!isset($_FILES['material']) || $_FILES['material']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Selecciona un archivo válido.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        $plan = Curso::obtenerPlanUsuario($usuarioId);
        $cantidadActual = Curso::contarArchivosCurso($cursoId);
        $bytesUsados = Curso::obtenerBytesTotalesUsuario($usuarioId);

        $peso = (int)$_FILES['material']['size'];
        $nombreOriginal = basename($_FILES['material']['name']);

        //verifica si supera la cantidad maxima(para gratutito)
        if ($plan['max_archivos_por_curso'] !== NULL && ($cantidadActual + 1) > $plan['max_archivos_por_curso']) {
            $_SESSION['error'] = "Este curso ya alcanzó el máximo de {$plan['max_archivos_por_curso']} archivos permitidos en tu plan.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //verifica que no supere tamaño por archivo
        if ($plan['max_bytes_por_archivo'] !== NULL && $peso > $plan['max_bytes_por_archivo']) {
            $_SESSION['error'] = "El archivo supera los 5 MB permitidos por archivo en tu plan gratuito.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //verifica que no supere tamaño total por curso
        if ($plan['max_bytes_total_cursos'] !== null && ($bytesUsados + $peso) > $plan['max_bytes_total_cursos']) {
            $_SESSION['error'] = "Superas el límite global de 10 MB de almacenamiento de tu plan gratuito.";
            header("Location: index.php?action=detalle_curso&id=" . $cursoId);
            exit;
        }

        //define directorio destino, si no existe lo crea
        $directorioDestino = __DIR__ . '/../../storage/material/';
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        //cambio de nombre para que todo tengan un nombre difertente
        $nombreUnico = uniqid('material_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);

        //gaurda el archivo en la bdd
        if (move_uploaded_file($_FILES['material']['tmp_name'], $directorioDestino . $nombreUnico)) {
            Curso::guardarArchivo($cursoId, $nombreOriginal, 'storage/material/' . $nombreUnico, $peso);
            $_SESSION['exito'] = "Material subido con éxito.";
        } else {
            $_SESSION['error'] = "Error al gaurdar el archivo en el servidor.";
        }

        //si llega aca, algo falló, lo lleva otra vez al detalle del curso
        header("Location: index.php?action=detalle_curso&id=" . $cursoId);
        exit;
    }


    //procesa eliminar curso
    public static function eliminar() {
        self::verificarSesion();
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $cursoId = (int)($_POST['curso_id'] ?? 0);
            $curso = Curso::obtenerPorId($cursoId);

            if($curso && $curso['craedor_id'] == $_SESSION['usuario_id']){
                Curso::eliminarCurso($cursoId);
                $_SESSION['exito'] = "Curso eliminado!";
            } else {
                $_SESSION['error'] = "No tiene permisos para eliminar este curso.";
            }
        }
        header("Location: index.php?action=catalogo");
        exit;
    }

}


?>