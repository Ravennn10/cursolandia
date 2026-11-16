
# Cursolandia

Plataforma web de gestión y publicación de cursos online desarrollada como TPI. Permite a los usuarios publicar cursos gratuitos o pagos, inscribirse, descargar material de estudio, participar en un foro de preguntas y respuestas con notificaciones, y solicitar suscripción al plan PRO.

 https://cursolandia.infinityfreeapp.com/

---

## Tecnologías utilizadas

* **Backend:** PHP 8.2.12 (arquitectura MVC con mysqli procedural y consultas preparadas)
* **Base de datos:** MySQL / MariaDB (motor InnoDB, cotejamiento utf8mb4_unicode_ci)
* **Frontend:** HTML5, CSS3 y Bootstrap 5

---

## Funcionalidades principales

* **Gestión de usuarios y perfiles:** Registro, inicio de sesión, edición de perfil, carga de foto de perfil y selección de etiquetas de interés para recomendaciones.
* **Catálogo y cursos:** Creación, edición y eliminación de cursos, control de cupos, visibilidad pública/privada y filtrado por título o etiquetas.
* **Material de estudio:** Subida y descarga protegida de archivos asociada a cada curso según los límites del plan del creador.
* **Foro y notificaciones:** Publicación de preguntas y respuestas por curso, marcado de preguntas destacadas, aceptación de respuestas (cierre de pregunta) y notificaciones en tiempo real.
* **Planes y administración:** Diferenciación entre plan Gratuito y plan PRO, envío de comprobantes de pago y panel de administración para aprobar o rechazar solicitudes.

---

## Estructura del proyecto

```text
cursolandia/
├── app/
│   ├── controllers/     # Lógica de negocio y control de permisos
│   ├── models/          # Consultas SQL preparadas y acceso a datos
│   └── views/           # Vistas HTML/PHP organizadas por módulo
├── config/
│   ├── conexion.php     # Conexión a MySQL mediante mysqli
│   └── config.example.php # Plantilla de credenciales de base de datos
├── public/
│   ├── css/             # Estilos personalizados
│   ├── uploads/         # Imágenes de perfil de usuarios
│   └── index.php        # Punto de entrada y enrutador principal
├── storage/
│   ├── material/        # Archivos PDF subidos a los cursos
│   └── pagos/           # Comprobantes adjuntos para solicitudes PRO
└── cursolandia.sql      # Estructura y datos iniciales de la base de datos
```


## Instalación en entorno local (XAMPP)

* **1. Clonar o descargar el repositorio dentro de la carpeta htdocs de XAMPP**
* **2. Iniciar Apache y MySQL desde el panel de XAMPP**
* **3. Importar Base de Datos:**
    * Abrir http://localhost/phpmyadmin 
    * Ir a la pestaña de **Importar**, seleccionar el archivo cursolandia.sql en la carpeta raíz y ejecutar la importación.

* **4. Configurar credenciales:**
    * Duplicar o renombrar `config/config_example.php` a `config/config.php` (los datos locales ya se encuentran escritos dentro del archivo).

* **5. Abril el sistema en el navegador:**
    * http://localhost/cursolandia/public/index.php


## Credenciales de prueba

| Rol / Plan | Email | Contraseña |
| :--- | :--- | :--- |
| **ADMIN**(único que gestiona solicitudes pro) | `admin1@gmail.com` | `12345` | 
| **Usuario PRO** | `laurapro@cursolandia.test` | `12345` |
| **Usuario Gratuito** | `martin@cursolandia.test` | `123456` |
| **Usuario PRO** | `sofia@cursolandia.test` | `12345` |
| **Usuario Gratuito** | `abril@gmail.com` | `123` |

