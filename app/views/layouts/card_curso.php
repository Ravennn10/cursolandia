<?php
/** @var array $curso */
$esPro = ($curso['creador_plan'] == 2);
$bgBanner = $esPro ? '#052B2A' : '#6B8F71';
$colorIcono = $esPro ? '#B9F5D8' : '#FFFFFF';
$icono = 'fa-solid fa-graduation-cap';
?>

<div class="col-md-4 mb-4">
    <div class="card h-100 rounded-4 shadow-sm overflow-hidden <?= $esPro ? 'card-cursolandia-pro' : 'card-cursolandia' ?>">
        
        <!-- Cabecera de color con ícono -->
        <div class="d-flex align-items-center justify-content-center position-relative" 
             style="height: 135px; background-color: <?= $bgBanner ?>; color: <?= $colorIcono ?>;">
            
            <?php if ($esPro): ?>
                <span class="badge badge-pro position-absolute top-0 end-0 m-2 rounded-pill">
                    ★ Destacado PRO
                </span>
            <?php endif; ?>

            <i class="<?= $icono ?> fa-3x"></i>
            
        </div>

        <!-- Cuerpo de la tarjeta -->
        <div class="card-body d-flex flex-column p-4 bg-white">
            
            <div class="text-olive small fw-semibold mb-1">
                <?php if (!empty($curso['etiquetas'])): ?>
                    <?= htmlspecialchars(str_replace(', ', ' · ', $curso['etiquetas'])) ?>
                <?php else: ?>
                    Curso general
                <?php endif; ?>
                · <span class="text-muted fw-normal">Por <?= htmlspecialchars($curso['creador_nombre']) ?></span>
            </div>

            <h5 class="card-title fw-bold text-evergreen mb-2">
                <?= htmlspecialchars($curso['titulo']) ?>
            </h5>

            <p class="card-text text-secondary small mb-4 flex-grow-1">
                <?= htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 95, '...')) ?>
            </p>

            <div class="d-flex justify-content-between align-items-center mt-auto pt-2" style="border-top: 1px solid #AAD2BA;">
                <span class="fw-bold fs-6 text-evergreen">
                    <?php if ($curso['costo'] > 0): ?>
                        $ <?= number_format($curso['costo'], 0, ',', '.') ?>
                    <?php else: ?>
                        <span class="badge badge-gratis">Gratis</span>
                    <?php endif; ?>
                </span>

                <a href="index.php?action=detalle_curso&id=<?= $curso['id'] ?>" 
                   class="btn btn-sm btn-evergreen px-3 py-2 fw-semibold" 
                   style="border-radius: 10px;">
                    Ver curso
                </a>
            </div>

        </div>
    </div>
</div>