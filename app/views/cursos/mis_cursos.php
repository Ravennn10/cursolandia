<?php
/** @var array $cursosCreados */
/** @var array $cursosInscripto */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-evergreen mb-0">Mis Cursos</h3>
    <a href="index.php?action=crear_curso" class="btn btn-evergreen btn-sm">+ Crear Nuevo Curso</a>
</div>

<!-- CURSOS CREADOS POR EL USUARIO -->
<div class="card card-cursolandia mb-4">
    <div class="card-header card-header-cursolandia">
        Cursos creados por mí (<?= count($cursosCreados) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($cursosCreados)): ?>
            <p class="text-muted mb-0">Aún no has creado ningún curso.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Fechas</th>
                            <th>Costo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursosCreados as $c): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($c['titulo']) ?></strong>
                                    <?php if ($c['es_publico'] == 0): ?>
                                        <span class="badge bg-secondary ms-1">Privado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <div>
                                        <?= date('d/m/Y', strtotime($c['fecha_inicio'])) ?> - 
                                        <?= date('d/m/Y', strtotime($c['fecha_fin'])) ?>
                                    </div>
                                    <?php if ($c['fecha_fin'] < date('Y-m-d')): ?>
                                        <span class="badge bg-secondary ms-1">Terminado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $c['costo'] > 0 ? '$ ' . number_format($c['costo'], 0, ',', '.') : '<span class="badge badge-gratis">Gratis</span>' ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?action=detalle_curso&id=<?= $c['id'] ?>" class="btn btn-sm btn-evergreen">
                                        Ir al curso
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!--CURSOS EN LOS QUE ESTÁ INSCRIPTO -->
<div class="card card-cursolandia mb-4">
    <div class="card-header card-header-cursolandia">
        Cursos en los que estoy inscripto (<?= count($cursosInscripto) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($cursosInscripto)): ?>
            <p class="text-muted mb-0">
                Todavía no te inscribiste a ningún curso. <a href="index.php?action=catalogo" class="link-cursolandia">Explorar catálogo</a>
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th>Docente</th>
                            <th>Cursada</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursosInscripto as $ins): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ins['titulo']) ?></strong></td>
                                <td class="small"><?= htmlspecialchars($ins['creador_nombre'] . ' ' . $ins['creador_apellido']) ?></td>
                                <td class="small">
                                    <?= date('d/m/Y', strtotime($ins['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($ins['fecha_fin'])) ?>
                                </td>
                                <td>
                                    <?php if ($ins['estado_inscripcion'] === 'confirmada'): ?>
                                        <span class="badge bg-success">Confirmada</span>
                                    <?php elseif ($ins['estado_inscripcion'] === 'pendiente'): ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Rechazada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?action=detalle_curso&id=<?= $ins['id'] ?>" class="btn btn-sm btn-evergreen">
                                        Ir al curso
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>