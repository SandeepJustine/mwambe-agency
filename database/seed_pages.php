<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = new Mwambe\Core\Config(dirname(__DIR__));
$db     = new Mwambe\Core\Database($config);
$pdo    = $db->pdo();

$pages = [
    [
        'slug' => 'about',
        'title' => 'About MWAMBE Agency',
        'eyebrow' => 'Who We Are',
        'hero_variant' => 'editorial',
        'hero_headline' => 'Access to an opportunity is only the beginning.',
        'hero_lede' => 'MWAMBE Agency is a young, Malawi-based organisation working to connect young people, skilled professionals, SMEs, cooperatives and community groups to opportunities that strengthen skills, livelihoods, enterprise development and community resilience.',
        'meta_title' => 'About MWAMBE Agency | Lilongwe, Malawi',
        'meta_description' => 'MWAMBE Agency — Men Women All Must Be Equal in Leadership and Opportunities. A Malawi-based organisation connecting people, enterprises and communities to opportunity.',
        'sections' => [
            [
                'type' => 'rich_text', 'eyebrow' => 'Our Meaning',
                'heading' => 'Men Women All Must Be Equal in Leadership and Opportunities.',
                'body' => "The name MWAMBE is an acronym — but more than that, it is a commitment.\n\nWe believe that access to an opportunity is only the beginning. People and organisations also need the skills, information, networks, confidence and practical support required to convert opportunities into meaningful outcomes. Our work sits at the intersection between those who need opportunities and the organisations that can create or provide them.",
            ],
            [
                'type' => 'cards', 'eyebrow' => 'Purpose', 'heading' => 'Vision & Mission',
                'data' => [
                    'cards' => [
                        ['title' => 'Our Vision', 'body' => 'Build resilient communities where people, enterprises and community groups can access opportunities, develop practical capacity and create sustainable livelihoods.'],
                        ['title' => 'Our Mission', 'body' => 'To connect skilled people, SMEs, cooperatives and community groups to opportunities, partnerships and practical capacity-building initiatives that contribute to resilient communities and sustainable livelihoods.'],
                        ['title' => 'Our Approach', 'body' => 'Identify → Build Capacity → Connect → Support Action → Document Results. We prepare people to compete, participate and create value.'],
                    ],
                ],
            ],
            [
                'type' => 'features', 'eyebrow' => 'Values', 'heading' => 'What guides us',
                'data' => [
                    'items' => [
                        ['title' => 'Equality', 'body' => 'Promoting fair access to leadership and opportunities.'],
                        ['title' => 'Opportunity', 'body' => 'Connecting people and organisations to relevant pathways for growth.'],
                        ['title' => 'Integrity', 'body' => 'Building relationships based on honesty, accountability and responsible practice.'],
                        ['title' => 'Collaboration', 'body' => 'Working with partners rather than operating in isolation.'],
                        ['title' => 'Practical Learning', 'body' => 'Prioritising skills that can be applied beyond training rooms.'],
                        ['title' => 'Innovation', 'body' => 'Using appropriate technology, creativity and new approaches to solve problems.'],
                        ['title' => 'Sustainability', 'body' => 'Building initiatives and partnerships that can continue beyond individual activities.'],
                        ['title' => 'Accountability', 'body' => 'Tracking activities, results, resources and lessons learned.'],
                    ],
                ],
            ],
        ],
    ],
    [
        'slug' => 'our-work',
        'title' => 'Our Work',
        'eyebrow' => 'What We Do',
        'hero_variant' => 'split',
        'hero_headline' => 'Four areas of work. One connected approach.',
        'hero_lede' => 'From youth development and enterprise support to agribusiness and community projects, MWAMBE Agency works across the areas that matter most to resilient communities in Malawi.',
        'meta_title' => 'Our Work | MWAMBE Agency',
        'meta_description' => 'Youth development, entrepreneurship and SME support, agriculture and agribusiness, and community project development across Malawi.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Connecting the people who need opportunities with the organisations that provide them.', 'body' => 'MWAMBE operates at the intersection between young people, emerging professionals, SMEs, cooperatives and community groups — and the organisations, enterprises and institutions that can create or provide opportunities. Rather than simply informing people about opportunities, we prepare them to compete, participate and create value.'],
            ['type' => 'cta_band', 'heading' => 'Explore our programmes', 'body' => 'See how we are structuring our work into measurable programmes with defined outcomes.', 'data' => ['buttons' => [['label' => 'View Programmes', 'url' => '/our-work/programmes', 'primary' => true], ['label' => 'Partner With Us', 'url' => '/partner-with-us', 'primary' => false]]]],
        ],
    ],
    [
        'slug' => 'sectors',
        'title' => 'Priority Sectors',
        'eyebrow' => 'Sectors',
        'hero_variant' => 'image_led',
        'hero_headline' => 'Priority sectors.',
        'hero_lede' => 'MWAMBE works across the sectors that most directly shape livelihoods and community resilience in Malawi — agriculture, health and education.',
        'meta_title' => 'Priority Sectors | MWAMBE Agency',
        'meta_description' => 'Agriculture, Health and Education — the priority sectors where MWAMBE Agency concentrates its work in Malawi.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Where we concentrate', 'body' => 'MWAMBE operates across multiple sectors, but three areas are central: agriculture (the backbone of Malawi\'s economy), health (a foundation of productive communities), and education (the pathway to opportunity). Read about each below.'],
        ],
    ],
    [
        'slug' => 'impact',
        'title' => 'Our Impact',
        'eyebrow' => 'Evidence',
        'hero_variant' => 'editorial',
        'hero_headline' => 'Evidence over activity.',
        'hero_lede' => 'MWAMBE is committed to moving beyond activity-based reporting toward stronger evidence of results. Only verified impact figures are shown here.',
        'meta_title' => 'Our Impact | MWAMBE Agency',
        'meta_description' => 'Verified impact indicators from MWAMBE Agency — evidence of reach, participation, skills, enterprise support and partnership outcomes.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Our measurement approach', 'body' => 'Our developing performance framework tracks reach, participation, skills, enterprise support, partnerships, sustainability, outcomes and learning. This page displays only indicators that have been reviewed and verified by an authorised administrator.'],
        ],
    ],
    [
        'slug' => 'news',
        'title' => 'News & Activities',
        'eyebrow' => 'Updates',
        'hero_variant' => 'minimal',
        'hero_headline' => 'News, insights and activities.',
        'hero_lede' => 'Updates on MWAMBE Agency\'s work with young people, enterprises and communities across Malawi.',
        'meta_title' => 'News & Activities | MWAMBE Agency',
        'meta_description' => 'Latest news, insights and updates on MWAMBE Agency activities in Malawi.',
        'sections' => [],
    ],
    [
        'slug' => 'partner-with-us',
        'title' => 'Partner With Us',
        'eyebrow' => 'Collaboration',
        'hero_variant' => 'split',
        'hero_headline' => 'The strongest partnerships are not simply transactions.',
        'hero_lede' => 'We combine resources, expertise, networks and local knowledge to produce results that neither organisation could achieve as effectively alone.',
        'meta_title' => 'Partner With Us | MWAMBE Agency',
        'meta_description' => 'Partnership opportunities with MWAMBE Agency — programme co-design, funding, technical assistance, mentorship, research and community project implementation.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Why partner with MWAMBE?', 'body' => 'MWAMBE brings local youth reach, practical implementation experience, a multi-sector approach, community-level connections, an emerging talent pipeline, and a partnership orientation built on complementing — not duplicating — existing institutions and initiatives.'],
            ['type' => 'features', 'heading' => 'Partnership areas', 'data' => ['items' => [
                ['title' => 'Programme co-design', 'body' => 'Building programmes together, around shared priorities.'],
                ['title' => 'Funding & sponsorship', 'body' => 'Financial and in-kind support for programme delivery.'],
                ['title' => 'Technical assistance', 'body' => 'Expertise that strengthens delivery capacity.'],
                ['title' => 'Mentorship & training', 'body' => 'Direct engagement with young people and enterprises.'],
                ['title' => 'Research & evaluation', 'body' => 'Evidence generation and learning.'],
                ['title' => 'Market linkages', 'body' => 'Connecting enterprises to markets and buyers.'],
                ['title' => 'Employment pathways', 'body' => 'Routes into jobs and opportunities.'],
                ['title' => 'Community project implementation', 'body' => 'Delivering projects at community level.'],
            ]]],
        ],
    ],
    [
        'slug' => 'contact',
        'title' => 'Contact MWAMBE',
        'eyebrow' => 'Get In Touch',
        'hero_variant' => 'minimal',
        'hero_headline' => 'Let\'s talk.',
        'hero_lede' => 'Reach MWAMBE Agency directly — or send us a message and we\'ll get back to you.',
        'meta_title' => 'Contact MWAMBE Agency',
        'meta_description' => 'Contact MWAMBE Agency in Lilongwe, Malawi. Email, telephone and enquiry form.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Contact details', 'body' => "MWAMBE Agency\nLilongwe, Malawi\nEmail: mwambeagency@gmail.com\nTelephone: +265 995 111 156 / +265 882 315 575"],
        ],
    ],
    [
        'slug' => 'privacy',
        'title' => 'Privacy Policy',
        'eyebrow' => 'Legal',
        'hero_variant' => 'minimal',
        'hero_headline' => 'Privacy Policy',
        'hero_lede' => 'How MWAMBE Agency collects, uses and protects personal information.',
        'meta_title' => 'Privacy Policy | MWAMBE Agency',
        'meta_description' => 'MWAMBE Agency privacy policy.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => '⚠ Draft pending legal review', 'body' => "The text below is a working draft prepared during website development. It requires review by a qualified legal practitioner before being relied on.\n\n**Information we collect:** Name, email address, telephone number and message content when you submit a contact or partnership enquiry.\n\n**How we use it:** To respond to your enquiry and, where relevant, to discuss partnership or programme opportunities.\n\n**How we store it:** Securely on our web server and database. Access is restricted to authorised MWAMBE Agency staff.\n\n**Retention:** Enquiry records are retained for a reasonable period to allow follow-up, then reviewed for deletion.\n\n**Sharing:** We do not sell or share personal information with third parties except where required by law.\n\n**Your rights:** You may request access to, correction of, or deletion of personal information we hold about you by contacting mwambeagency@gmail.com."],
        ],
    ],
    [
        'slug' => 'terms',
        'title' => 'Terms of Use',
        'eyebrow' => 'Legal',
        'hero_variant' => 'minimal',
        'hero_headline' => 'Terms of Use',
        'hero_lede' => 'Terms governing use of the MWAMBE Agency website.',
        'meta_title' => 'Terms of Use | MWAMBE Agency',
        'meta_description' => 'MWAMBE Agency website terms of use.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => '⚠ Draft pending legal review', 'body' => "The text below is a working draft prepared during website development. It requires review by a qualified legal practitioner before being relied on.\n\nBy using this website you agree to use it lawfully and not to interfere with its operation or security. Content is provided for general information and does not constitute professional advice. MWAMBE Agency makes reasonable efforts to keep information accurate but does not guarantee completeness. External links are provided for convenience and MWAMBE Agency is not responsible for the content of linked sites."],
        ],
    ],
    [
        'slug' => 'accessibility',
        'title' => 'Accessibility Statement',
        'eyebrow' => 'Accessibility',
        'hero_variant' => 'minimal',
        'hero_headline' => 'Accessibility Statement',
        'hero_lede' => 'MWAMBE Agency is committed to making this website usable by everyone.',
        'meta_title' => 'Accessibility Statement | MWAMBE Agency',
        'meta_description' => 'MWAMBE Agency is committed to accessibility for all users.',
        'sections' => [
            ['type' => 'rich_text', 'heading' => 'Our commitment', 'body' => "This website aims to meet the Web Content Accessibility Guidelines (WCAG) 2.2 at level AA. We work to ensure keyboard navigation, sufficient colour contrast, meaningful alternative text for images, and reduced-motion support for users who prefer less animation.\n\nIf you encounter an accessibility barrier on this website, please contact mwambeagency@gmail.com so we can address it."],
        ],
    ],
];

$insertPage = $pdo->prepare("
    INSERT INTO pages (slug, title, eyebrow, hero_variant, hero_headline, hero_lede, meta_title, meta_description, workflow_status, published_at)
    VALUES (:slug, :title, :eyebrow, :hero_variant, :hero_headline, :hero_lede, :meta_title, :meta_description, 'published', NOW())
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        eyebrow = VALUES(eyebrow),
        hero_variant = VALUES(hero_variant),
        hero_headline = VALUES(hero_headline),
        hero_lede = VALUES(hero_lede),
        meta_title = VALUES(meta_title),
        meta_description = VALUES(meta_description)
");

$insertSection = $pdo->prepare("
    INSERT INTO page_sections (page_id, position, section_type, heading, eyebrow, body, data, is_visible)
    VALUES (:page_id, :position, :section_type, :heading, :eyebrow, :body, :data, 1)
");

foreach ($pages as $page) {
    $insertPage->execute([
        'slug' => $page['slug'],
        'title' => $page['title'],
        'eyebrow' => $page['eyebrow'] ?? null,
        'hero_variant' => $page['hero_variant'],
        'hero_headline' => $page['hero_headline'],
        'hero_lede' => $page['hero_lede'],
        'meta_title' => $page['meta_title'],
        'meta_description' => $page['meta_description'],
    ]);
    $pageId = (int) $pdo->lastInsertId();
    if ($pageId === 0) {
        $pageId = (int) $pdo->query("SELECT id FROM pages WHERE slug = " . $pdo->quote($page['slug']))->fetchColumn();
        // Wipe existing sections and reinsert (simple, safe for seeds)
        $pdo->prepare('DELETE FROM page_sections WHERE page_id = ?')->execute([$pageId]);
    }
    foreach ($page['sections'] as $i => $s) {
        $insertSection->execute([
            'page_id' => $pageId,
            'position' => $i + 1,
            'section_type' => $s['type'],
            'heading' => $s['heading'] ?? null,
            'eyebrow' => $s['eyebrow'] ?? null,
            'body' => $s['body'] ?? null,
            'data' => isset($s['data']) ? json_encode($s['data']) : null,
        ]);
    }
    echo "Seeded page: {$page['slug']}\n";
}

// Impact indicators — all pending verification
$impact = [
    ['key_name' => 'young_people_supported', 'label' => 'Young people supported', 'value' => '700', 'unit' => '+', 'description' => 'Young people supported through opportunity-related assistance, capacity building and professional support.', 'reporting_period' => 'To date', 'evidence_reference' => 'Organisational profile 2026 — pending verification', 'position' => 1],
    ['key_name' => 'smes_engaged', 'label' => 'SMEs engaged', 'value' => '10', 'unit' => '+', 'description' => 'Small and medium enterprises engaged through marketing, project support, opportunity connections and related professional services.', 'reporting_period' => 'To date', 'evidence_reference' => 'Organisational profile 2026 — pending verification', 'position' => 2],
    ['key_name' => 'organisations_engaged', 'label' => 'Organisations engaged', 'value' => '5', 'unit' => '', 'description' => 'Organisations engaged through partnership, marketing and related professional services.', 'reporting_period' => 'To date', 'evidence_reference' => 'Organisational profile 2026 — pending verification', 'position' => 3],
];
$stmt = $pdo->prepare("
    INSERT INTO impact_indicators (key_name, label, value, unit, description, reporting_period, evidence_reference, verification_status, position)
    VALUES (:key_name, :label, :value, :unit, :description, :reporting_period, :evidence_reference, 'pending', :position)
    ON DUPLICATE KEY UPDATE
        label = VALUES(label),
        value = VALUES(value),
        unit = VALUES(unit),
        description = VALUES(description),
        reporting_period = VALUES(reporting_period),
        evidence_reference = VALUES(evidence_reference),
        position = VALUES(position)
");
foreach ($impact as $i) {
    $stmt->execute($i);
}
echo "Seeded 3 impact indicators (all pending verification).\n";

// Sectors
$sectors = [
    ['slug' => 'agriculture', 'name' => 'Agriculture', 'summary' => 'Agriculture is the backbone of Malawi\'s economy and the primary livelihood for most households. MWAMBE works on agribusiness development, agricultural entrepreneurship, youth participation and climate-resilient livelihoods.', 'position' => 1, 'is_featured' => 1],
    ['slug' => 'health', 'name' => 'Health', 'summary' => 'Healthy communities are productive communities. MWAMBE supports health-related projects and partnerships that strengthen community resilience and wellbeing.', 'position' => 2, 'is_featured' => 1],
    ['slug' => 'education', 'name' => 'Education', 'summary' => 'Education opens the pathway to opportunity. MWAMBE supports education-related initiatives, digital skills development and youth learning.', 'position' => 3, 'is_featured' => 1],
];
$stmt = $pdo->prepare("
    INSERT INTO sectors (slug, name, summary, position, is_featured, workflow_status)
    VALUES (:slug, :name, :summary, :position, :is_featured, 'published')
    ON DUPLICATE KEY UPDATE
        name = VALUES(name), summary = VALUES(summary), position = VALUES(position), is_featured = VALUES(is_featured)
");
foreach ($sectors as $s) {
    $stmt->execute($s);
}
echo "Seeded 3 sectors.\n";

// Services (from the organisation profile)
$services = [
    ['slug' => 'youth-professional-development', 'name' => 'Youth & Professional Development', 'category' => 'youth', 'summary' => 'Career coaching, CV and cover-letter development, personal branding, LinkedIn and professional visibility, employability workshops, mentorship and networking.', 'position' => 1, 'is_featured' => 1],
    ['slug' => 'entrepreneurship-sme-development', 'name' => 'Entrepreneurship & SME Development', 'category' => 'enterprise', 'summary' => 'Business development, registration guidance, marketing and brand development, digital marketing, market research, proposal development and entrepreneurship capacity building.', 'position' => 2, 'is_featured' => 1],
    ['slug' => 'agriculture-agribusiness', 'name' => 'Agriculture & Agribusiness', 'category' => 'agriculture', 'summary' => 'Agribusiness development, agricultural entrepreneurship, youth participation in agriculture, research and climate-resilient livelihoods.', 'position' => 3, 'is_featured' => 1],
    ['slug' => 'project-community-development', 'name' => 'Project & Community Development', 'category' => 'community', 'summary' => 'Project planning, community engagement, field operations, research, monitoring, evaluation and reporting.', 'position' => 4, 'is_featured' => 1],
];
$stmt = $pdo->prepare("
    INSERT INTO services (slug, name, category, summary, position, is_featured, workflow_status)
    VALUES (:slug, :name, :category, :summary, :position, :is_featured, 'published')
    ON DUPLICATE KEY UPDATE
        name = VALUES(name), category = VALUES(category), summary = VALUES(summary), position = VALUES(position), is_featured = VALUES(is_featured)
");
foreach ($services as $s) {
    $stmt->execute($s);
}
echo "Seeded 4 services.\n";

// VAY-2026 as a programme (In Development — not Active)
$stmt = $pdo->prepare("
    INSERT INTO programmes
        (slug, kind, title, category, summary, body, status, workflow_status, is_featured)
    VALUES
        (:slug, 'programme', :title, :category, :summary, :body, 'in_development', 'published', 1)
    ON DUPLICATE KEY UPDATE
        title = VALUES(title), summary = VALUES(summary), body = VALUES(body), status = VALUES(status)
");
$stmt->execute([
    'slug' => 'vay-2026',
    'title' => 'VAY-2026 — Value Addition on Youths',
    'category' => 'youth',
    'summary' => 'A practical capacity-building initiative to move young people from learning to application — through personal branding, digital skills, entrepreneurship, agribusiness, mentorship and connection to opportunity.',
    'body' => "VAY-2026 is a developing initiative. Rather than treating it simply as a youth event, MWAMBE is positioning it as a platform through which participants develop practical capabilities in personal branding and professional positioning, job and opportunity readiness, digital marketing and content creation, entrepreneurship, agribusiness, networking, mentorship, and practical digital skills.\n\nWhere appropriate, MWAMBE intends to document participant capabilities and create pathways through which prepared participants can be connected to companies, organisations and relevant opportunities.\n\n**This programme is in development.** Registration is not currently open, activities have not yet started, and no funding has been secured. Launch status will be published when confirmed.",
]);
echo "Seeded VAY-2026 programme.\n";

// A welcome news article placeholder (draft)
$stmt = $pdo->prepare("
    INSERT INTO news_articles (slug, title, excerpt, body, workflow_status)
    VALUES ('mwambe-website-launch', 'MWAMBE Agency launches new website', 'MWAMBE Agency is pleased to launch a new website to better connect people, enterprises and communities to opportunities.', 'This is a draft article waiting for review and editing by MWAMBE staff. Replace with real news when ready.', 'draft')
    ON DUPLICATE KEY UPDATE title = VALUES(title)
");
$stmt->execute();
echo "Seeded 1 draft news article.\n";

echo "\nAll page and content seeds complete.\n";