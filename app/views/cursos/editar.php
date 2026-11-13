<?php
/** @var array $curso */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-cursolandia shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="fw-bold text-evergreen mb-3">Editar Información del Curso</h3>

                <form action="index.php?action=actualizar_curso" method="POST">
                    <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-evergreen">Título del Curso *</label>
                        <input type="text" name="titulo" class="form-control" required maxlength="150"
                               value="<?= htmlspecialchars($curso['titulo']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-evergreen">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="5" required><?= htmlspecialchars($curso['descripcion']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-evergreen">Costo ($ ARS)</label>
                        <input type="number" name="costo" class="form-control" step="0.01" min="0"
                               value="<?= htmlspecialchars($curso['costo']) ?>">
                        <div class="form-text text-olive">Ingresa 0 si el curso es gratuito.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php?action=detalle_curso&id=<?= $curso['id'] ?>" class="btn btn-outline-evergreen">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-evergreen px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>