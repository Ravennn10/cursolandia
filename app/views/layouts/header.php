<?php
if (session_status() === PHP_SESSION_NONE) session_start();
 
$noLeidas = 0;
if(isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../../models/Foro.php';
    $noLeidas= Foro::contarNoLeidas($_SESSION['usuario_id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursolandia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body class="bg-cursolandia d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark navbar-cursolandia shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php?action=home" >
            🎓 Cursolandia
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=home">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=catalogo">Explorar Cursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=mis_cursos">Mis Cursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=crear_curso">Crear Curso +</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="index.php?action=perfil" style="color: #B9F5D8;">
                            👤 <?= htmlspecialchars($_SESSION['usuario_nom'] ?? 'Mi Perfil') ?>
                            <?php if ($noLeidas > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $noLeidas ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3" href="index.php?action=logout">
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
                
            <?php else: ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=home">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=catalogo">Explorar Cursos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=login">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-olive btn-sm fw-semibold px-3" href="index.php?action=registro">
                            Registrarse
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container flex-grow-1">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-cursolandia-info alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['exito']); unset($_SESSION['exito']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>