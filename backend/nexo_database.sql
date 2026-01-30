-- Base de datos para NEXO - Plataforma de Historias Interactivas
-- Escuela Secundaria Técnica N°1 de Vicente López

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS nexo_escuela;
USE nexo_escuela;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('estudiante', 'profesor', 'admin') NOT NULL DEFAULT 'estudiante',
    curso VARCHAR(50), -- Solo para estudiantes
    division VARCHAR(10), -- Solo para estudiantes
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de historias
CREATE TABLE historias (
    id_historia INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    id_autor INT NOT NULL,
    portada VARCHAR(255) NOT NULL, -- Ruta a la imagen de portada (en raíz de carpeta)
    archivo_twine VARCHAR(255) NOT NULL, -- Ruta al archivo HTML de Twine (en raíz de carpeta)
    carpeta_contenido VARCHAR(255), -- Carpeta opcional para assets multimedia (/contenidos/)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('borrador', 'publicada') DEFAULT 'borrador',
    FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de visualizaciones
CREATE TABLE visualizaciones (
    id_visualizacion INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_visualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_visualizacion (id_historia, id_usuario),
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de likes
CREATE TABLE likes (
    id_like INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_like TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (id_historia, id_usuario),
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de comentarios
CREATE TABLE comentarios (
    id_comentario INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT NOT NULL,
    id_usuario INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de formularios
CREATE TABLE formularios (
    id_formulario INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_formulario_historia (id_historia),
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia) ON DELETE CASCADE
);

-- Tabla de preguntas del formulario
CREATE TABLE preguntas_formulario (
    id_pregunta INT PRIMARY KEY AUTO_INCREMENT,
    id_formulario INT NOT NULL,
    pregunta TEXT NOT NULL,
    tipo_pregunta ENUM('texto', 'multiple_opcion', 'verdadero_falso', 'escala') DEFAULT 'texto',
    orden INT NOT NULL,
    obligatoria BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_formulario) REFERENCES formularios(id_formulario) ON DELETE CASCADE
);

-- Tabla de opciones de respuesta para preguntas de múltiple opción
CREATE TABLE opciones_pregunta (
    id_opcion INT PRIMARY KEY AUTO_INCREMENT,
    id_pregunta INT NOT NULL,
    opcion TEXT NOT NULL,
    orden INT NOT NULL,
    FOREIGN KEY (id_pregunta) REFERENCES preguntas_formulario(id_pregunta) ON DELETE CASCADE
);

-- Tabla de respuestas de formularios completados
CREATE TABLE respuestas_formulario (
    id_respuesta INT PRIMARY KEY AUTO_INCREMENT,
    id_pregunta INT NOT NULL,
    id_usuario INT NOT NULL,
    respuesta_texto TEXT, -- Para preguntas de texto
    id_opcion_seleccionada INT, -- Para preguntas de múltiple opción
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pregunta) REFERENCES preguntas_formulario(id_pregunta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_opcion_seleccionada) REFERENCES opciones_pregunta(id_opcion) ON DELETE SET NULL
);

-- Tabla de contenido de historias (archivos asociados)
CREATE TABLE contenido_historia (
    id_contenido INT PRIMARY KEY AUTO_INCREMENT,
    id_historia INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_archivo ENUM('imagen', 'audio', 'video', 'documento', 'otro') DEFAULT 'otro',
    extension VARCHAR(10),
    tamano_bytes INT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_historia) REFERENCES historias(id_historia) ON DELETE CASCADE
);
CREATE INDEX idx_visualizacion_historia ON visualizaciones(id_historia);
CREATE INDEX idx_visualizacion_usuario ON visualizaciones(id_usuario);
CREATE INDEX idx_like_historia ON likes(id_historia);
CREATE INDEX idx_like_usuario ON likes(id_usuario);
CREATE INDEX idx_comentario_historia ON comentarios(id_historia);
CREATE INDEX idx_comentario_usuario ON comentarios(id_usuario);
CREATE INDEX idx_formulario_historia ON formularios(id_historia);
CREATE INDEX idx_pregunta_formulario ON preguntas_formulario(id_formulario);
CREATE INDEX idx_respuesta_pregunta ON respuestas_formulario(id_pregunta);
CREATE INDEX idx_respuesta_usuario ON respuestas_formulario(id_usuario);

-- Datos de ejemplo para testing
INSERT INTO usuarios (nombre, apellido, email, password_hash, tipo_usuario, curso, division) VALUES
('Juan', 'Pérez', 'juan.perez@est1vl.edu.ar', '$2y$10$example.hash.here', 'estudiante', '6to', 'A'),
('María', 'González', 'maria.gonzalez@est1vl.edu.ar', '$2y$10$example.hash.here', 'profesor', NULL, NULL),
('Admin', 'Sistema', 'admin@est1vl.edu.ar', '$2y$10$example.hash.here', 'admin', NULL, NULL);

INSERT INTO historias (titulo, descripcion, id_autor, estado, visible) VALUES
('La Elección del Programador', 'Una historia interactiva sobre decisiones en el mundo de la programación', 1, 'publicada', TRUE),
('El Misterio del Código Perdido', 'Aventura detectivesca en el mundo del desarrollo de software', 1, 'publicada', TRUE);