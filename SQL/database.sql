CREATE DATABASE IF NOT EXISTS perriatra;
USE perriatra;

-- USUARIOS (LOGIN)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- VETERINARIOS
CREATE TABLE IF NOT EXISTS veterinarios (
    id_veterinario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(75) NOT NULL,
    telefono VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    especialidad VARCHAR(100) NOT NULL,
    salario DECIMAL(10,2) NOT NULL
);

-- PROPIETARIOS
CREATE TABLE IF NOT EXISTS propietarios (
    id_propietario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(75) NOT NULL,
    telefono VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    DNI VARCHAR(9) NOT NULL UNIQUE
);

-- RAZAS
CREATE TABLE IF NOT EXISTS razas (
    id_raza INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(75) NOT NULL,
    caracteristicas_fisicas TEXT NOT NULL,
    comportamiento TEXT NOT NULL
);

-- MASCOTAS
CREATE TABLE IF NOT EXISTS mascotas (
    id_mascota INT AUTO_INCREMENT PRIMARY KEY,
    chip VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(75) NOT NULL,
    sexo ENUM('Macho','Hembra') NOT NULL,
    especie ENUM('Perro','Gato','Ave','Reptil','Otro') NOT NULL
    fecha_nacimiento  NOT NULL,
    id_raza INT NOT NULL,
    id_propietario INT NOT NULL,
    id_veterinario INT NOT NULL,

    FOREIGN KEY (id_raza) REFERENCES razas(id_raza),
    FOREIGN KEY (id_propietario) REFERENCES propietarios(id_propietario),
    FOREIGN KEY (id_veterinario) REFERENCES veterinarios(id_veterinario)
);