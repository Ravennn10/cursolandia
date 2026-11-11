--
-- Base de datos: cursolandia
--

-- Configuración inicial
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-03:00";


-- * * * ROLES * * * 

-- Tabla de roles
CREATE TABLE roles (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de roles
INSERT INTO roles (nombre) VALUES ('usuario'), ('admin');  -- usuario ID = 1 , admin ID = 2


-- * * * PLANES * * * 

-- Tabla de planes
CREATE TABLE planes (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(20) NOT NULL,
    max_cursos_activos SMALLINT UNSIGNED NULL,       -- NULL = sin limite
    max_inscripciones_activas SMALLINT UNSIGNED NULL,
    max_archivos_por_curso SMALLINT UNSIGNED NULL,
    max_bytes_por_archivo INT UNSIGNED NULL,
    max_bytes_total_cursos INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_planes_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de planes
INSERT INTO planes (nombre, max_cursos_activos, max_inscripciones_activas, max_archivos_por_curso, max_bytes_por_archivo, max_bytes_total_cursos) 
VALUES ('gratuito', 3, 3, 5, 5242880, 10485760) , -- 5MB por archivo - 10MB en total   -- ID = 1
('pro', NULL, NULL, NULL, NULL, NULL);  -- valores NULL = no tiene limite              -- ID = 2


-- * * * USUARIOS * * *

-- Tabla de usuarios
CREATE TABLE usuarios(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    plan_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    nombre VARCHAR(100) NOT NULL,                      
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    antecedentes TEXT NULL,
    imagen_perfil VARCHAR(255),  -- ruta de la imagen
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    plan_vence_en DATE NULL,  -- solo para plan 'pro'
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_rol (rol_id),
    KEY idx_usuarios_plan (plan_id),

    -- FK a rol y plan
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_plan FOREIGN KEY (plan_id) REFERENCES planes(id) ON UPDATE CASCADE ON DELETE RESTRICT

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de usuarios

-- usuario gratuito
INSERT INTO usuarios (rol_id, plan_id, nombre, apellido, email, password_hash) 
VALUES (1, 1, 'ABRIL', 'PAEZ CUELLO', 'abrilcuello31@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- usuario pro
INSERT INTO usuarios (rol_id, plan_id, nombre, apellido, email, password_hash, plan_vence_en) 
VALUES (1, 2, 'ABRIL', 'PRO', 'abrilHOLA@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-12-31');

INSERT INTO usuarios (rol_id, nombre, apellido, email, password_hash) 
VALUES (2, 'ADMIN', 'SYSTEM', 'admin1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' = '12345' con hash de bcrypt


-- * * * ETIQUETAS * * * 

-- Tabla de etiquetas
CREATE TABLE etiquetas(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_etiquetas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de etiquetas
INSERT INTO etiquetas (nombre) VALUES ('Programacion'), ('Diseño');


-- * * * USUARIO_INTERESES * * * (usuarios N:M etiquetas)

-- Tabla de usuarios_intereses
CREATE TABLE usuarios_intereses(
    usuario_id INT UNSIGNED NOT NULL,
    etiqueta_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (usuario_id, etiqueta_id),
    KEY idx_ui_etiqueta(etiqueta_id),
    -- FK a usuarios y etiquetas
    CONSTRAINT fk_ui_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ui_etiqueta FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de usuarios_intereses


-- * * * SOLICITUDES_PRO * * * (aviso de pago con comprobante adjunto)

-- Tabla de solicitudes_pro
CREATE TABLE solicitudes_pro (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    mensaje TEXT NOT NULL,
    adjunto_ruta VARCHAR(255) NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') NOT NULL DEFAULT 'pendiente',
    admin_id INT UNSIGNED NULL,   -- id del admin que atendió la solicitud
    fecha_vencimiento DATE NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sp_usuario (usuario_id),
    KEY idx_sp_estado (estado),
    KEY idx_sp_admin (admin_id),
    -- FK a usuarios
    CONSTRAINT fk_sp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_sp_admin FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de solicitudes_pro


-- * * * CURSOS * * * 

-- Tabla de cursos
CREATE TABLE cursos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    creador_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- 0 = gratuito
    cupo SMALLINT UNSIGNED NULL,                -- NULL= sin cupo(no hay limite)
    es_publico TINYINT(1) NOT NULL DEFAULT 1,   -- 1 = publico   0 = privado
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cursos_creador (creador_id),
    KEY idx_cursos_titulo (titulo),
    KEY idx_cursos_fechas (fecha_inicio, fecha_fin),
    -- FK a usuarios
    CONSTRAINT fk_cursos_creador FOREIGN KEY (creador_id) REFERENCES usuarios(id),
    CONSTRAINT ck_cursos_costo CHECK (costo >= 0),
    CONSTRAINT ck_cursos_cupo CHECK (cupo IS NULL or cupo > 0),
    CONSTRAINT ck_cursos_publico CHECK (es_publico IN (0, 1)),
    CONSTRAINT ck_cursos_fechas CHECK (fecha_fin >= fecha_inicio)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de cursos


-- * * * CURSO_ETIQUTAS * * * (cursos N:M etiquetas)

-- Tabal de curso_etiquetas
CREATE TABLE curso_etiquetas (
    curso_id INT UNSIGNED NOT NULL,
    etiqueta_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (curso_id, etiqueta_id),
    KEY idx_ce_etiqueta (etiqueta_id),
    -- FK a cursos y etiquetas
    CONSTRAINT fk_ce_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_ce_etiqueta FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de curso_etiqueta


-- * * * CURSO_ARCHIVOS * * *

-- Tabla de curso_archivos
CREATE TABLE curso_archivos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id INT UNSIGNED NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    peso_bytes INT UNSIGNED NOT NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ca_curso (curso_id),
    -- FK a cursos
    CONSTRAINT fk_ca_curso FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE CASCADE,
    CONSTRAINT ck_ca_peso CHECK (peso_bytes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de curso_archivos


-- * * * INSCRIPCIONES * * *

-- Tabla de inscripciones
CREATE TABLE inscripciones (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'confirmada', -- cuando sea pago PHP la crea con 'pendiente'
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inscripciones_curso_usuario (curso_id, usuario_id),  -- no se anota 2 veces
    KEY idx_inscripcion_usuario (usuario_id),
    -- FK a cursos y usuarios
    CONSTRAINT fk_inscripciones_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_inscripciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de inscripciones


-- * * * PREGUNTAS * * *

-- Tabla de preguntas
CREATE TABLE preguntas ( 
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id INT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    destacada TINYINT(1) NOT NULL DEFAULT 0,  -- 0 = no destacada  1 = destacada
    respuesta_aceptada_id INT UNSIGNED NULL,  -- si no es NULL, la pregunta está cerrada
    fecha_creada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_preg_curso (curso_id),
    KEY idx_preg_autor (autor_id),
    -- FK a cursos y usuarios
    CONSTRAINT fk_preg_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_preg_autor FOREIGN KEY (autor_id) REFERENCES usuarios(id),
    CONSTRAINT ck_preg_destacada CHECK (destacada IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de preguntas


-- * * * RESPUESTAS * * *

-- Tabla de respuestas
CREATE TABLE respuestas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pregunta_id INT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    contenido TEXT NOT NULL,
    es_aceptada TINYINT(1) NOT NULL DEFAULT 0,  -- 0 = no aceptada  1 = aceptada 
    fecha_creada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_editada DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_resp_pregunta (pregunta_id),
    KEY idx_resp_autor (autor_id),
    -- FK a preguntas y usuarios
    CONSTRAINT fk_resp_pregunta FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE,
    CONSTRAINT fk_resp_autor FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Se agrega acá(despues de respuestas) porque preguntas y respuestas se referencian entre sí
-- agregamos la fk en preguntas de respuesta_aceptada_id referenciada a respuestas (id)
ALTER TABLE preguntas ADD CONSTRAINT fk_preg_resp_aceptada FOREIGN KEY (respuesta_aceptada_id) 
REFERENCES respuestas (id) ON DELETE SET NULL;

-- INSERT de respuestas


-- * * * NOTIFICACIONES * * *

-- Tabla de notificaciones
CREATE TABLE notificaciones ( 
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,  -- quién la recibe
    actor_id INT UNSIGNED NULL,    -- quién hizo la acción
    curso_id INT UNSIGNED NOT NULL,
    pregunta_id INT UNSIGNED NULL,
    tipo ENUM('pregunta_nueva', 'respuesta_nueva', 'pregunta_cerrada', 'pregunta_eliminada') NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,  -- 0 = no leida  1 = leida
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    KEY idx_notif_usuario(usuario_id, leida, fecha),
    KEY idx_notif_actor(actor_id),
    KEY idx_notif_curso(curso_id),
    KEY idx_notif_pregunta(pregunta_id),
    -- FK a usuarios, cursos y preguntas
    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_actor FOREIGN KEY (actor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_notif_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_pregunta FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE SET NULL,
    CONSTRAINT ck_notif_leida CHECK (leida IN (0, 1))

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT de notificaciones



