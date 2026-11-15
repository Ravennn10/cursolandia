<?php
/** @var array $curso */
/** @var array $archivos */
/** @var bool $esCreador */
/** @var int $ocupados */
/** @var array|null $miInscripcion */
/** @var array $inscripcionesCurso */
require_once __DIR__ . '/../layouts/header.php';

$cursoFinalizado = ($curso['fecha_fin'] < date('Y-m-d'));
$sinCupo = ($curso['cupo'] !== null && $ocupados >= (int)$curso['cupo']);
$puedeDescargar = ($esCreador || ($miInscripcion && $miInscripcion['estado'] === 'confirmada'));
?>

<div class="mb-3">
    <a href="index.php?action=catalogo" class="link-cursolandia">&larr; Volver al catálogo</a>
</div>

<!--  -->
<div class="card mb-4 <?= $curso['creador_plan'] == 2 ? 'card-cursolandia-pro' : 'card-cursolandia' ?>">
    <div class="card-header card-header-cursolandia d-flex justify-content-between align-items-center">
        <span>
            Detalle del Curso
            <?php if ($curso['creador_plan'] == 2): ?>
                <span class="badge badge-pro ms-2">PRO</span>
            <?php endif; ?>
        </span>

        <?php if ($esCreador): ?>
            <div class="d-flex gap-2">
                <a href="index.php?action=editar_curso&id=<?= $curso['id'] ?>" class="btn btn-sm btn-light">
                    Editar
                </a>
                <form action="index.php?action=eliminar_curso" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este curso?');">
                    <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

<!-- CONTENIDO DEL CURSO -->
    <div class="card-body">
        <h3 class="text-evergreen mb-1"><?= htmlspecialchars($curso['titulo']) ?></h3>
        <p class="text-muted small mb-3">
            Docente: <strong><?= htmlspecialchars($curso['creador_nombre'] . ' ' . $curso['creador_apellido']) ?></strong> |
            Inicio: <?= date('d/m/Y', strtotime($curso['fecha_inicio'])) ?> |
            Fin: <?= date('d/m/Y', strtotime($curso['fecha_fin'])) ?>
            <?php if ($curso['fecha_fin'] < date('Y-m-d')): ?>
                <span class="badge bg-secondary ms-1">Terminado</span>
            <?php endif; ?>
        </p>
        

        <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>

        <?php if (!empty($curso['etiquetas'])): ?>
            <div class="mb-3">
                <?php foreach (explode(', ', $curso['etiquetas']) as $tag): ?>
                    <span class="badge badge-tag me-1"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="me-3">
                    <strong>Costo:</strong>
                    <?php if ($curso['costo'] > 0): ?>
                        <span class="badge badge-precio">$ <?= number_format($curso['costo'], 2, ',', '.') ?></span>
                    <?php else: ?>
                        <span class="badge badge-gratis">Gratis</span>
                    <?php endif; ?>
                </span>
                <span>
                    <strong>Cupo:</strong>
                    <?= $curso['cupo'] !== null ? "{$ocupados} / {$curso['cupo']}" : "{$ocupados} inscriptos (Ilimitado)" ?>
                </span>
            </div>

            <!-- ESTADO O BOTÓN DE INSCRIPCIÓN (SOLO ALUMNOS) -->
            <?php if (!$esCreador): ?>
                <div>
                    <?php if ($miInscripcion && $miInscripcion['estado'] === 'confirmada'): ?>
                        <span class="badge bg-success p-2">Inscripción confirmada</span>

                    <?php elseif ($miInscripcion && $miInscripcion['estado'] === 'pendiente'): ?>
                        <span class="badge bg-warning text-dark p-2">Pendiente de aprobación del docente</span>

                    <?php elseif ($cursoFinalizado): ?>
                        <button class="btn btn-secondary btn-sm" disabled>Curso finalizado</button>

                    <?php elseif ($sinCupo): ?>
                        <button class="btn btn-secondary btn-sm" disabled>Cupo completo</button>

                    <?php else: ?>
                        <form action="index.php?action=inscribirse" method="POST" class="d-inline">
                            <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                            <button type="submit" class="btn btn-evergreen">
                                <?= $curso['costo'] > 0 ? 'Solicitar inscripción' : 'Inscribirme' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MATERIALES DEL CURSO -->
<div class="card card-cursolandia mb-4">
    <div class="card-header card-header-cursolandia">
        Materiales del Curso
    </div>
    <div class="card-body">
        <?php if (empty($archivos)): ?>
            <p class="text-muted mb-0">No hay archivos cargados en este curso.</p>
        <?php else: ?>
            <ul class="list-group mb-3">
                <?php foreach ($archivos as $arch): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <?= htmlspecialchars($arch['nombre_archivo']) ?>
                            <small class="text-muted ms-2">(<?= round($arch['peso_bytes'] / 1048576, 2) ?> MB)</small>
                        </div>

                        <?php if ($puedeDescargar): ?>
                            <a href="index.php?action=descargar_material&id=<?= $arch['id'] ?>" class="btn btn-sm btn-evergreen">
                                Descargar
                            </a>
                        <?php else: ?>
                            <span class="text-muted small">Requiere inscripción confirmada</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($esCreador): ?>
            <hr>
            <form action="index.php?action=subir_material" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                <label class="form-label small fw-bold text-evergreen">Subir nuevo material:</label>
                <div class="input-group input-group-sm">
                    <input type="file" name="material" class="form-control" required>
                    <button type="submit" class="btn btn-olive">Subir</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ALUMNOS INSCRIPTOS (SOLO CREADOR) -->
<?php if ($esCreador): ?>
    <div class="card card-cursolandia mb-4">
        <div class="card-header card-header-cursolandia">
            Alumnos Inscriptos (<?= count($inscripcionesCurso) ?>)
        </div>
        <div class="card-body">
            <?php if (empty($inscripcionesCurso)): ?>
                <p class="text-muted mb-0">Todavía no hay alumnos inscriptos.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Alumno</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripcionesCurso as $ins): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($ins['nombre'] . ' ' . $ins['apellido']) ?>
                                        <?php if ($ins['plan_id'] == 2): ?>
                                            <span class="badge badge-pro ms-1">PRO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($ins['email']) ?></td>
                                    <td>
                                        <?php if ($ins['estado'] === 'confirmada'): ?>
                                            <span class="badge bg-success">Confirmada</span>
                                        <?php elseif ($ins['estado'] === 'pendiente'): ?>
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rechazada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($ins['estado'] === 'pendiente'): ?>
                                            <div class="d-inline-flex gap-1">
                                                <form action="index.php?action=cambiar_estado_inscripcion" method="POST">
                                                    <input type="hidden" name="inscripcion_id" value="<?= $ins['id'] ?>">
                                                    <input type="hidden" name="estado" value="confirmada">
                                                    <button type="submit" class="btn btn-sm btn-evergreen">Confirmar</button>
                                                </form>

                                                <form action="index.php?action=cambiar_estado_inscripcion" method="POST">
                                                    <input type="hidden" name="inscripcion_id" value="<?= $ins['id'] ?>">
                                                    <input type="hidden" name="estado" value="rechazada">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Rechazar</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>