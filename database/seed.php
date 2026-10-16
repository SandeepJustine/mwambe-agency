<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = new Mwambe\Core\Config(dirname(__DIR__));
$db     = new Mwambe\Core\Database($config);
$pdo    = $db->pdo();

// Roles
$roles = [
    ['super_admin', 'Super Administrator', 'Full system access'],
    ['admin',       'Administrator',       'Manages content and settings'],
    ['editor',      'Editor',              'Creates and edits content'],
    ['contributor', 'Contributor',         'Creates drafts only'],
    ['enquiries',   'Enquiry Manager',     'Handles enquiries'],
];
$stmt = $pdo->prepare('INSERT IGNORE INTO roles (slug, name, description) VALUES (?, ?, ?)');
foreach ($roles as $r) {
    $stmt->execute($r);
}

// Site settings — homepage hero slider seed (exactly 3 slides will be added
// when the slide table exists in a later stage).
$settings = [
    ['org_name',          'MWAMBE Agency', 'string'],
    ['org_email',         'mwambeagency@gmail.com', 'string'],
    ['org_phone_primary', '+265 995 111 156', 'string'],
    ['org_phone_secondary', '+265 882 315 575', 'string'],
    ['org_location',      'Lilongwe, Malawi', 'string'],
    ['org_tagline',       'Connecting People, Skills, Enterprises and Opportunities for Resilient Communities.', 'text'],
    ['org_closing',       'Connect. Build Capacity. Create Opportunity. Strengthen Communities.', 'text'],
];
$stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)');
foreach ($settings as $s) {
    $stmt->execute($s);
}

// Initial administrator (only if none exists)
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count === 0) {
    echo "No users found. Creating initial administrator.\n";
    echo "Enter admin email: ";
    $email = trim((string) fgets(STDIN));

    // Generate a strong random password
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $password = '';
    for ($i = 0; $i < 20; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute(['Administrator', $email, $hash]);
    $userId = (int) $pdo->lastInsertId();

    $roleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'super_admin'")->fetchColumn();
    $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)')->execute([$userId, $roleId]);

    echo "\n============================================\n";
    echo " ADMINISTRATOR CREATED\n";
    echo " Email:    {$email}\n";
    echo " Password: {$password}\n";
    echo " Save this password now — it will not be shown again.\n";
    echo "============================================\n\n";
} else {
    echo "Users already exist — skipping administrator creation.\n";
}

echo "Seeding complete.\n";