<?php
/** @var string $busqueda */
/** @var array $cursos */
/** @var array $recomendados */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold text-evergreen mb-0">Catálogo de Cursos</h2>
    </div>
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex gap-2">
            <input type="hidden" name="action" value="catalogo">
            <input type="text" name="q" class="form-control" placeholder="Buscar por título o etiqueta..." value="<?= htmlspecialchars($busqueda) ?>">
            <button type="submit" class="btn btn-evergreen px-4">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="index.php?action=catalogo" class="btn btn-outline-evergreen">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!empty($recomendados)): ?>
    <div class="mb-5">
        <h4 class="fw-bold text-olive mb-3">Recomendados según tus intereses</h4>
        <div class="row">
            <?php foreach ($recomendados as $curso): ?>
                <?php require __DIR__ . '/../layouts/card_curso.php'; ?>
            <?php endforeach; ?>
        </div>
        <hr style="border-color: #6B8F71;">
    </div>
<?php endif; ?>

<h4 class="fw-bold text-evergreen mb-3">
    <?= $busqueda !== '' ? 'Resultados para "' . htmlspecialchars($busqueda) . '"' : 'Todos los cursos disponibles' ?>
</h4>
<div class="row">
    <?php if (empty($cursos)): ?>
        <div class="col-12">
            <div class="alert alert-cursolandia-info">No se encontraron cursos activos.</div>
        </div>
    <?php else: ?>
        <?php foreach ($cursos as $curso): ?>
            <?php require __DIR__ . '/../layouts/card_curso.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>