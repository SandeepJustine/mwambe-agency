<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    // Impact indicators with verification gating
    $pdo->exec("
        CREATE TABLE impact_indicators (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(80) NOT NULL UNIQUE,
            label VARCHAR(120) NOT NULL,
            value VARCHAR(40) NOT NULL,
            unit VARCHAR(40) NULL,
            description VARCHAR(500) NULL,
            reporting_period VARCHAR(120) NULL,
            evidence_reference VARCHAR(500) NULL,
            verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
            is_public TINYINT(1) NOT NULL DEFAULT 1,
            reviewer_id INT UNSIGNED NULL,
            approved_at DATETIME NULL,
            position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_impact_public (verification_status, is_public, position),
            CONSTRAINT fk_impact_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Contact enquiries
    $pdo->exec("
        CREATE TABLE contact_enquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference CHAR(10) NOT NULL UNIQUE,
            name VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NULL,
            subject VARCHAR(190) NULL,
            message MEDIUMTEXT NOT NULL,
            privacy_ack TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('new','in_review','assigned','responded','resolved','archived') NOT NULL DEFAULT 'new',
            assigned_to INT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            email_sent_at DATETIME NULL,
            email_error VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_contact_status (status, created_at),
            CONSTRAINT fk_contact_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Partnership enquiries
    $pdo->exec("
        CREATE TABLE partnership_enquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference CHAR(10) NOT NULL UNIQUE,
            organisation_name VARCHAR(190) NOT NULL,
            contact_person VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NULL,
            organisation_type VARCHAR(80) NULL,
            partnership_interest VARCHAR(190) NULL,
            proposed_collaboration MEDIUMTEXT NOT NULL,
            additional_message MEDIUMTEXT NULL,
            privacy_ack TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('new','in_review','assigned','responded','resolved','archived') NOT NULL DEFAULT 'new',
            assigned_to INT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL,
            email_sent_at DATETIME NULL,
            email_error VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_partnership_status (status, created_at),
            CONSTRAINT fk_partnership_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Enquiry notes (internal)
    $pdo->exec("
        CREATE TABLE enquiry_notes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            enquiry_type ENUM('contact','partnership') NOT NULL,
            enquiry_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NULL,
            body TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notes_enquiry (enquiry_type, enquiry_id),
            CONSTRAINT fk_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};