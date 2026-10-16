<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = new Mwambe\Core\Config(dirname(__DIR__));
$db     = new Mwambe\Core\Database($config);
$pdo    = $db->pdo();

// Tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

$files = glob(__DIR__ . '/migrations/*.php');
sort($files);

$ran = 0;
foreach ($files as $file) {
    $name = basename($file, '.php');
    if (in_array($name, $applied, true)) {
        continue;
    }

    echo "Migrating: {$name}... ";
    $migration = require $file;
    if (!is_callable($migration)) {
        echo "SKIP (not callable)\n";
        continue;
    }
    $migration($pdo);

    $stmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');
    $stmt->execute([$name]);
    echo "OK\n";
    $ran++;
}

echo $ran === 0 ? "Nothing to migrate.\n" : "Applied {$ran} migration(s).\n";