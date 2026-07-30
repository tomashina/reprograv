<?php
/**
 * Refresh OCMOD and generate the SEO sitemap files from the command line.
 *
 * Run from the project root:
 *   php tools/generate_sitemaps.php
 */

require __DIR__ . '/refresh_ocmod.php';

class SitemapCliUser {
    public function hasPermission($key, $value) {
        return $key === 'modify' && $value === 'extension/feed/boost_sitemap';
    }
}

$registry->set('user', new SitemapCliUser());

$request->server['REQUEST_METHOD'] = 'POST';
$request->post = array(
    'feed_boost_sitemap_status' => 1,
    'feed_boost_sitemap_item_limit' => 1000,
    'feed_boost_sitemap_item' => array(
        'product',
        'category',
        'information',
        'blog'
    )
);

require_once DIR_APPLICATION . 'controller/extension/feed/boost_sitemap.php';

$sitemap_controller = new ControllerExtensionFeedBoostSitemap($registry);
$sitemap_controller->generate();

fwrite(STDOUT, "SEO sitemap files generated.\n");
