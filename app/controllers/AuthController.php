<?php
// CONTROLADOR: registro, login, logout

if (session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    //procesar inicio de sesion
    public static function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if(empty($email) || empty($password)){
                $_SESSION['error'] = "Todos los campos son obligatorios.";
                header("Location: index.php?action=login");
                exit;
            }

            $usuario = Usuario::obtenerPorEmail($email);

            if($usuario && password_verify($password, $usuario['password_hash'])) {
                //inicia sesion y guarda datos
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nom'] = $usuario['nombre'] . ' ' . $usuario['apelldio'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['plan_id'] = $usuario['plan_id'];
                $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
                
                header("Location: index.php?action=catalogo");
                exit;
            } else {
                $_SESSION['error'] = "Credenciales Incorrectas";
                header("Location: index.php?action=login");
                exit;
            }
        }
    }

    //procesar registro
    public static function registro(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? ''); 

            if(empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
                $_SESSION['error'] = "Completar todos los campos!";
                header("Location: index.php?action=registro");
                exit;
            }

            if(Usuario::existeEmail($email)) {
                $_SESSION['error'] = "El correo ingresado ya se encuentra registrado!";
                header("Location: index.php?action=registro");
                exit;
            }

            if(Usuario::crearUsuario($nombre, $apellido, $email, $password)) {
                $_SESSION['exito'] = "Cuenta creada con éxito! Ya puede iniciar sesión...";
                header("Location: index.php?action=login");
                exit;
            } else {
                $_SESSION['error'] = "Error al registrar. Intente nuevamente";
                header("Location: index.php?action=registro");
                exit;
            }
        }
    }

    //cerrar sesion
    public static function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }

}
?>