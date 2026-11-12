<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-5 col-lg-4">
        <div class="card card-cursolandia shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="text-center fw-bold text-evergreen mb-4">Iniciar Sesión</h3>

                <form action="index.php?action=procesar_login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-evergreen">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold text-evergreen">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-evergreen w-100 py-2 fw-semibold">Ingresar</button>
                </form>

                <hr class="my-4" style="border-color: #AAD2BA;">
                <p class="text-center mb-0">
                    ¿No tienes cuenta? <a href="index.php?action=registro" class="link-cursolandia">Regístrate</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>