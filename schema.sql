-- Esquema de la base de datos del Vale de Suministros (MySQL).
-- Se ejecuta con: php init_db.php
-- (esto borra y vuelve a crear las tablas; la base de datos en sí la crea
-- init_db.php automáticamente si todavía no existe)

DROP TABLE IF EXISTS solicitudes;
DROP TABLE IF EXISTS trabajadores;
DROP TABLE IF EXISTS materiales;

CREATE TABLE materiales (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    clave       VARCHAR(50) UNIQUE NOT NULL,
    nombre      VARCHAR(120) NOT NULL,
    categoria   ENUM('oficina', 'limpieza') NOT NULL,
    unidad      VARCHAR(20) NOT NULL,
    stock       INT NOT NULL DEFAULT 0,
    stock_min   INT NOT NULL DEFAULT 0,
    stock_max   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE trabajadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    area VARCHAR(100) NOT NULL,
    puesto VARCHAR(100) NOT NULL,
    status ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE solicitudes (
    folio             INT AUTO_INCREMENT PRIMARY KEY,
    nombre            VARCHAR(150) NOT NULL,
    area              VARCHAR(100) NOT NULL,
    material_id       INT NOT NULL,
    cantidad          INT NOT NULL,
    urgencia          ENUM('normal', 'urgente') NOT NULL,
    nota              TEXT NULL,
    fecha_creacion    DATETIME NOT NULL,
    status            ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    fecha_resolucion  DATETIME NULL,
    CONSTRAINT fk_solicitudes_material FOREIGN KEY (material_id) REFERENCES materiales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_solicitudes_status ON solicitudes(status);
CREATE INDEX idx_solicitudes_nombre ON solicitudes(nombre);
CREATE INDEX idx_trabajadores_status ON trabajadores(status);
