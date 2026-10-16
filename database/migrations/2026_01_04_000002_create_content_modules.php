<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    // Sectors (Agriculture, Health, Education)
    $pdo->exec("
        CREATE TABLE sectors (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(120) NOT NULL UNIQUE,
            name VARCHAR(190) NOT NULL,
            summary VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            icon_key VARCHAR(40) NULL,
            media_id INT UNSIGNED NULL,
            position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_sectors_status (workflow_status, position),
            CONSTRAINT fk_sectors_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Services
    $pdo->exec("
        CREATE TABLE services (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(120) NOT NULL UNIQUE,
            name VARCHAR(190) NOT NULL,
            category VARCHAR(60) NULL,
            summary VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            icon_key VARCHAR(40) NULL,
            media_id INT UNSIGNED NULL,
            position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_services_status (workflow_status, position),
            CONSTRAINT fk_services_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Programmes (covers programmes AND projects — distinguished by 'kind')
    $pdo->exec("
        CREATE TABLE programmes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(120) NOT NULL UNIQUE,
            kind ENUM('programme','project') NOT NULL DEFAULT 'programme',
            title VARCHAR(190) NOT NULL,
            category VARCHAR(80) NULL,
            summary VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            objectives JSON NULL,
            audience JSON NULL,
            activities JSON NULL,
            timeline VARCHAR(190) NULL,
            outcomes MEDIUMTEXT NULL,
            status ENUM('proposed','in_development','active','completed','paused','archived') NOT NULL DEFAULT 'proposed',
            start_date DATE NULL,
            end_date DATE NULL,
            media_id INT UNSIGNED NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(300) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_programmes_status (workflow_status, status),
            INDEX idx_programmes_kind (kind),
            CONSTRAINT fk_programmes_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // News articles
    $pdo->exec("
        CREATE TABLE news_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(80) NOT NULL UNIQUE,
            name VARCHAR(120) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE news_articles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL UNIQUE,
            title VARCHAR(190) NOT NULL,
            category_id INT UNSIGNED NULL,
            excerpt VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            media_id INT UNSIGNED NULL,
            author_id INT UNSIGNED NULL,
            published_at DATETIME NULL,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(300) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_news_status (workflow_status, published_at),
            CONSTRAINT fk_news_category FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL,
            CONSTRAINT fk_news_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL,
            CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Events (as confirmed — in scope)
    $pdo->exec("
        CREATE TABLE events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL UNIQUE,
            title VARCHAR(190) NOT NULL,
            summary VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            location VARCHAR(190) NULL,
            starts_at DATETIME NOT NULL,
            ends_at DATETIME NULL,
            media_id INT UNSIGNED NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_events_status (workflow_status, starts_at),
            CONSTRAINT fk_events_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Success stories
    $pdo->exec("
        CREATE TABLE success_stories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL UNIQUE,
            title VARCHAR(190) NOT NULL,
            person_name VARCHAR(120) NULL,
            person_context VARCHAR(190) NULL,
            summary VARCHAR(500) NULL,
            body MEDIUMTEXT NULL,
            media_id INT UNSIGNED NULL,
            consent_confirmed TINYINT(1) NOT NULL DEFAULT 0,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_stories_status (workflow_status),
            CONSTRAINT fk_stories_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};