<?php 
// MODELO: acceso a la tabla usuarios.
// Solo consultas a la base: no imprime HTML ni toca la sesión.

require_once __DIR__ . '/../../config/conexion.php';

class Usuario {

    //buscar por id
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
        
        mysqli_stmt_close($stmt);   //cierra proceso (para no usar RAM)
        return $usuario;
    }

    // buscar usuario por correo electrónico
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




}


