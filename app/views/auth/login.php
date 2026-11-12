<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursolandia - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">Iniciar Sesión</h3>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger mb-3">
                            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['exito'])): ?>
                        <div class="alert alert-success mb-3">
                            <?= $_SESSION['exito']; unset($_SESSION['exito']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=procesar_login" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center mb-0">¿No tienes cuenta? <a href="index.php?action=registro">Regístrate</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>