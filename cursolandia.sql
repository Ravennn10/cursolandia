-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 04:04:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cursolandia`
--

CREATE DATABASE IF NOT EXISTS `cursolandia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cursolandia`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(10) UNSIGNED NOT NULL,
  `creador_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `costo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cupo` smallint(5) UNSIGNED DEFAULT NULL,
  `es_publico` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `creador_id`, `titulo`, `descripcion`, `costo`, `cupo`, `es_publico`, `fecha_inicio`, `fecha_fin`, `fecha_creacion`) VALUES
(1, 11, 'Introducción a PHP y MySQL', 'Aprendé a crear aplicaciones web dinámicas con PHP, MySQL y Bootstrap desde cero.', 0.00, 30, 1, '2026-10-01', '2027-03-31', '2026-11-11 16:14:46'),
(2, 11, 'Diseño de interfaces con Bootstrap', 'Curso práctico para maquetar sitios responsivos usando Bootstrap 5.', 15000.00, 20, 1, '2026-11-01', '2027-02-28', '2026-11-11 16:14:46'),
(3, 3, 'Fotografía digital desde cero', 'Conceptos básicos de cámara, composición y edición. Curso privado por invitación.', 0.00, NULL, 0, '2026-11-01', '2027-01-31', '2026-11-11 16:14:46'),
(4, 3, 'Inglés para programadores', 'Vocabulario técnico y lectura de documentación en inglés.', 0.00, 15, 1, '2026-11-01', '2027-04-30', '2026-11-11 16:14:46'),
(5, 4, 'Marketing digital para emprendedores', 'Estrategias de redes sociales y publicidad online para pequeños negocios.', 8000.00, 10, 1, '2026-12-01', '2027-03-01', '2026-11-11 16:14:46'),
(6, 5, 'Excel básico', 'Fórmulas, tablas dinámicas y gráficos para el trabajo diario.', 0.00, NULL, 1, '2026-03-01', '2026-06-30', '2026-11-11 16:14:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_archivos`
--

CREATE TABLE `curso_archivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `curso_id` int(10) UNSIGNED NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `peso_bytes` int(10) UNSIGNED NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `curso_archivos`
--

INSERT INTO `curso_archivos` (`id`, `curso_id`, `nombre_archivo`, `ruta_archivo`, `peso_bytes`, `fecha_subida`) VALUES
(1, 1, 'clase1-introduccion.pdf', 'storage/material/1/clase1-introduccion.pdf', 1048576, '2026-11-11 16:19:03'),
(2, 1, 'ejercicios-sql.pdf', 'storage/material/1/ejercicios-sql.pdf', 524288, '2026-11-11 16:19:03'),
(3, 2, 'guia-bootstrap.pdf', 'storage/material/2/guia-bootstrap.pdf', 2097152, '2026-11-11 16:19:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_etiquetas`
--

CREATE TABLE `curso_etiquetas` (
  `curso_id` int(10) UNSIGNED NOT NULL,
  `etiqueta_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `curso_etiquetas`
--

INSERT INTO `curso_etiquetas` (`curso_id`, `etiqueta_id`) VALUES
(1, 1),
(1, 5),
(2, 2),
(3, 2),
(3, 3),
(4, 1),
(4, 4),
(5, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id`, `nombre`) VALUES
(5, 'Bases de Datos'),
(2, 'Diseño'),
(4, 'Idiomas'),
(3, 'Marketing'),
(1, 'Programacion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `curso_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `estado` enum('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'confirmada',
  `fecha_inscripcion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `curso_id`, `usuario_id`, `estado`, `fecha_inscripcion`) VALUES
(1, 1, 3, 'confirmada', '2026-11-11 16:22:34'),
(2, 1, 4, 'confirmada', '2026-11-11 16:22:34'),
(3, 1, 5, 'confirmada', '2026-11-11 16:22:34'),
(5, 2, 5, 'confirmada', '2026-11-11 16:22:34'),
(6, 3, 3, 'confirmada', '2026-11-11 16:22:34'),
(8, 4, 4, 'confirmada', '2026-11-11 16:22:34'),
(14, 2, 4, 'rechazada', '2026-11-15 05:29:42'),
(15, 4, 11, 'confirmada', '2026-11-16 00:18:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `curso_id` int(10) UNSIGNED NOT NULL,
  `pregunta_id` int(10) UNSIGNED DEFAULT NULL,
  `tipo` enum('pregunta_nueva','respuesta_nueva','pregunta_cerrada','pregunta_eliminada') NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `actor_id`, `curso_id`, `pregunta_id`, `tipo`, `mensaje`, `leida`, `fecha`) VALUES
(1, 11, 4, 1, 1, 'pregunta_nueva', 'Martín Rojas hizo una pregunta nueva en \"Introducción a PHP y MySQL\".', 1, '2026-11-11 16:27:55'),
(2, 3, 4, 1, 1, 'pregunta_nueva', 'Martín Rojas hizo una pregunta nueva en \"Introducción a PHP y MySQL\".', 0, '2026-11-11 16:27:55'),
(3, 5, 4, 1, 1, 'pregunta_nueva', 'Martín Rojas hizo una pregunta nueva en \"Introducción a PHP y MySQL\".', 0, '2026-11-11 16:27:55'),
(4, 4, 1, 1, 1, 'respuesta_nueva', 'Tu pregunta \"¿Diferencia entre mysqli y PDO?\" tiene una respuesta nueva.', 1, '2026-11-11 16:27:55'),
(5, 4, 5, 1, 1, 'respuesta_nueva', 'Sofía Díaz respondió en \"¿Diferencia entre mysqli y PDO?\".', 0, '2026-11-11 16:27:55'),
(6, 11, 5, 1, 1, 'respuesta_nueva', 'Sofía Díaz respondió en \"¿Diferencia entre mysqli y PDO?\".', 1, '2026-11-11 16:27:55'),
(7, 4, 1, 1, 1, 'pregunta_cerrada', 'La pregunta \"¿Diferencia entre mysqli y PDO?\" fue cerrada con una respuesta aceptada.', 0, '2026-11-11 16:27:55'),
(8, 5, 1, 1, 1, 'pregunta_cerrada', 'La pregunta \"¿Diferencia entre mysqli y PDO?\" fue cerrada con una respuesta aceptada.', 0, '2026-11-11 16:27:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `max_cursos_activos` smallint(5) UNSIGNED DEFAULT NULL,
  `max_inscripciones_activas` smallint(5) UNSIGNED DEFAULT NULL,
  `max_archivos_por_curso` smallint(5) UNSIGNED DEFAULT NULL,
  `max_bytes_por_archivo` int(10) UNSIGNED DEFAULT NULL,
  `max_bytes_total_cursos` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre`, `max_cursos_activos`, `max_inscripciones_activas`, `max_archivos_por_curso`, `max_bytes_por_archivo`, `max_bytes_total_cursos`) VALUES
(1, 'gratuito', 3, 3, 5, 5242880, 10485760),
(2, 'pro', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(10) UNSIGNED NOT NULL,
  `curso_id` int(10) UNSIGNED NOT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `destacada` tinyint(1) NOT NULL DEFAULT 0,
  `respuesta_aceptada_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_creada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `curso_id`, `autor_id`, `titulo`, `contenido`, `destacada`, `respuesta_aceptada_id`, `fecha_creada`) VALUES
(1, 1, 4, '¿Diferencia entre mysqli y PDO?', '¿Cuál conviene usar en el trabajo integrador y por qué?', 0, 3, '2026-11-11 16:24:02'),
(2, 1, 5, '¿Cómo evito la inyección SQL?', 'Quiero asegurarme de que mi formulario de login sea seguro.', 1, NULL, '2026-11-11 16:24:02'),
(3, 2, 5, '¿Qué versión de Bootstrap usamos?', '¿Trabajamos con la versión 5 o con la 4?', 0, NULL, '2026-11-11 16:24:02'),
(4, 4, 11, '¿Las clases quedan grabadas?', 'Me gustaría poder repasar el material después de cada clase.', 0, NULL, '2026-11-11 16:24:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(10) UNSIGNED NOT NULL,
  `pregunta_id` int(10) UNSIGNED NOT NULL,
  `autor_id` int(10) UNSIGNED NOT NULL,
  `contenido` text NOT NULL,
  `fecha_creada` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_editada` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id`, `pregunta_id`, `autor_id`, `contenido`, `fecha_creada`, `fecha_editada`) VALUES
(1, 1, 11, 'Ambas sirven. Para esta materia te conviene mysqli procedural.', '2026-11-11 16:25:36', NULL),
(2, 1, 5, 'Yo también uso mysqli, es lo que vemos en clase.', '2026-11-11 16:25:36', NULL),
(3, 1, 11, 'Exacto, usá mysqli con consultas preparadas.', '2026-11-11 16:25:36', NULL),
(4, 2, 11, 'Usá siempre consultas preparadas con mysqli_prepare.', '2026-11-11 16:25:36', NULL),
(5, 2, 4, 'Y nunca concatenes variables dentro del SQL.', '2026-11-11 16:25:36', NULL),
(6, 3, 3, 'Usamos Bootstrap 5, la versión actual.', '2026-11-11 16:25:36', NULL),
(7, 4, 4, 'Sí, las grabaciones se suben al final de cada semana.', '2026-11-11 16:25:36', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(2, 'admin'),
(1, 'usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_pro`
--

CREATE TABLE `solicitudes_pro` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `mensaje` text NOT NULL,
  `ruta_comprobante` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_pro`
--

INSERT INTO `solicitudes_pro` (`id`, `usuario_id`, `mensaje`, `ruta_comprobante`, `estado`, `admin_id`, `fecha_vencimiento`, `fecha_solicitud`) VALUES
(7, 5, 'envio comprobante', 'storage/pagos/pago_6af969c399845_comprobanteSofia.png', 'aprobada', 1, '2026-12-15', '2026-11-15 05:13:55'),
(8, 4, 'anjunto comprobante de pago', 'storage/pagos/pago_6afa8a461de8b_comprobanteMartin.png', 'pendiente', NULL, NULL, '2026-11-16 01:44:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `rol_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `plan_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `antecedentes` text DEFAULT NULL,
  `imagen_perfil` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `plan_vence_en` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `rol_id`, `plan_id`, `nombre`, `apellido`, `email`, `password_hash`, `antecedentes`, `imagen_perfil`, `fecha_registro`, `plan_vence_en`) VALUES
(1, 2, 1, 'ADMIN', 'SYSTEM', 'admin1@gmail.com', '$2y$10$Xm98xp9aoZWjX3kOkysTAOfNLGzDnMBU8KjlKSnAm9DsLBVkNPP0i', '', 'uploads/perfil1_6af96c5815d3e.png', '2026-11-11 14:55:21', NULL),
(3, 1, 2, 'Laura', 'Gómez', 'laurapro@cursolandia.test', '$2y$10$Xm98xp9aoZWjX3kOkysTAOfNLGzDnMBU8KjlKSnAm9DsLBVkNPP0i', NULL, NULL, '2026-11-11 16:05:25', '2027-12-31'),
(4, 1, 1, 'Martín', 'Rojas', 'martin@cursolandia.test', '$2y$10$wqGVYzJztfFHF8gPQNV/degPaOwGQw3g1xrIepW7QphoDUpKlqtqG', NULL, NULL, '2026-11-11 16:05:25', NULL),
(5, 1, 2, 'Sofía', 'Díaz', 'sofia@cursolandia.test', '$2y$10$Xm98xp9aoZWjX3kOkysTAOfNLGzDnMBU8KjlKSnAm9DsLBVkNPP0i', '', 'uploads/perfil5_6afa8cfbac178.png', '2026-11-11 16:05:25', '2026-12-15'),
(11, 1, 1, 'Abril', 'Test', 'abril@gmail.com', '$2y$10$1ETiA5irFZbkfYzIE6LnwOgy1VrlAZTnlxvHx5hf.EAl/OEi8UpXO', NULL, NULL, '2026-11-12 23:11:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_intereses`
--

CREATE TABLE `usuarios_intereses` (
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `etiqueta_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_intereses`
--

INSERT INTO `usuarios_intereses` (`usuario_id`, `etiqueta_id`) VALUES
(3, 1),
(3, 3),
(4, 4),
(5, 2),
(5, 5);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cursos_creador` (`creador_id`),
  ADD KEY `idx_cursos_titulo` (`titulo`),
  ADD KEY `idx_cursos_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `curso_archivos`
--
ALTER TABLE `curso_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ca_curso` (`curso_id`);

--
-- Indices de la tabla `curso_etiquetas`
--
ALTER TABLE `curso_etiquetas`
  ADD PRIMARY KEY (`curso_id`,`etiqueta_id`),
  ADD KEY `idx_ce_etiqueta` (`etiqueta_id`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_etiquetas_nombre` (`nombre`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inscripciones_curso_usuario` (`curso_id`,`usuario_id`),
  ADD KEY `idx_inscripcion_usuario` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_usuario` (`usuario_id`,`leida`,`fecha`),
  ADD KEY `idx_notif_actor` (`actor_id`),
  ADD KEY `idx_notif_curso` (`curso_id`),
  ADD KEY `idx_notif_pregunta` (`pregunta_id`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_planes_nombre` (`nombre`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_preg_curso` (`curso_id`),
  ADD KEY `idx_preg_autor` (`autor_id`),
  ADD KEY `fk_preg_resp_aceptada` (`respuesta_aceptada_id`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resp_pregunta` (`pregunta_id`),
  ADD KEY `idx_resp_autor` (`autor_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre`);

--
-- Indices de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sp_usuario` (`usuario_id`),
  ADD KEY `idx_sp_estado` (`estado`),
  ADD KEY `idx_sp_admin` (`admin_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_rol` (`rol_id`),
  ADD KEY `idx_usuarios_plan` (`plan_id`);

--
-- Indices de la tabla `usuarios_intereses`
--
ALTER TABLE `usuarios_intereses`
  ADD PRIMARY KEY (`usuario_id`,`etiqueta_id`),
  ADD KEY `idx_ui_etiqueta` (`etiqueta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `curso_archivos`
--
ALTER TABLE `curso_archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_cursos_creador` FOREIGN KEY (`creador_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `curso_archivos`
--
ALTER TABLE `curso_archivos`
  ADD CONSTRAINT `fk_ca_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `curso_etiquetas`
--
ALTER TABLE `curso_etiquetas`
  ADD CONSTRAINT `fk_ce_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ce_etiqueta` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_inscripciones_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscripciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `fk_preg_autor` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_preg_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_preg_resp_aceptada` FOREIGN KEY (`respuesta_aceptada_id`) REFERENCES `respuestas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `fk_resp_autor` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_resp_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD CONSTRAINT `fk_sp_admin` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_plan` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_intereses`
--
ALTER TABLE `usuarios_intereses`
  ADD CONSTRAINT `fk_ui_etiqueta` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ui_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
