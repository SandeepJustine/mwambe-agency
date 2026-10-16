<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE sessions (
            id VARCHAR(128) NOT NULL PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            payload TEXT NOT NULL,
            last_activity INT UNSIGNED NOT NULL,
            INDEX idx_sessions_user (user_id),
            INDEX idx_sessions_activity (last_activity),
            CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};