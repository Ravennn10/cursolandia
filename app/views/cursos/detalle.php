<?php
/** @var array $curso */
/** @var array $archivos */
/** @var bool $esCreador */
/** @var int $ocupados */
/** @var array|null $miInscripcion */
/** @var array $inscripcionesCurso */
/** @var array $preguntas */

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

<!-- FORO  -->
<div class="card card-cursolandia mb-4">
    <div class="card-header card-header-cursolandia">
        Foro del Curso
    </div>
    <div class="card-body">
        <?php if (!$puedeDescargar): ?>
            <p class="text-muted mb-0">Debés tener una inscripción confirmada para participar en el foro.</p>
        <?php else: ?>

            <!-- FORM pregunta nueva -->
            <form action="index.php?action=crear_pregunta" method="POST" class="mb-3">
                <input type="hidden" name="curso_id" value="<?= $curso['id'] ?>">
                <div class="mb-2">
                    <label class="form-label fw-bold text-evergreen">Nueva pregunta</label>
                    <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Título de la pregunta" required>
                </div>
                <div class="mb-2">
                    <textarea name="contenido" class="form-control form-control-sm" rows="2" placeholder="Escribí tu consulta..." required></textarea>
                </div>
                <button type="submit" class="btn btn-evergreen btn-sm">Publicar pregunta</button>
            </form>

            <hr>

            <!-- lista preguntas -->
            <?php if (empty($preguntas)): ?>
                <p class="text-muted mb-0">No hay preguntas todavía.</p>
            <?php else: ?>
                <?php foreach ($preguntas as $p): ?>
                    <div class="border p-2 mb-3">
                        <div>
                            <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                            <?php if ($p['destacada'] == 1): ?>
                                <span class="badge badge-pro">Destacada</span>
                            <?php endif; ?>
                            <?php if (!empty($p['respuesta_aceptada_id'])): ?>
                                <span class="badge bg-danger">Cerrada</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">
                                Publicada por <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?> (<?= date('d/m/Y H:i', strtotime($p['fecha_creada'])) ?>)
                            </small>
                        </div>

                        <p class="mt-2 mb-2"><?= nl2br(htmlspecialchars($p['contenido'])) ?></p>

                        <?php if ($p['autor_id'] == $_SESSION['usuario_id'] || $esCreador): ?>
                            <form action="index.php?action=eliminar_pregunta" method="POST" class="mb-2" onsubmit="return confirm('¿Eliminar esta pregunta?');">
                                <input type="hidden" name="pregunta_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar pregunta</button>
                            </form>
                        <?php endif; ?>

                        <!-- respuestas -->
                        <div class="ms-4 mt-2">
                            <?php foreach ($p['respuestas'] as $r): ?>
                                <?php $esAceptada = ($p['respuesta_aceptada_id'] == $r['id']); ?>
                                <div class="border-top pt-2 mt-2">
                                    <strong><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellido']) ?></strong>
                                    <?php if ($esAceptada): ?>
                                        <span class="badge bg-success">Respuesta aceptada</span>
                                    <?php endif; ?>
                                    <small class="text-muted">- <?= date('d/m/Y H:i', strtotime($r['fecha_creada'])) ?></small>

                                    <p class="mb-1"><?= nl2br(htmlspecialchars($r['contenido'])) ?></p>

                                    <?php if (empty($p['respuesta_aceptada_id']) && ($p['autor_id'] == $_SESSION['usuario_id'] || $esCreador)): ?>
                                        <form action="index.php?action=aceptar_respuesta" method="POST" class="mb-1">
                                            <input type="hidden" name="pregunta_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="respuesta_id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm">Aceptar respuesta y cerrar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <!-- FORM responder (si sigue abierta) -->
                            <?php if (empty($p['respuesta_aceptada_id'])): ?>
                                <form action="index.php?action=crear_respuesta" method="POST" class="mt-2">
                                    <input type="hidden" name="pregunta_id" value="<?= $p['id'] ?>">
                                    <input type="text" name="contenido" class="form-control form-control-sm mb-1" placeholder="Escribir respuesta..." required>
                                    <button type="submit" class="btn btn-olive btn-sm">Responder</button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted d-block mt-2">Pregunta cerrada.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>