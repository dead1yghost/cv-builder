-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CVs table
CREATE TABLE IF NOT EXISTS `cvs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'Yeni CV',
    `theme_color` VARCHAR(20) DEFAULT 'teal',
    `show_photo` TINYINT(1) DEFAULT 0,
    `photo_path` VARCHAR(255),
    `ats_score` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal info table
CREATE TABLE IF NOT EXISTS `cv_personal_info` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL UNIQUE,
    `full_name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255),
    `email` VARCHAR(255),
    `phone` VARCHAR(50),
    `address` VARCHAR(500),
    `city` VARCHAR(100),
    `birth_year` INT,
    `linkedin` VARCHAR(255),
    `website` VARCHAR(255),
    `github` VARCHAR(255),
    `summary` TEXT,
    `gender` VARCHAR(50),
    `nationality` VARCHAR(100),
    `military_status` VARCHAR(100),
    `driving_license` VARCHAR(50),
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Experience table
CREATE TABLE IF NOT EXISTS `cv_experience` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `job_title` VARCHAR(255) NOT NULL,
    `company` VARCHAR(255) NOT NULL,
    `location` VARCHAR(255),
    `start_date` DATE,
    `end_date` DATE,
    `is_current` TINYINT(1) DEFAULT 0,
    `description` TEXT,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Education table
CREATE TABLE IF NOT EXISTS `cv_education` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `school` VARCHAR(255) NOT NULL,
    `degree` VARCHAR(255),
    `field_of_study` VARCHAR(255),
    `start_date` DATE,
    `end_date` DATE,
    `is_current` TINYINT(1) DEFAULT 0,
    `grade` VARCHAR(50),
    `description` TEXT,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills table
CREATE TABLE IF NOT EXISTS `cv_skills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `category` VARCHAR(100),
    `skill_name` VARCHAR(255) NOT NULL,
    `proficiency` VARCHAR(50) DEFAULT 'intermediate',
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Languages table
CREATE TABLE IF NOT EXISTS `cv_languages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `language_name` VARCHAR(100) NOT NULL,
    `proficiency` VARCHAR(50) DEFAULT 'intermediate',
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE IF NOT EXISTS `cv_projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `project_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `technologies` VARCHAR(500),
    `url` VARCHAR(255),
    `start_date` DATE,
    `end_date` DATE,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certifications table
CREATE TABLE IF NOT EXISTS `cv_certifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `issuing_organization` VARCHAR(255),
    `issue_date` DATE,
    `credential_url` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- References table
CREATE TABLE IF NOT EXISTS `cv_references` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255),
    `company` VARCHAR(255),
    `email` VARCHAR(255),
    `phone` VARCHAR(50),
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Interests table
CREATE TABLE IF NOT EXISTS `cv_interests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cv_id` INT NOT NULL,
    `interest` VARCHAR(255) NOT NULL,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`cv_id`) REFERENCES `cvs`(`id`) ON DELETE CASCADE,
    INDEX `idx_cv_id` (`cv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ATS Scans table
CREATE TABLE IF NOT EXISTS `ats_scans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `score` INT DEFAULT 0,
    `analysis_json` TEXT,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
