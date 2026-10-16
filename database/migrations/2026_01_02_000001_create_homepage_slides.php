<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    // Media table (referenced by slides; full library UI comes in Stage 3)
    $pdo->exec("
        CREATE TABLE media (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            size_bytes INT UNSIGNED NOT NULL,
            width INT UNSIGNED NULL,
            height INT UNSIGNED NULL,
            alt_text VARCHAR(255) NULL,
            caption VARCHAR(500) NULL,
            uploaded_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Homepage slides — exactly three, enforced by trigger below
    $pdo->exec("
        CREATE TABLE homepage_slides (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            position TINYINT UNSIGNED NOT NULL,
            layout ENUM('editorial','split','image_led') NOT NULL DEFAULT 'editorial',
            eyebrow VARCHAR(120) NULL,
            headline VARCHAR(255) NOT NULL,
            supporting TEXT NOT NULL,
            primary_cta_label VARCHAR(60) NULL,
            primary_cta_url VARCHAR(255) NULL,
            secondary_cta_label VARCHAR(60) NULL,
            secondary_cta_url VARCHAR(255) NULL,
            media_id INT UNSIGNED NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_homepage_position (position),
            CONSTRAINT fk_slides_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // --- Exactly-3 active-slide enforcement via triggers ---

    $pdo->exec("
        CREATE TRIGGER trg_slides_before_insert
        BEFORE INSERT ON homepage_slides
        FOR EACH ROW
        BEGIN
            DECLARE active_count INT;
            IF NEW.is_active = 1 THEN
                SELECT COUNT(*) INTO active_count FROM homepage_slides WHERE is_active = 1;
                IF active_count >= 3 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'homepage_slides: cannot have more than 3 active slides';
                END IF;
            END IF;
        END
    ");

    $pdo->exec("
        CREATE TRIGGER trg_slides_before_update
        BEFORE UPDATE ON homepage_slides
        FOR EACH ROW
        BEGIN
            DECLARE active_count INT;
            IF NEW.is_active = 1 AND OLD.is_active = 0 THEN
                SELECT COUNT(*) INTO active_count FROM homepage_slides WHERE is_active = 1;
                IF active_count >= 3 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'homepage_slides: cannot have more than 3 active slides';
                END IF;
            END IF;

            IF NEW.is_active = 0 AND OLD.is_active = 1 THEN
                SELECT COUNT(*) INTO active_count FROM homepage_slides WHERE is_active = 1;
                IF active_count <= 1 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'homepage_slides: must keep exactly 3 active slides';
                END IF;
            END IF;
        END
    ");

    $pdo->exec("
        CREATE TRIGGER trg_slides_before_delete
        BEFORE DELETE ON homepage_slides
        FOR EACH ROW
        BEGIN
            DECLARE active_count INT;
            IF OLD.is_active = 1 THEN
                SELECT COUNT(*) INTO active_count FROM homepage_slides WHERE is_active = 1;
                IF active_count <= 3 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'homepage_slides: cannot delete — must keep exactly 3 active slides';
                END IF;
            END IF;
        END
    ");
};