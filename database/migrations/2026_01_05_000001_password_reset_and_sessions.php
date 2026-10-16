<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    // The password_resets table was created in Stage 3.
    // Add rate limiting for password reset requests.
    $pdo->exec("
        CREATE TABLE password_reset_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pra_email_time (email, attempted_at),
            INDEX idx_pra_ip_time (ip_address, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Sessions table was also created in Stage 3. Add index for revocation queries.
    $pdo->exec("
        ALTER TABLE sessions
        ADD COLUMN revoked_at DATETIME NULL,
        ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ");
};