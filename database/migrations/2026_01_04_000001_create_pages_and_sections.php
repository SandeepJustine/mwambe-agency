<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE pages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(120) NOT NULL UNIQUE,
            title VARCHAR(190) NOT NULL,
            eyebrow VARCHAR(120) NULL,
            hero_variant ENUM('editorial','split','image_led','minimal') NOT NULL DEFAULT 'editorial',
            hero_headline VARCHAR(255) NULL,
            hero_lede TEXT NULL,
            hero_media_id INT UNSIGNED NULL,
            meta_title VARCHAR(190) NULL,
            meta_description VARCHAR(300) NULL,
            workflow_status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
            published_at DATETIME NULL,
            created_by INT UNSIGNED NULL,
            updated_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_pages_status (workflow_status),
            CONSTRAINT fk_pages_hero_media FOREIGN KEY (hero_media_id) REFERENCES media(id) ON DELETE SET NULL,
            CONSTRAINT fk_pages_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT fk_pages_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE page_sections (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_id INT UNSIGNED NOT NULL,
            position SMALLINT UNSIGNED NOT NULL,
            section_type ENUM(
                'rich_text','cards','features','cta_band','image_text','stats','accordion','quote'
            ) NOT NULL,
            heading VARCHAR(190) NULL,
            eyebrow VARCHAR(120) NULL,
            body MEDIUMTEXT NULL,
            data JSON NULL,
            is_visible TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_sections_page (page_id, position),
            CONSTRAINT fk_sections_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};