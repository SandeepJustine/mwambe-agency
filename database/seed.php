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

// Site settings
$settings = [
    ['org_name',            'MWAMBE Agency', 'string'],
    ['org_email',           'mwambeagency@gmail.com', 'string'],
    ['org_phone_primary',   '+265 995 111 156', 'string'],
    ['org_phone_secondary', '+265 882 315 575', 'string'],
    ['org_location',        'Lilongwe, Malawi', 'string'],
    ['org_tagline',         'Connecting People, Skills, Enterprises and Opportunities for Resilient Communities.', 'text'],
    ['org_closing',         'Connect. Build Capacity. Create Opportunity. Strengthen Communities.', 'text'],
];
$stmt = $pdo->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)');
foreach ($settings as $s) {
    $stmt->execute($s);
}

// Homepage slides — exactly three
$slideCount = (int) $pdo->query('SELECT COUNT(*) FROM homepage_slides')->fetchColumn();
if ($slideCount === 0) {
    $slides = [
        [
            'position' => 1,
            'layout'   => 'editorial',
            'eyebrow'  => 'MWAMBE Agency',
            'headline' => 'Connecting People, Skills, Enterprises and Opportunities for Resilient Communities.',
            'supporting' => 'MWAMBE Agency connects young people, skilled professionals, SMEs, cooperatives and community groups to opportunities, practical capacity building and partnerships that strengthen livelihoods and community resilience.',
            'primary_cta_label' => 'Explore Our Work',
            'primary_cta_url'   => '/our-work',
            'secondary_cta_label' => 'Partner With Us',
            'secondary_cta_url'   => '/partner-with-us',
        ],
        [
            'position' => 2,
            'layout'   => 'split',
            'eyebrow'  => 'Youth & Enterprise',
            'headline' => 'Building Skills. Unlocking Potential. Creating Opportunities.',
            'supporting' => 'From youth employability and professional positioning to entrepreneurship, digital skills and SME growth — MWAMBE prepares people to compete, participate and create value.',
            'primary_cta_label' => 'Discover Our Programmes',
            'primary_cta_url'   => '/our-work',
            'secondary_cta_label' => 'Get Involved',
            'secondary_cta_url'   => '/partner-with-us',
        ],
        [
            'position' => 3,
            'layout'   => 'image_led',
            'eyebrow'  => 'Community & Resilience',
            'headline' => 'Stronger Enterprises. Resilient Communities. Sustainable Livelihoods.',
            'supporting' => 'Through agribusiness, community development and practical partnerships, MWAMBE supports enterprises and community groups to build sustainable, climate-resilient livelihoods.',
            'primary_cta_label' => 'Work With Us',
            'primary_cta_url'   => '/partner-with-us',
            'secondary_cta_label' => 'Contact MWAMBE',
            'secondary_cta_url'   => '/contact',
        ],
    ];

    $stmt = $pdo->prepare('
        INSERT INTO homepage_slides
            (position, layout, eyebrow, headline, supporting,
             primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url, is_active)
        VALUES
            (:position, :layout, :eyebrow, :headline, :supporting,
             :primary_cta_label, :primary_cta_url, :secondary_cta_label, :secondary_cta_url, 1)
    ');
    foreach ($slides as $slide) {
        $stmt->execute($slide);
    }
    echo "Seeded 3 homepage slides.\n";
} else {
    echo "Homepage slides already present ({$slideCount}). Skipping.\n";
}

// Initial administrator (only if none exists)
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count === 0) {
    echo "No users found. Creating initial administrator.\n";
    echo "Enter admin email: ";
    $email = trim((string) fgets(STDIN));

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
}

echo "Seeding complete.\n";