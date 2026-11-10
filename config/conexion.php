<?php

function conectar(): mysqli {
    static $conexion = null;   // se crea una sola vez y se reutiliza

    if ($conexion === null) {
        $c = require __DIR__ . '/config.php';

        // Para que mysqli lance excepciones (equivale a ERRMODE_EXCEPTION)
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conexion = mysqli_connect($c['db_host'], $c['db_user'], $c['db_pass'], $c['db_name']);
        mysqli_set_charset($conexion, 'utf8mb4');
    }
    return $conexion;
}


?>