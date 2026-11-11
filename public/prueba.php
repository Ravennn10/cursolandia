<?php 
    require_once __DIR__ . '/../config/conexion.php';

    $con = conectar();

    $resultado = mysqli_query($con, "SELECT id, nombre, email FROM usuarios");
    while($fila = mysqli_fetch_assoc($resultado)){
        echo $fila['id'] . ' - ' .  $fila['nombre'] . ' - ' . $fila['email'] . '<br>';
    }
?>