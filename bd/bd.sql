-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS plataforma_empleos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE plataforma_empleos;

-- Tabla de usuarios (para ambos roles)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('candidato', 'empresa') NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de candidatos (extiende usuarios)
CREATE TABLE candidatos (
    usuario_id INT PRIMARY KEY,
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    foto_perfil VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de empresas (extiende usuarios)
CREATE TABLE empresas (
    usuario_id INT PRIMARY KEY,
    nombre_empresa VARCHAR(100) NOT NULL,
    descripcion TEXT,
    direccion VARCHAR(200),
    telefono VARCHAR(20),
    sitio_web VARCHAR(100),
    logo VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de CVs
CREATE TABLE cvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidato_id INT NOT NULL,
    objetivo_profesional TEXT,
    habilidades_clave TEXT,
    disponibilidad VARCHAR(50),
    linkedin VARCHAR(100),
    github VARCHAR(100),
    otras_redes TEXT,
    referencias TEXT,
    cv_pdf VARCHAR(255),
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(usuario_id) ON DELETE CASCADE
);

-- Tabla de formación académica
CREATE TABLE formacion_academica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    institucion VARCHAR(100) NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    descripcion TEXT,
    FOREIGN KEY (cv_id) REFERENCES cvs(id) ON DELETE CASCADE
);

-- Tabla de experiencia laboral
CREATE TABLE experiencia_laboral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    empresa VARCHAR(100) NOT NULL,
    puesto VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    descripcion TEXT,
    FOREIGN KEY (cv_id) REFERENCES cvs(id) ON DELETE CASCADE
);

-- Tabla de idiomas
CREATE TABLE idiomas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    idioma VARCHAR(50) NOT NULL,
    nivel VARCHAR(50) NOT NULL,
    FOREIGN KEY (cv_id) REFERENCES cvs(id) ON DELETE CASCADE
);

-- Tabla de proyectos/logros
CREATE TABLE proyectos_logros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATE,
    enlace VARCHAR(255),
    FOREIGN KEY (cv_id) REFERENCES cvs(id) ON DELETE CASCADE
);

-- Tabla de ofertas de empleo
CREATE TABLE ofertas_empleo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    requisitos TEXT NOT NULL,
    ubicacion VARCHAR(100),
    salario VARCHAR(50),
    tipo_contrato VARCHAR(50),
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATE,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(usuario_id) ON DELETE CASCADE
);

-- Tabla de aplicaciones a ofertas
CREATE TABLE aplicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    oferta_id INT NOT NULL,
    candidato_id INT NOT NULL,
    cv_id INT NOT NULL,
    fecha_aplicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    mensaje TEXT,
    estado ENUM('pendiente', 'revisado', 'seleccionado', 'rechazado') DEFAULT 'pendiente',
    FOREIGN KEY (oferta_id) REFERENCES ofertas_empleo(id) ON DELETE CASCADE,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (cv_id) REFERENCES cvs(id) ON DELETE CASCADE,
    UNIQUE KEY (oferta_id, candidato_id) -- Evitar aplicaciones duplicadas
);