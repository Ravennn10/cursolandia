<?php 
// MODELO: acceso a la tabla usuarios.
// Solo consultas a la base: no imprime HTML ni toca la sesión.

require_once __DIR__ . '/../../config/conexion.php';

class Usuario {

    // - - - - - LOGIN / REGISTRO - - - - -

    // buscar usuario por correo electrónico (para login)
    public static function obtenerPorEmail($email){
        $db = conectar();
        $sql = "SELECT u.*, r.nombre AS rol_nombre, p.nombre AS plan_nombre 
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                JOIN planes p ON u.plan_id = p.id
                WHERE u.email = ?";
        
        $stmt = mysqli_prepare($db, $sql); //analiza sintaxis
        if (!$stmt) { return false; }   //si la consulta esta mal escrita q devuelva false para q no salte un fatal error
        mysqli_stmt_bind_param($stmt, "s", $email); //envia el parametro que era ? y pone el tipo s=string
        mysqli_stmt_execute($stmt); //ejecuta la consulta
        
        $resultado = mysqli_stmt_get_result($stmt);  //resultado de la consulta
        $usuario = mysqli_fetch_assoc($resultado);  //array asociativo de lo que recibió
        
        mysqli_stmt_close($stmt);   //cierra proceso (para no usar RAM)
        return $usuario;
    }

    // Registrar un nuevo usuario (Plan Gratuito = 1, Rol Usuario = 1 por defecto)
    public static function crearUsuario($nombre, $apellido, $email, $password){
        $db = conectar();
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);  // hash en la contraseña por seguridad
        
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password_hash, rol_id, plan_id) 
                VALUES (?, ?, ?, ?, 1, 1)";
        
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $nombre, $apellido, $email, $passwordHash);
        
        $exito = mysqli_stmt_execute($stmt); //devuelve true o false del INSERT
        mysqli_stmt_close($stmt);
        
        return $exito;
    }

    // verifica si el correo ya existe en la bdd
    //reutilizando obtenerporEmail() que busca al usuario por email, si devuelve true es pq ya existe el email, null = false si se puede usar el email ingresado 
    public static function existeEmail($email) {
        return self::obtenerPorEmail($email) !== null;
    }


    // - - - - -  PERFIL - - - - -

    //buscar por id (devuelve datos, rol y plan)
    public static function ObtenerPorId($id){
        $db=conectar();
        $sql = "SELECT u.*, r.nombre AS rol_nombre, p.nombre AS plan_nombre 
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                JOIN planes p ON u.plan_id = p.id
                WHERE u.id = ?";
        
        $stmt = mysqli_prepare($db, $sql); //analiza sintaxis
        if (!$stmt) { return false; }   //si la consulta esta mal escrita q devuelva false para q no salte un fatal error
        mysqli_stmt_bind_param($stmt, "i", $id); //envia el parametro que era ? y pone el tipo i=integer
        mysqli_stmt_execute($stmt); //ejecuta la consulta
        
        $resultado = mysqli_stmt_get_result($stmt);  //resultado de la consulta
        $usuario = mysqli_fetch_assoc($resultado);  //array asociativo de lo que recibió
        
        mysqli_stmt_close($stmt);   //cierra proceso (libera RAM)
        return $usuario;
    }

    //actualiza datos de perfil y foto 
    public static function actualizarPerfil($usuarioId, $nombre, $apellido, $antecedentes, $rutaImagen = NULL) {
        $db=conectar();
        if($rutaImagen !== NULL){
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, antecedentes = ?, imagen_perfil = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt,"ssssi", $nombre, $apellido, $antecedentes, $rutaImagen, $usuarioId);
        } else {
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, antecedentes = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt,"sssi", $nombre, $apellido, $antecedentes, $usuarioId);
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
        
    }

    //actualiza contraseña
    public static function actualizarContraseña($usuarioId, $passwordHash) {
        $db = conectar();
        $sql = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "si", $passwordHash, $usuarioId);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    //actualizar intereses
    public static function actualizarIntereses($usuarioId, $etiquetasTexto) {
        $db = conectar();

        //limpiar los que ya tenia antes (para evitar errores de duplicacion)
        $stmtEliminar = mysqli_prepare($db, "DELETE FROM usuarios_intereses WHERE usuario_id = ?");
        mysqli_stmt_bind_param($stmtEliminar, "i", $usuarioId);
        mysqli_stmt_execute($stmtEliminar);
        mysqli_stmt_close($stmtEliminar);

        //si $etiquetasTexto llegó vacio, elimina las que tenia, sale y no agrega ninguna etiqueta
        if(empty(trim($etiquetasTexto))) { return true; }

        //separa el string de etiquetas en un array, quita espacios, borra vacios, borra repetidos
        $lista = array_unique(array_filter(array_map('trim', explode(',', $etiquetasTexto))));

        foreach($lista as $nombreEtiqueta) {
            $nombreEtiqueta = mb_substr($nombreEtiqueta, 0, 50); //controla limite de caracteres por etiqueta

            //si la etiqueta no existe la inserta, si si existe se ignora la consulta(IGNORE)
            $stmtInsert = mysqli_prepare($db, "INSERT IGNORE INTO etiquetas (nombre) VALUES (?)");
            mysqli_stmt_bind_param($stmtInsert, "s", $nombreEtiqueta);
            mysqli_stmt_execute($stmtInsert);
            mysqli_stmt_close($stmtInsert);

            //obtiene id de la etiqueta
            $stmtGet = mysqli_prepare($db, "SELECT id FROM etiquetas WHERE nombre= ?");
            mysqli_stmt_bind_param($stmtGet, "s", $nombreEtiqueta);
            mysqli_stmt_execute($stmtGet);
            $etiqueta = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));
            mysqli_stmt_close($stmtGet);

            //si encuentra el id, inserta la etiqueta en la tabla intermedia vinculando al usuario con la etiqueta
            if($etiqueta) {
                $stmt = mysqli_prepare($db, "INSERT IGNORE INTO usuarios_intereses (usuario_id, etiqueta_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ii", $usuarioId, $etiqueta['id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        return true;
    }

    //obtener intereses(para mostrar en la vista)
    public static function obtenerIntereses($usuarioId) {
        $db = conectar();
        $sql = "SELECT GROUP_CONCAT(e.nombre ORDER BY e.nombre SEPARATOR ', ') AS intereses 
                FROM usuarios_intereses ui JOIN etiquetas e ON ui.etiqueta_id = e.id
                WHERE ui.usuario_id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila['intereses'] ?? '';
    }

    
    // - - - - -  SOLICITUDES PRO - - - - -
    
    //solicitar plan pro
    public static function crearSolicitudPro($usuarioId, $mensaje, $rutaComprobante) {
        $db = conectar();
        $sql = "INSERT INTO solicitudes_pro (usuario_id, mensaje, ruta_comprobante, estado) VALUES (?, ?, ?, 'pendiente')";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $usuarioId, $mensaje, $rutaComprobante);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok;
    }

    //para que el usuario pueda ver el estado de su solicitud(si le rechazaron antes no importa, solo se va a ver la ultima)
    public static function obtenerUltimaSolicitudPro($usuarioId) {
        $db = conectar();
        $sql = "SELECT * FROM solicitudes_pro WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //para que el admin vea todas las solicitudes pendientes
    public static function obtenerSolicitudesProPendientes() {
        $db = conectar();
        $sql = "SELECT s.*, u.nombre, u.apellido, u.email 
                FROM solicitudes_pro s
                JOIN usuarios u ON s.usuario_id = u.id
                WHERE s.estado = 'pendiente'
                ORDER BY s.id ASC";

        $resultado = mysqli_query($db, $sql);

        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }

    //apra que el admin pueda acceder a revisar la solicitud
    public static function obtenerSolicitudProPorId($solicitudId) {
        $db = conectar();
        $sql = "SELECT * FROM solicitudes_pro WHERE id = ?";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $solicitudId);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila;
    }

    //modifica el estado de la solicitud (el admin aprueba o rechaza)
    public static function resolverSolicitudPro ($solicitudId, $usuarioId, $adminId, $nuevoEstado) {
        $db = conectar();

        if($nuevoEstado === 'aprobada'){
            //date toma lo que le devuelve strtotime(convierte, a partir de la fecha de hoy, la fecha futura en que va a vencer el plan en timestamp) para guardarlo en la bdd con el formato Y-m-d
            $fechaVence = date('Y-m-d', strtotime("+30 days")); 
        
            //actualiza estado, gaurda admin_id y fecha_vencimiento (en solicitudes_pro)
            $sql = "UPDATE solicitudes_pro SET estado = 'aprobada', admin_id = ?, fecha_vencimiento = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "isi", $adminId, $fechaVence, $solicitudId);
            $ok =mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            //cambia el plan del usuario(plan PRO = 2)
            if ($ok) {
                $stmtUsuario = mysqli_prepare($db, "UPDATE usuarios SET plan_id = 2, plan_vence_en = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmtUsuario, "si", $fechaVence, $usuarioId);
                mysqli_stmt_execute($stmtUsuario);
                mysqli_stmt_close($stmtUsuario);
            }

            return $ok;
            
        } else {
            //si se rechazo, guarda estado rechazado y el admin_id
            $stmt = mysqli_prepare($db, "UPDATE solicitudes_pro SET estado = 'rechazada', admin_id = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $adminId, $solicitudId);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            return $ok;
        }
    }


    


}


