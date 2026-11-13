<?php 
require_once __DIR__ . '/../../config/conexion.php';

class Curso {
    //obtener limites de plan (y verificar fecha de vencimiento)
    public static function obtenerPlanUsuario($usuarioId) {
        $db = conectar();
        $sql = "SELECT u.plan_id, u.plan_vence_en, p.* 
                FROM usuarios u JOIN planes p ON u.plan_id = p.id
                WHERE u.id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);

        $plan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        //si es pro(plan_id=2) y ya venció aplican las reglas de plan gratuito
        if($plan && $plan['plan_id'] == 2 && !empty($plan['plan_vence_en']) && $plan['plan_vence_en'] < date('Y-m-d')) {
            $sqlGratuito = "SELECT * FROM planes WHERE id = 1";
            $result = mysqli_query($db, $sqlGratuito);
            $planGratuito = mysqli_fetch_assoc($result);
            $planGratuito['plan_id'] = 1;
            return $planGratuito;
        }

        return $plan;
    }

    //cuenta cursos activos creados por el usuario (fecha_fin >= hoy)
    public static function contarCursosActivos($usuarioId) {
        $db = conectar();
        $sql = "SELECT COUNT(*) AS total FROM cursos WHERE creador_id = ? AND fecha_fin >= CURDATE()";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);

        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)$fila['total'];
    }

    //suma peso total(bytes) de los archivos subidos por el creador
    public static function obtenerBytesTotalesUsuario($usuarioId) {
        $db = conectar();
        $sql = "SELECT COALESCE(SUM(ca.peso_bytes), 0) AS total_bytes
                FROM curso_archivos ca JOIN cursos c ON ca.curso_id = c.id
                WHERE c.creador_id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);

        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)$fila['total_bytes'];
    }

    //cunta cuantos archivos tiene UN curso
    public static function contarArchivosCurso($cursoId) {
        $db = conectar();
        $sql = "SELECT COUNT(*) AS total FROM curso_archivos WHERE curso_id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);

        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (int)$fila['total'];
    }

    //crea un nuevo curso y devuelve su id
    public static function crearCurso($creador_id, $titulo, $descripcion, $costo, $cupo, $esPublico, $fechaInicio, $fechaFin) {
        $db = conectar();
        $sql = "INSERT INTO cursos (creador_id, titulo, descripcion, costo, cupo, es_publico, fecha_inicio, fecha_fin)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "issdiiss", $creador_id, $titulo, $descripcion, $costo, $cupo, $esPublico, $fechaInicio, $fechaFin);
        
        if( mysqli_stmt_execute($stmt)) {
            $nuevoId = mysqli_insert_id($db); //devuelve el último id que se generó en la bdd(id del nuevo curso)
            mysqli_stmt_close($stmt);
            return $nuevoId;
        }
        mysqli_stmt_close($stmt);
        return false;
    }

    //guardar etiquetas y asociarlas al curso
    public static function asignarEtiquetas($cursoId, $etiquetasTexto) {
        if(empty(trim($etiquetasTexto))) return; //si el texto de etiqueta esta vacio, sale

        $db = conectar();
        $lista = array_unique(array_filter(array_map('trim', explode(',',$etiquetasTexto))));

        foreach($lista as $nombreTag) {
            $nombreTag = mb_substr($nombreTag, 0, 50);  //max 50 caracteres mb=multi-byte(para q no corte mal)

            //inserta si no existe la etiqueta en la tabla etiquetas
            $sqlInsertTag = "INSERT IGNORE INTO etiquetas (nombre) VALUES (?)"; //ignore: evita falta error si la etiqueta ya existe en la tabla, simplemente no hace el insert(silenciosamente).
            $stmtTag = mysqli_prepare($db, $sqlInsertTag);
            mysqli_stmt_bind_param($stmtTag, "s", $nombreTag);
            mysqli_stmt_execute($stmtTag);

            //obtener id de la etiqueta(existente)
            $sqlGetTag = "SELECT id FROM etiquetas WHERE nombre = ?"; //no se hace mysqli_insert_id() pq si ya existia y no se insertó entonces no va a devolver el id verdadero, devuelve otro.
            $stmtGet = mysqli_prepare($db, $sqlGetTag);
            mysqli_stmt_bind_param($stmtGet, "s", $nombreTag);
            mysqli_stmt_execute($stmtGet);
            $tag = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));
            mysqli_stmt_close($stmtGet);

            if($tag) { //una vez obtenido el id de la etiqueta se inserta relacionandola con el id del curso recibido 
                $sqlRel = "INSERT IGNORE INTO curso_etiquetas (curso_id, etiqueta_id) VALUES (?, ?)";
                $stmtRel = mysqli_prepare($db, $sqlRel);
                mysqli_stmt_bind_param($stmtRel, "ii", $cursoId, $tag['id']);
                mysqli_stmt_execute($stmtRel);
                mysqli_stmt_close($stmtRel);
            }
        }
    }

    //registra archivo subido del curso en la bdd
    public static function guardarArchivo($curso_id, $nombreArchivo, $rutaArchivo, $pesoBytes) {
        $db = conectar();
        $sql = "INSERT INTO curso_archivos (curso_id, nombre_archivo, ruta_archivo, peso_bytes)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "issi", $curso_id, $nombreArchivo, $rutaArchivo, $pesoBytes);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    //lsitar cursos publicos activos(primero los pro)
    public static function obtenerCatalogo($busqueda = '') { //valor default si no se pasa nada (vacio)
        $db = conectar();
        $like = '%' . $busqueda . '%'; //%: comodines (necesarios para busquedas parciales (LIKE))

        $sql = "SELECT c.id, c.creador_id, c.titulo, c.descripcion, c.costo, c.cupo, c.es_publico, c.fecha_inicio,
                    c.fecha_fin, c.fecha_creacion, u.nombre AS creador_nombre, u.apellido AS creador_apellido, 
                    u.plan_id AS creador_plan, GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') AS etiquetas
                FROM cursos c 
                JOIN usuarios u ON c.creador_id = u.id
                LEFT JOIN curso_etiquetas ce ON c.id = ce.curso_id
                LEFT JOIN etiquetas e ON ce.etiqueta_id = e.id
                WHERE c.es_publico = 1
                AND c.fecha_fin >= CURDATE()
                AND (? = '' OR c.titulo LIKE ? OR EXISTS (
                    SELECT 1 FROM curso_etiquetas ce2
                    JOIN etiquetas e2 ON ce2.etiqueta_id = e2.id
                    WHERE ce2.curso_id = c.id 
                    AND e2.nombre LIKE ?
                ))
                GROUP BY c.id, c.creador_id, c.titulo, c.descripcion, c.costo, c.cupo, c.es_publico, c.fecha_inicio,
                        c.fecha_fin, c.fecha_creacion, u.nombre, u.apellido, u.plan_id
                ORDER BY u.plan_id DESC, c.fecha_creacion DESC";  
        
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $busqueda, $like, $like);
        mysqli_stmt_execute($stmt);
        $cursos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $cursos;
                 
        //group concat: vuelve a unir las etiquetas en una cadena separadas por coma
        //left join: "mostrar el curso igual, aunque no tenga etiquetas"
        //exists: consulta interna (si no aparece por titulo busca por etiquetas)
        //
    }

    //obtiene cursos recomendados entre intereses del usuario y etiquetas de curso
    public static function obtenerRecomendados($usuarioId) {
        $db = conectar();
        $sql = "SELECT c.id, c.creador_id, c.titulo, c.descripcion, c.costo, c.cupo, c.es_publico, c.fecha_inicio,
                    c.fecha_fin, c.fecha_creacion, u.nombre AS creador_nombre, u.apellido AS creador_apellido, 
                    u.plan_id AS creador_plan, GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') AS etiquetas,
                    COUNT(DISTINCT ui.etiqueta_id) AS coincidencias
                FROM cursos c
                JOIN usuarios u ON c.creador_id = u.id
                JOIN curso_etiquetas ce_coincidencia ON c.id = ce_coincidencia.curso_id
                JOIN usuarios_intereses ui ON ce_coincidencia.etiqueta_id = ui.etiqueta_id
                LEFT JOIN curso_etiquetas ce ON c.id = ce.curso_id
                LEFT JOIN etiquetas e ON ce.etiqueta_id = e.id
                WHERE ui.usuario_id = ?
                AND c.creador_id != ?
                AND c.es_publico = 1
                AND c.fecha_fin >= CURDATE()
                GROUP BY c.id, c.creador_id, c.titulo, c.descripcion, c.costo, c.cupo, c.es_publico, c.fecha_inicio,
                        c.fecha_fin, c.fecha_creacion, u.nombre, u.apellido, u.plan_id
                ORDER BY u.plan_id DESC, coincidencias DESC, c.fecha_creacion DESC
                LIMIT 6";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt,"ii", $usuarioId, $usuarioId);
        mysqli_stmt_execute($stmt);
        $recomendados = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $recomendados;

        //count: cuenta cuantas etiquetas tienen en comun el usuario y el curso
        //join curso_etiquetas x2: join para verificar q al menos comparten 1 etiqueta - left join para mostrar todas las etiquetas q tiene 1 curso(incluso las q no coinciden con el usuario)
        //where: evita q muestre los cursos cerados por uno mismo - q muestre los publicos y activos
        //order by: prioridad PRO - los que tengas mas coincidencias de etiquetas - creado mas reciente
    }


    //obtiene un curso por id con datos de creador y etiquetas
    public static function obtenerPorId($cursoId) {
        $db = conectar();
        $sql = "SELECT c.*, u.nombre AS creador_nombre, u.apellido AS creador_apellido, u.plan_id AS creador_plan,
                u.imagen_perfil AS creador_imagen,
                GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') AS etiquetas
                FROM cursos c
                JOIN usuarios u ON c.creador_id = u.id
                LEFT JOIN curso_etiquetas ce ON c.id = ce.curso_id
                LEFT JOIN etiquetas e ON ce.etiquetas_id = e.id
                WHERE c.id = ?
                GROUP BY c.id, u.nombre, u.apellido, u.plan_id, u.imagen_perfil";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);
        $curso = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        return $curso;
    }

    //obtiene los materiales descargables de un curso
    public static function obtenerArchivos($cursoId) {
        $db = conectar();
        $sql = "SELECT * FROM curso_archivos WHERE curso_id = ? ORDER BY fecha_subida DESC";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        mysqli_stmt_execute($stmt);
        $archivos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $archivos;
    }

    // actualiza un curso 
    public static function actualizarCurso($cursoId, $titulo, $descripcion, $costo) {
        $db = conectar();
        $sql = "UPDATE cursos SET titulo = ?, descripcion = ?, costo = ? WHERE id = ?";
        
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ssdi", $titulo, $descripcion, $costo, $cursoId);
        
        $actualizado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $actualizado;
            
        }
    
    // elimina un curso
    public static function eliminarCurso($cursoId) {
        $db = conectar();
        $sql = "DELETE FROM cursos WHERE id = ?";
        
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cursoId);
        $eliminado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $eliminado;
    }

    //obtiene un archivo por su id
    public static function obtenerArchivoPorId($archivoId) {
        $db = conectar();
        $sql = "SELECT * FROM curso_archivos WHERE id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $archivoId);
        mysqli_stmt_execute($stmt);

        $archivo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        mysqli_stmt_close($stmt);

        //devuelve los datos del archivo si existe,  NULL/false si no lo encuentra
        return $archivo;
    }
}

?>