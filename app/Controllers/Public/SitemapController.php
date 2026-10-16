<?php

declare(strict_types=1);

namespace Mwambe\Controllers\Public;

use Mwambe\Core\Database;
use Mwambe\Core\Http\Response;

final class SitemapController
{
    public static function render(): Response
    {
        $app = $GLOBALS['__mwambe_app'] ?? null;
        if (!$app) {
            return Response::text('Service unavailable', 503);
        }
        $db = $app->container->get(Database::class);

        $urls = [];
        $urls[] = ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'];

        $pages = $db->select("SELECT slug, updated_at FROM pages WHERE workflow_status = 'published'");
        foreach ($pages as $p) {
            $urls[] = ['loc' => url('/' . $p['slug']), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $p['updated_at']];
        }

        $programmes = $db->select("SELECT slug, updated_at FROM programmes WHERE workflow_status = 'published'");
        foreach ($programmes as $p) {
            $urls[] = ['loc' => url('/our-work/programmes/' . $p['slug']), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $p['updated_at']];
        }

        $news = $db->select("SELECT slug, updated_at FROM news_articles WHERE workflow_status = 'published' AND published_at <= NOW()");
        foreach ($news as $n) {
            $urls[] = ['loc' => url('/news/' . $n['slug']), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $n['updated_at']];
        }

        $events = $db->select("SELECT slug, updated_at FROM events WHERE workflow_status = 'published'");
        foreach ($events as $e) {
            $urls[] = ['loc' => url('/events/' . $e['slug']), 'priority' => '0.6', 'changefreq' => 'weekly', 'lastmod' => $e['updated_at']];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . date('c', strtotime($u['lastmod'])) . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return Response::text($xml, 200)->withHeader('Content-Type', 'application/xml; charset=UTF-8');
    }
}