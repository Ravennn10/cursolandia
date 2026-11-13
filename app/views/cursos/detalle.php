<?php
/** @var array $curso */
/** @var array $archivos */
/** @var bool $esCreador */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card shadow-sm rounded-4 mb-4 <?= $curso['creador_plan'] == 2 ? 'card-cursolandia-pro' : 'card-cursolandia' ?>">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <?php if ($curso['creador_plan'] == 2): ?>
                    <span class="badge badge-pro mb-2">★ Curso de Usuario PRO</span>
                <?php endif; ?>
                <h2 class="fw-bold text-evergreen mb-1"><?= htmlspecialchars($curso['titulo']) ?></h2>
                <p class="text-olive mb-0">
                    Docente: <strong><?= htmlspecialchars($curso['creador_nombre'] . ' ' . $curso['creador_apellido']) ?></strong> |
                    Desde <?= date('d/m/Y', strtotime($curso['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($curso['fecha_fin'])) ?>
                </p>
            </div>

            <div class="text-end">
                <h4 class="mb-1">
                    <?php if ($curso['costo'] > 0): ?>
                        <span class="badge badge-precio">$ <?= number_format($curso['costo'], 2, ',', '.') ?></span>
                    <?php else: ?>
                        <span class="badge badge-gratis">Gratuito</span>
                    <?php endif; ?>
                </h4>
                <small class="text-muted d-block mb-2">Cupo: <?= $curso['cupo'] ?? 'Ilimitado' ?></small>

                <?php if ($esCreador): ?>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="index.php?action=editar_curso&id=<?= $curso['id'] ?>" class="btn btn-sm btn-outline-evergreen">
                            Editar curso
                        </a>
                        <form action="index.php?action=eliminar_curso" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este curso?');">
                            <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="lead fs-6 text-dark"><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

        <?php if (!empty($curso['etiquetas'])): ?>
            <div class="mb-2">
                <?php foreach (explode(', ', $curso['etiquetas']) as $tag): ?>
                    <span class="badge badge-tag me-1">#<?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Materiales del Curso -->
<div class="card card-cursolandia shadow-sm rounded-4 mb-4">
    <div class="card-header card-header-cursolandia fw-bold rounded-top-4">
        Materiales del Curso
    </div>
    <div class="card-body">
        <?php if (empty($archivos)): ?>
            <p class="text-muted mb-0">Este curso aún no tiene archivos adjuntos.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush mb-3">
                <?php foreach ($archivos as $arch): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="border-color: #AAD2BA;">
                        <div>
                            📄 <a href="index.php?action=descargar_material&id=<?= $arch['id'] ?>" class="link-cursolandia">
                                <?= htmlspecialchars($arch['nombre_archivo']) ?>
                            </a>
                            <small class="text-muted ms-2">(<?= round($arch['peso_bytes'] / 1048576, 2) ?> MB)</small>
                        </div>
                        <a href="index.php?action=descargar_material&id=<?= $arch['id'] ?>" class="btn btn-sm btn-outline-evergreen">
                            Descargar
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($esCreador): ?>
            <form action="index.php?action=subir_material" method="POST" enctype="multipart/form-data" class="pt-3 mt-2" style="border-top: 1px solid #AAD2BA;">
                <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                <label class="form-label small fw-bold text-evergreen">Agregar nuevo archivo al curso:</label>
                <div class="input-group">
                    <input type="file" name="material" class="form-control" required>
                    <button type="submit" class="btn btn-olive">Subir Archivo</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>