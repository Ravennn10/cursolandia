<?php
/** @var array $plan */
/** @var int $cursosActivos */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-cursolandia shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-evergreen mb-0">Proponer Nuevo Curso</h3>
                    <span class="badge badge-gratis px-3 py-2">
                        Cursos activos: <?= $cursosActivos ?> / <?= $plan['max_cursos_activos'] ?? '∞' ?>
                    </span>
                </div>

                <form action="index.php?action=guardar_curso" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-evergreen">Título del Curso *</label>
                        <input type="text" name="titulo" class="form-control" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-evergreen">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-evergreen">Etiquetas (opcional, separadas por comas)</label>
                        <input type="text" name="etiquetas" class="form-control" placeholder="Ej: PHP, MySQL, Diseño Web">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-evergreen">Costo ($ ARS)</label>
                            <input type="number" name="costo" class="form-control" step="0.01" min="0" placeholder="0 = Gratuito">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-evergreen">Cupo máximo</label>
                            <input type="number" name="cupo" class="form-control" min="1" placeholder="Vacío = Sin límite">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-evergreen">Visibilidad</label>
                            <select name="es_publico" class="form-select">
                                <option value="1" selected>Público</option>
                                <option value="0">Privado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-evergreen">Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-evergreen">Fecha de Fin *</label>
                            <input type="date" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-evergreen">Material de estudio (opcional)</label>
                        <input type="file" name="materiales[]" class="form-control" multiple>
                        <?php if ($plan['plan_id'] == 1): ?>
                            <div class="form-text text-olive">
                                Plan Gratuito: máximo 5 archivos por curso, hasta 5 MB cada uno y 10 MB en total.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?action=catalogo" class="btn btn-outline-evergreen">Cancelar</a>
                        <button type="submit" class="btn btn-evergreen px-4">Publicar Curso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>