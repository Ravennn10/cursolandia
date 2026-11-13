<?php require_once __DIR__ . '/layouts/header.php'; ?>

<!-- Hero con buscador -->
<section class="p-4 p-md-5 mb-5 rounded-4 text-white shadow-sm" 
         style="background: linear-gradient(135deg, #052B2A 0%, #6B8F71 100%); border-bottom: 4px solid #AAD2BA;">
    <div class="row align-items-center py-2">
        <div class="col-lg-8 mx-auto text-center">
            <span class="badge badge-pro rounded-pill px-3 py-2 mb-3">Plataforma de Aprendizaje</span>
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
                    <button type="submit" class="btn btn-lg fw-bold shadow-sm" 
                            style="background-color: #B9F5D8; color: #052B2A;">
                        Buscar cursos
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>





<?php require_once __DIR__ . '/layouts/footer.php'; ?>