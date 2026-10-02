CREATE DATABASE IF NOT EXISTS `tarea#1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tarea#1`;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profile (
    id TINYINT UNSIGNED PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    tagline VARCHAR(220) NOT NULL,
    short_bio TEXT NOT NULL,
    about_text TEXT NOT NULL,
    interests TEXT NOT NULL,
    skills TEXT NOT NULL,
    experience_text TEXT NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(60) NOT NULL DEFAULT '',
    social_github VARCHAR(255) NOT NULL DEFAULT '',
    social_linkedin VARCHAR(255) NOT NULL DEFAULT '',
    social_instagram VARCHAR(255) NOT NULL DEFAULT '',
    photo_path VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO profile (id, full_name, tagline, short_bio, about_text, interests, skills, experience_text, email)
VALUES (1, 'Tu nombre completo', 'Estudiante · Creador de ideas digitales',
        'Este es mi espacio para compartir lo que aprendo, creo y descubro.',
        'Escribe aquí una descripción personal más amplia: tu historia, metas y motivaciones.',
        'Diseño, tecnología y aprendizaje continuo', 'HTML, CSS, PHP y MySQL',
        'Agrega aquí tu experiencia o conocimientos actuales.', 'tu-correo@ejemplo.com')
ON DUPLICATE KEY UPDATE id = id;

CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    institution VARCHAR(180) NOT NULL,
    year SMALLINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    excerpt VARCHAR(350) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_publication (is_published, created_at)
) ENGINE=InnoDB;
