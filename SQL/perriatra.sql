CREATE DATABASE IF NOT EXISTS perriatra;
USE perriatra;

-- USUARIOS (LOGIN)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(50)  NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL
);

-- VETERINARIOS
CREATE TABLE IF NOT EXISTS veterinarios (
    id_veterinario INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(75)   NOT NULL,
    telefono       VARCHAR(15)   NOT NULL UNIQUE,
    email          VARCHAR(100)  NOT NULL UNIQUE,
    especialidad   VARCHAR(100)  NOT NULL,
    salario        DECIMAL(10,2) NOT NULL
);

-- PROPIETARIOS
CREATE TABLE IF NOT EXISTS propietarios (
    id_propietario INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(75)  NOT NULL,
    telefono       VARCHAR(15)  NOT NULL UNIQUE,
    email          VARCHAR(100) NOT NULL UNIQUE,
    DNI            VARCHAR(9)   NOT NULL UNIQUE
);

-- RAZAS
CREATE TABLE IF NOT EXISTS razas (
    id_raza                INT AUTO_INCREMENT PRIMARY KEY,
    nombre                 VARCHAR(75) NOT NULL,
    caracteristicas_fisicas TEXT        NOT NULL,
    comportamiento          TEXT        NOT NULL
);

-- MASCOTAS
CREATE TABLE IF NOT EXISTS mascotas (
    id_mascota       INT AUTO_INCREMENT PRIMARY KEY,
    chip             VARCHAR(50)                         NOT NULL UNIQUE,
    nombre           VARCHAR(75)                         NOT NULL,
    sexo             ENUM('Macho','Hembra')              NOT NULL,
    especie          ENUM('Perro','Gato','Ave','Reptil','Otro') NOT NULL,
    fecha_nacimiento DATE                                NOT NULL,
    id_raza          INT                                 NOT NULL,
    id_propietario   INT                                 NOT NULL,
    id_veterinario   INT                                 NOT NULL,
    FOREIGN KEY (id_raza)        REFERENCES razas(id_raza),
    FOREIGN KEY (id_propietario) REFERENCES propietarios(id_propietario),
    FOREIGN KEY (id_veterinario) REFERENCES veterinarios(id_veterinario)
);

USE perriatra;

CREATE TABLE IF NOT EXISTS citas (
    id_cita      INT AUTO_INCREMENT PRIMARY KEY,
    id_mascota   INT          NOT NULL,
    id_veterinario INT        NOT NULL,
    fecha        DATE         NOT NULL,
    hora         TIME         NOT NULL,
    tipo         ENUM('Consulta','Operación','Vacuna','Revisión') NOT NULL,
    motivo       VARCHAR(255) NOT NULL,
    observaciones TEXT,
    estado       ENUM('Pendiente','Realizada','Cancelada') NOT NULL DEFAULT 'Pendiente',
    FOREIGN KEY (id_mascota)    REFERENCES mascotas(id_mascota)       ON DELETE CASCADE,
    FOREIGN KEY (id_veterinario) REFERENCES veterinarios(id_veterinario) ON DELETE CASCADE
);

-- Citas de prueba
INSERT INTO citas (id_mascota, id_veterinario, fecha, hora, tipo, motivo, observaciones, estado) VALUES
(1, 1, CURDATE(), '10:00:00', 'Consulta',  'Revisión anual',         'Traer cartilla de vacunas',   'Pendiente'),
(2, 2, CURDATE(), '11:30:00', 'Vacuna',    'Vacuna antirrábica',     NULL,                          'Pendiente'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', 'Operación', 'Extracción de tumor benigno', 'Ayuno previo de 12 horas', 'Pendiente');

-- Usuario de prueba (contraseña: Admin1234)
INSERT INTO usuarios (nombre, email, password) VALUES
('Admin', 'admin@perriatra.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Veterinarios de prueba
INSERT INTO veterinarios (nombre, telefono, email, especialidad, salario) VALUES
('Dr. Carlos Fernández', '600111222', 'carlos@perriatra.com', 'Cirugía',      2800.00),
('Dra. Laura Pérez',     '600333444', 'laura@perriatra.com',  'Dermatología', 2600.00),
('Dr. Miguel Santos',    '600555666', 'miguel@perriatra.com', 'Oncología',    3000.00);

-- Propietarios de prueba
INSERT INTO propietarios (nombre, telefono, email, DNI) VALUES
('María García López', '612345678', 'maria@email.com', '12345678A'),
('Juan Martínez Ruiz',  '623456789', 'juan@email.com',  '23456789B'),
('Ana Torres Sánchez',  '634567890', 'ana@email.com',   '34567890C');

-- Razas de prueba
INSERT INTO razas (nombre, caracteristicas_fisicas, comportamiento) VALUES
('Labrador Retriever', 'Talla grande, pelaje corto y denso, colores negro, amarillo o chocolate', 'Amigable, activo, leal, excelente con niños'),
('Gato Persa',         'Pelaje largo y espeso, cara plana, ojos grandes y redondos',              'Tranquilo, cariñoso, independiente, poco activo'),
('Golden Retriever',   'Talla grande, pelaje dorado y denso, constitución atlética',              'Inteligente, confiable, amistoso, fácil de entrenar');

-- Mascotas de prueba
INSERT INTO mascotas (chip, nombre, sexo, especie, fecha_nacimiento, id_raza, id_propietario, id_veterinario) VALUES
('724098000000001', 'Max',   'Macho',  'Perro', '2020-03-15', 1, 1, 1),
('724098000000002', 'Luna',  'Hembra', 'Gato',  '2019-07-22', 2, 2, 2),
('724098000000003', 'Rocky', 'Macho',  'Perro', '2021-01-10', 3, 3, 3);
