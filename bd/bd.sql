-- Crear base de datos
CREATE DATABASE trabajo_rd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trabajo_rd;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('candidato', 'empresa') NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de Candidatos (CV Digital)
CREATE TABLE candidatos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    formacion_academica JSON,  # {titulo: "", institucion: "", fecha: ""}
    experiencia_laboral JSON,   # {puesto: "", empresa: "", fecha: ""}
    habilidades TEXT,
    idiomas VARCHAR(255),
    objetivo_profesional TEXT,
    logros TEXT,
    disponibilidad ENUM('inmediata', '15_dias', '1_mes'),
    linkedin VARCHAR(255),
    referencias TEXT,
    foto VARCHAR(255),
    cv_pdf VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de Empresas
CREATE TABLE empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nombre_empresa VARCHAR(255) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    sitio_web VARCHAR(255),
    descripcion TEXT,
    logo VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de Ofertas de Empleo
CREATE TABLE ofertas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    requisitos TEXT NOT NULL,
    tipo_empleo ENUM('tiempo_completo', 'medio_tiempo', 'freelance', 'practicas'),
    experiencia ENUM('sin_experiencia', '1_3_anios', '3_5_anios', '5_anios'),
    salario DECIMAL(10,2),
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATE,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de Postulaciones
CREATE TABLE postulaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    oferta_id INT NOT NULL,
    candidato_id INT NOT NULL,
    fecha_postulacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'revisado', 'aceptado', 'rechazado'),
    mensaje TEXT,
    FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Índices para optimización
CREATE INDEX idx_ofertas_titulo ON ofertas(titulo);
CREATE INDEX idx_empresas_nombre ON empresas(nombre_empresa);