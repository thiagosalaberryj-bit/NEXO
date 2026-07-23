# NEXO

**Plataforma de Historias Interactivas** — Escuela Secundaria Técnica N°1 de Vicente López

NEXO es una herramienta web para que estudiantes y docentes compartan, exploren y colaboren en historias interactivas creadas con Twine. Funciona como un repositorio social: subís tu historia HTML, la comunidad la ve, le da like, comenta y completa formularios integrados.

## Captura rápida

- **Estudiantes**: suben sus trabajos interactivos, reciben feedback
- **Profesores**: crean formularios de evaluación vinculados a historias
- **Comunidad**: explora, comenta y colabora en proyectos compartidos

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Backend | PHP 8+ (MySQLi, programación funcional) |
| Base de datos | MySQL / MariaDB |
| Servidor | Apache / Nginx (recomendado Laragon en desarrollo) |

## Estructura del proyecto

```
NEXO/
├── api/                  ← Backend (API REST, cada archivo independiente)
│   ├── config/
│   │   ├── database.php     Conexión a la BD
│   │   └── helpers.php      Funciones compartidas (respuestas JSON, validación)
│   ├── auth/                Autenticación (login, register, logout)
│   ├── stories/             CRUD de historias, likes, comentarios, vistas
│   ├── profile/             Perfil de usuario, estadísticas
│   └── invitations/         Invitaciones a colaboradores
├── frontend/             ← Páginas PHP (incluyen navbar, renderizan HTML)
│   ├── explorar.php
│   ├── perfil.php
│   └── subir-historia.php
├── components/           ← Recursos compartidos (navbar, imágenes)
├── css/                  ← Estilos
├── js/                   ← JavaScript del frontend
├── uploads/              ← Archivos subidos por usuarios (portadas, historias HTML)
└── index.html            ← Landing page
```

## Instalación rápida

1. Clonar el repositorio en el root del servidor web
2. Importar `api/config/schema.sql` a MySQL / MariaDB
3. Configurar credenciales en `api/config/database.php`
4. Asegurar que `uploads/` tenga permisos de escritura
5. Acceder vía navegador a `http://localhost/NEXO`

