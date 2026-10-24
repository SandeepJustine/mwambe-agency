<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE team_members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(190) NOT NULL,
            position_title VARCHAR(190) NOT NULL,
            bio TEXT NULL,
            media_id INT UNSIGNED NULL,
            position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_team_members_active (is_active, position),
            CONSTRAINT fk_team_members_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
