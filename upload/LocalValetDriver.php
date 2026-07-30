<?php

use Valet\Drivers\BasicValetDriver;

/**
 * Laravel Herd does not process OpenCart's Apache .htaccess file.
 *
 * This local driver mirrors the important rewrite rules by forwarding clean
 * URLs to OpenCart through the `_route_` request parameter.
 */
class LocalValetDriver extends BasicValetDriver
{
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return is_file($sitePath . '/config.php')
            && is_file($sitePath . '/index.php')
            && is_dir($sitePath . '/catalog')
            && is_dir($sitePath . '/system');
    }

    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        parent::beforeLoading($sitePath, $siteName, $uri);

        $path = trim(parse_url($uri, PHP_URL_PATH) ?: '', '/');

        if ($path === 'robots.txt') {
            $this->setRoute('common/robots');

            return;
        }

        if ($path === 'llms.txt') {
            $this->setRoute('common/robots/llms');

            return;
        }

        if ($path === 'sitemap-index.xml') {
            $this->setRoute('extension/feed/boost_sitemap');

            return;
        }

        if ($path === 'googlebase.xml') {
            $this->setRoute('extension/feed/google_base');

            return;
        }

        if ($path === 'kontaktirajte-nas') {
            $this->setRoute('information/contact');

            return;
        }

        if ($path !== ''
            && $path !== 'index.php'
            && $path !== 'admin'
            && strpos($path, 'admin/') !== 0
            && !isset($_GET['_route_'])
            && !isset($_GET['route'])
        ) {
            $_GET['_route_'] = $path;
            $_REQUEST['_route_'] = $path;
        }
    }

    public function isStaticFile(string $sitePath, string $siteName, string $uri)
    {
        if (trim(parse_url($uri, PHP_URL_PATH) ?: '', '/') === 'robots.txt') {
            return false;
        }

        return parent::isStaticFile($sitePath, $siteName, $uri);
    }

    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        if (trim(parse_url($uri, PHP_URL_PATH) ?: '', '/') === 'robots.txt') {
            $_SERVER['SCRIPT_FILENAME'] = $sitePath . '/index.php';
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['DOCUMENT_ROOT'] = $sitePath;

            return $sitePath . '/index.php';
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }

    private function setRoute($route): void
    {
        unset($_GET['_route_'], $_REQUEST['_route_']);
        $_GET['route'] = $route;
        $_REQUEST['route'] = $route;
    }
}
