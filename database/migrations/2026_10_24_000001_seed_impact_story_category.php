<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    $pdo->exec("
        INSERT INTO news_categories (slug, name)
        VALUES ('impact-story', 'Impact Story')
        ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");
};
