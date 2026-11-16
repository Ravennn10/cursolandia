<?php
/** @var array $usuario */
/** @var string $intereses */
/** @var array|null $ultimaSolicitud */
/** @var bool $esAdmin */
/** @var array $solicitudesPendientes */
/** @var array $notificaciones */

require_once __DIR__ . '/../layouts/header.php';

$esProActivo = ($usuario['plan_id'] == 2 && (empty($usuario['plan_vence_en']) || $usuario['plan_vence_en'] >= date('Y-m-d')));
?>

<div class="col-lg-9 mx-auto">

    <!-- cuenta -->
    <div class="d-flex align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">

        <?php if (!empty($usuario['imagen_perfil']) && file_exists(__DIR__ . '/../../../public/' . $usuario['imagen_perfil'])): ?>
            <img src="<?= htmlspecialchars($usuario['imagen_perfil']) ?>" alt="Foto de perfil" class="rounded-circle object-fit-cover border" style="width: 64px; height: 64px;">

        <?php else: ?>
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width: 64px; height: 64px; background-color: #052B2A;">
                <?= strtoupper(mb_substr($usuario['nombre'], 0, 1) . mb_substr($usuario['apellido'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <h4 class="text-evergreen mb-0"> <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?> </h4>

        <span class="text-muted">|</span>

        <span class="text-muted"> <?= htmlspecialchars($usuario['email']) ?> </span>

        <span class="text-muted">|</span>

        <div>
            <?php if ($esAdmin): ?>
                <span class="badge bg-dark">Administrador</span>

            <?php elseif ($esProActivo): ?>
                <span class="badge badge-pro">Plan PRO Activo</span>
                <?php if (!empty($usuario['plan_vence_en'])): ?>
                    <small class="text-muted ms-1">(Vence: <?= date('d/m/Y', strtotime($usuario['plan_vence_en'])) ?>)</small>
                <?php endif; ?>

            <?php else: ?>
                <span class="badge badge-gratis">Plan Gratuito</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- notif -->
    <div class="card card-cursolandia mb-4">
        <div class="card-header card-header-cursolandia d-flex justify-content-between align-items-center">
            <span>Notificaciones</span>

            <?php if (!empty($notificaciones)): ?>
                <form action="index.php?action=leer_notificaciones" method="POST" class="m-0">
                    <button type="submit" class="btn btn-sm btn-light py-0">Marcar como leídas</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-body">

            <?php if (empty($notificaciones)): ?>
                <p class="text-muted small mb-0">No tenés notificaciones.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">

                    <?php foreach ($notificaciones as $n): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 <?= $n['leida'] == 0 ? 'fw-bold' : 'text-muted' ?>">
                            
                            <div class="small">
                                <?= htmlspecialchars($n['mensaje']) ?>
                                <span class="d-block text-muted fw-normal" style="font-size: 0.78rem;">
                                    Curso: <?= htmlspecialchars($n['curso_titulo']) ?> | <?= date('d/m/Y H:i', strtotime($n['fecha'])) ?>
                                </span>
                            </div>
                            <a href="index.php?action=detalle_curso&id=<?= $n['curso_id'] ?>" class="btn btn-sm btn-outline-evergreen ms-2">
                                Ver curso
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>


    <!-- datos -->
    <div class="card card-cursolandia mb-4">
        <div class="card-header card-header-cursolandia"> Datos Personales </div>
        <div class="card-body">
            <form action="index.php?action=actualizar_perfil" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-evergreen">Nombre *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-evergreen">Apellido *</label>
                        <input type="text" name="apellido" class="form-control form-control-sm" required value="<?= htmlspecialchars($usuario['apellido']) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-evergreen">Antecedentes / Sobre mí</label>
                    <textarea name="antecedentes" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($usuario['antecedentes'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-evergreen">Foto de perfil (opcional)</label>
                    <input type="file" name="imagen_perfil" class="form-control form-control-sm" accept="image/*">
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">(Al presionar el botón se actualizarán tus datos actuales)</small>
                    <button type="submit" class="btn btn-evergreen btn-sm px-3">Actualizar datos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- intereses -->
    <div class="card card-cursolandia mb-4">
        <div class="card-header card-header-cursolandia"> Mis Intereses </div>
        <div class="card-body">
            <div class="mb-3">
                <span class="small fw-bold text-evergreen d-block mb-1">Intereses actuales:</span>
                <?php if (!empty($intereses)): ?>
                    <?php foreach (explode(', ', $intereses) as $tag): ?>
                        <span class="badge badge-tag me-1"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted small">Todavía no tenés intereses guardados.</span>
                <?php endif; ?>
            </div>

            <form action="index.php?action=guardar_intereses" method="POST">
                <label class="form-label small fw-bold text-evergreen">Nuevas etiquetas (separadas por comas)</label>
                <div class="input-group input-group-sm mb-2">
                    <input type="text" name="intereses" class="form-control" placeholder="Ej: PHP, MySQL, Diseño, Marketing" value="<?= htmlspecialchars($intereses) ?>">
                    <button type="submit" class="btn btn-olive">Actualizar intereses</button>
                </div>
                <small class="text-muted d-block">Aviso: al actualizar, se borrarán los intereses anteriores y se reemplazarán por los que queden escritos arriba.</small>
            </form>
        </div>
    </div>

    <!-- cambiar contraseña -->
    <div class="card card-cursolandia mb-4">
        <div class="card-header card-header-cursolandia"> Cambiar Contraseña </div>
        <div class="card-body">
            <form action="index.php?action=actualizar_password" method="POST" class="row g-2 align-items-end">

                <div class="col-md-5">
                    <label class="form-label small fw-bold text-evergreen">Nueva contraseña (mín. 6)</label>
                    <input type="password" name="password_nueva" class="form-control form-control-sm" required minlength="6">
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-evergreen">Repetir contraseña</label>
                    <input type="password" name="password_confirmar" class="form-control form-control-sm" required minlength="6">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-evergreen btn-sm">Actualizar</button>
                </div>

            </form>
        </div>
    </div>

    <!-- solicitar plan pro (solo lo ve el admin y los q no tiene plan pro activo) -->
    <?php if (!$esAdmin && !$esProActivo): ?>
        <div class="card card-cursolandia mb-4">
            <div class="card-header card-header-cursolandia"> Solicitar Plan PRO </div>
            <div class="card-body">
                <p class="small text-muted"> Con el Plan PRO podés crear e inscribirte en cursos sin límites y destacar tus cursos en el catálogo (vigencia 30 días). </p>

                <?php if ($ultimaSolicitud && $ultimaSolicitud['estado'] === 'pendiente'): ?>
                    <div class="alert alert-warning small mb-0"> Ya tenés una solicitud pendiente de revisión.
                        <a href="index.php?action=ver_comprobante&id=<?= $ultimaSolicitud['id'] ?>" class="ms-2 link-cursolandia"> Ver comprobante enviado </a>
                    </div>
                <?php else: ?>
                    <?php if ($ultimaSolicitud && $ultimaSolicitud['estado'] === 'rechazada'): ?>
                        <div class="alert alert-danger small">Tu solicitud anterior fue rechazada. Podés enviar una nueva.</div>
                    <?php endif; ?>

                    <form action="index.php?action=solicitar_pro" method="POST" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-evergreen">Mensaje / Referencia</label>
                            <textarea name="mensaje" class="form-control form-control-sm" rows="2" placeholder="Ej: Envío comprobante..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-evergreen">Comprobante *</label>
                            <input type="file" name="comprobante" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-evergreen btn-sm"> Enviar solicitud PRO </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- panel admin (solo rol_id = 2) -->
    <?php if ($esAdmin): ?>
        <div class="card card-cursolandia-pro mb-4">
            <div class="card-header card-header-cursolandia"> Panel ADMIN: Solicitudes de Plan PRO (<?= count($solicitudesPendientes) ?> pendientes) </div>
            <div class="card-body">
                <?php if (empty($solicitudesPendientes)): ?>
                    <p class="text-muted mb-0">No hay solicitudes pendientes de revisión.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Mensaje</th>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudesPendientes as $sol): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']) ?></strong>
                                            <small class="text-muted d-block"><?= htmlspecialchars($sol['email']) ?></small>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($sol['mensaje'] ?: '-') ?></td>
                                        <td class="text-center">
                                            <a href="index.php?action=ver_comprobante&id=<?= $sol['id'] ?>" class="btn btn-sm btn-outline-evergreen"> Descargar </a>
                                        </td>
                                        <td class="text-center">
                                            <form action="index.php?action=gestionar_solicitud_pro" method="POST" class="d-inline-flex gap-1">

                                                <input type="hidden" name="solicitud_id" value="<?= $sol['id'] ?>">
                                                <button type="submit" name="accion" value="aprobada" class="btn btn-sm btn-evergreen">Aceptar</button>

                                                <button type="submit" name="accion" value="rechazada" class="btn btn-sm btn-outline-danger">Rechazar</button>
                                            </form>
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

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>