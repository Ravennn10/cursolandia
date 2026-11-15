<?php require_once __DIR__ . '/layouts/header.php'; ?>

<!-- Hero con buscador -->
<section class="p-4 p-md-5 mb-5 rounded-4 text-white shadow-sm" 
         style="background: linear-gradient(135deg, #052B2A 0%, #6B8F71 100%); border-bottom: 4px solid #AAD2BA;">
    <div class="row align-items-center py-2">
        <div class="col-lg-8 mx-auto text-center">
            
            <h1 class="display-5 fw-bold mb-3">Aprendé y compartí conocimiento en Cursolandia</h1>
            <p class="lead mb-4" style="color: #AAD2BA;">
                Explorá cursos gratuitos y pagos creados por la comunidad, descargá material de estudio y resolvé dudas en los foros.
            </p>

            <form action="index.php" method="GET" class="row g-2 justify-content-center">
                <input type="hidden" name="action" value="catalogo">
                <div class="col-md-8">
                    <input type="text" name="q" class="form-control form-control-lg border-0 shadow-sm" 
                           placeholder="¿Qué querés aprender hoy? (Ej: PHP, MySQL, Diseño...)">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-lg fw-bold shadow-sm" style="background-color: #B9F5D8; color: #052B2A;">
                        Buscar cursos
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Cursos Destacados -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-evergreen mb-0">Cursos destacados</h2>
            <p class="text-olive small mb-0">Propuestas activas seleccionadas por nuestra comunidad</p>
        </div>
        <a href="index.php?action=catalogo" class="link-cursolandia fs-6"> Ver todos los cursos </a>
    </div>

    <div class="row">
        <?php if (empty($cursosDestacados)): ?>
            <div class="col-12">
                <div class="alert alert-cursolandia-info">
                    Todavía no hay cursos activos publicados. ¡Sé el primero en crear uno!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($cursosDestacados as $curso): ?>
                <?php require __DIR__ . '/layouts/card_curso.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Texto guia -->
<section class="mb-4">
    <div class="card card-cursolandia rounded-4 shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-evergreen">¿Cómo funciona Cursolandia?</h2>
            <p class="text-muted">Todo lo que podés hacer dentro de la plataforma en tres simples pasos</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="h-100 p-4 rounded-4" style="background-color: #F4F6F5; border: 1px solid #AAD2BA;">
                    <div class="fs-4 mb-2">🔍</div>
                    <h5 class="fw-bold text-evergreen">1. Explorá e inscribite</h5>
                    <p class="text-secondary small mb-0">
                        Buscá cursos por título o etiquetas. Configurá tus intereses en tu perfil para recibir recomendaciones personalizadas y anotate en propuestas gratuitas o pagas.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="h-100 p-4 rounded-4" style="background-color: #F4F6F5; border: 1px solid #AAD2BA;">
                    <div class="fs-4 mb-2">📚</div>
                    <h5 class="fw-bold text-evergreen">2. Creá tus propios cursos</h5>
                    <p class="text-secondary small mb-0">
                        Publicá tus cursos definiendo fechas, cupos y costo. Compartí material de estudio descargable con tus alumnos y gestioná las inscripciones pagas.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="h-100 p-4 rounded-4" style="background-color: #F4F6F5; border: 1px solid #AAD2BA;">
                    <div class="fs-4 mb-2">💬</div>
                    <h5 class="fw-bold text-evergreen">3. Foro y Plan PRO</h5>
                    <p class="text-secondary small mb-0">
                        Resolvé dudas en el foro de cada curso con respuestas aceptadas y notificaciones en tiempo real. Con el <strong>Plan PRO</strong> creás(destacados) y te inscribís sin límites.
                    </p>
                </div>
            </div>
        </div>

        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <div class="text-center mt-4 pt-2">
                <a href="index.php?action=registro" class="btn btn-evergreen px-4 py-2 fw-semibold me-2">
                    Crear cuenta gratis
                </a>
                <a href="index.php?action=login" class="btn btn-outline-evergreen px-4 py-2 fw-semibold">
                    Ya tengo cuenta
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>