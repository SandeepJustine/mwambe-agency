<?php

declare(strict_types=1);

/**
 * MWAMBE Agency — front controller.
 * All web requests enter here. Document root MUST be public/.
 */

define('MWAMBE_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// Composer autoloader
$autoload = BASE_PATH . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Application dependencies not installed. Run: composer install');
}
require $autoload;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Bootstrap the application
$app = new Mwambe\Core\Application(BASE_PATH);

// Send the response
$app->run();