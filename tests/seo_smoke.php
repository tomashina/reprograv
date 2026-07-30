<?php
/**
 * Repro-Grav public SEO/privacy smoke test.
 *
 * Usage:
 *   php tests/seo_smoke.php
 *   php tests/seo_smoke.php https://www.repro-grav.com
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$base_url = isset($argv[1]) ? rtrim($argv[1], '/') : 'https://reprograv.test';
$failures = 0;
$warnings = 0;

function requestUrl($url, array $post = null) {
    $handle = curl_init($url);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_ENCODING => ''
    );

    if ($post !== null) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($post);
    }

    curl_setopt_array($handle, $options);
    $response = curl_exec($handle);

    if ($response === false) {
        $error = curl_error($handle);
        curl_close($handle);

        return array(
            'status' => 0,
            'headers' => '',
            'body' => '',
            'error' => $error
        );
    }

    $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $result = array(
        'status' => (int)curl_getinfo($handle, CURLINFO_HTTP_CODE),
        'headers' => substr($response, 0, $header_size),
        'body' => substr($response, $header_size),
        'error' => ''
    );
    curl_close($handle);

    return $result;
}

function passCheck($message) {
    fwrite(STDOUT, "[OK]   " . $message . PHP_EOL);
}

function failCheck($message) {
    global $failures;
    $failures++;
    fwrite(STDOUT, "[FAIL] " . $message . PHP_EOL);
}

function warnCheck($message) {
    global $warnings;
    $warnings++;
    fwrite(STDOUT, "[WARN] " . $message . PHP_EOL);
}

function assertCheck($condition, $message) {
    if ($condition) {
        passCheck($message);
    } else {
        failCheck($message);
    }
}

function loadHtmlDocument($html) {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return $document;
}

function jsonLdBlocks($html) {
    $document = loadHtmlDocument($html);
    $xpath = new DOMXPath($document);
    $blocks = array();

    foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
        $decoded = json_decode(trim($script->textContent), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            failCheck('JSON-LD blok je valjani JSON: ' . json_last_error_msg());
            continue;
        }

        $blocks[] = $decoded;
    }

    return $blocks;
}

function schemaTypes($value) {
    $types = array();

    if (!is_array($value)) {
        return $types;
    }

    if (isset($value['@type'])) {
        foreach ((array)$value['@type'] as $type) {
            $types[] = $type;
        }
    }

    foreach ($value as $item) {
        if (is_array($item)) {
            $types = array_merge($types, schemaTypes($item));
        }
    }

    return array_unique($types);
}

function containsKeyRecursive($value, array $forbidden_keys) {
    if (!is_array($value)) {
        return false;
    }

    foreach ($value as $key => $item) {
        if (in_array((string)$key, $forbidden_keys, true)) {
            return true;
        }

        if (is_array($item) && containsKeyRecursive($item, $forbidden_keys)) {
            return true;
        }
    }

    return false;
}

function hasCanonical($html, $expected) {
    $document = loadHtmlDocument($html);
    $xpath = new DOMXPath($document);
    $links = $xpath->query('//link[contains(concat(" ", normalize-space(@rel), " "), " canonical ")]');

    return $links->length === 1 && rtrim($links->item(0)->getAttribute('href'), '/') === rtrim($expected, '/');
}

function hasSingleH1($html) {
    $document = loadHtmlDocument($html);

    return $document->getElementsByTagName('h1')->length === 1;
}

function visibleText($html) {
    $document = loadHtmlDocument($html);

    foreach (array('script', 'style', 'template', 'noscript') as $tag_name) {
        $nodes = $document->getElementsByTagName($tag_name);
        while ($nodes->length > 0) {
            $node = $nodes->item(0);
            $node->parentNode->removeChild($node);
        }
    }

    return preg_replace('/\s+/u', ' ', $document->textContent);
}

function assertSchemaType(array $blocks, $expected_type, $message) {
    $types = array();
    foreach ($blocks as $block) {
        $types = array_merge($types, schemaTypes($block));
    }

    assertCheck(in_array($expected_type, $types, true), $message);
}

$home = requestUrl($base_url . '/');
assertCheck($home['status'] === 200, 'Naslovnica vraća HTTP 200');
assertCheck(hasSingleH1($home['body']), 'Naslovnica ima točno jedan H1');
assertCheck(hasCanonical($home['body'], $base_url . '/'), 'Naslovnica ima ispravan canonical');
$home_schema = jsonLdBlocks($home['body']);
assertSchemaType($home_schema, 'Store', 'Naslovnica sadrži Store schema');
assertSchemaType($home_schema, 'WebSite', 'Naslovnica sadrži WebSite schema');
assertCheck(
    !preg_match('/(?:€\s*\d|\d+(?:[.,]\d+)?\s*(?:€|EUR))/iu', visibleText($home['body'])),
    'Naslovnica gostu ne prikazuje cijenu umetnutu u naziv proizvoda'
);

$product_path = '/r242-postanski-datumar';
$product = requestUrl($base_url . $product_path);
assertCheck($product['status'] === 200, 'Javna stranica proizvoda vraća HTTP 200');
assertCheck(hasSingleH1($product['body']), 'Proizvod ima točno jedan H1');
assertCheck(hasCanonical($product['body'], $base_url . $product_path), 'Proizvod ima ispravan canonical');
$product_schema = jsonLdBlocks($product['body']);
assertSchemaType($product_schema, 'Product', 'Proizvod sadrži Product schema');
assertSchemaType($product_schema, 'BreadcrumbList', 'Proizvod sadrži BreadcrumbList schema');

$forbidden_schema_keys = array(
    'offers',
    'price',
    'priceCurrency',
    'priceValidUntil',
    'availability',
    'inventoryLevel'
);
$has_forbidden_schema = false;
foreach ($product_schema as $block) {
    if (containsKeyRecursive($block, $forbidden_schema_keys)) {
        $has_forbidden_schema = true;
        break;
    }
}
assertCheck(!$has_forbidden_schema, 'Javni Product schema ne sadrži cijenu ni dostupnost');
assertCheck(
    !preg_match('/(?:€\s*\d|\d+(?:[.,]\d+)?\s*(?:€|EUR))/iu', visibleText($product['body'])),
    'Javna stranica proizvoda gostu ne prikazuje cijenu'
);

$price_name_product_paths = array(
    '/6-4926-stari-rezervni-jastucic-jastuk-u-kutiji-rasprodaja',
    '/gravometall-srebro-mesing-rasprodaja'
);
foreach ($price_name_product_paths as $price_name_product_path) {
    $price_name_product = requestUrl($base_url . $price_name_product_path);
    assertCheck(
        $price_name_product['status'] === 200,
        'Proizvod s uklonjenom cijenom iz naziva vraća HTTP 200: ' . $price_name_product_path
    );
    assertCheck(
        !preg_match('/(?:€\s*\d|\d+(?:[.,]\d+)?\s*(?:€|EUR))/iu', visibleText($price_name_product['body'])),
        'Proizvod gostu ne prikazuje cijenu: ' . $price_name_product_path
    );
}

$forbidden_html_patterns = array(
    '/property=["\']product:price/i',
    '/property=["\']product:availability/i',
    '/["\']offers["\']\s*:/i',
    '/["\']priceCurrency["\']\s*:/i',
    '/["\']availability["\']\s*:/i',
    '/relatedoptions_id/i'
);
foreach ($forbidden_html_patterns as $pattern) {
    assertCheck(!preg_match($pattern, $product['body']), 'Javni HTML ne sadrži zabranjeni komercijalni zapis ' . $pattern);
}

$blog = requestUrl($base_url . '/blog');
assertCheck($blog['status'] === 200, 'Blog vraća HTTP 200');
assertCheck(hasCanonical($blog['body'], $base_url . '/blog'), 'Blog ima ispravan canonical');
$blog_schema = jsonLdBlocks($blog['body']);
assertSchemaType($blog_schema, 'Blog', 'Blog sadrži Blog schema');

$post_path = '/blog/ciscenje-optike-trotec-lasera';
$post = requestUrl($base_url . $post_path);
assertCheck($post['status'] === 200, 'Blog članak vraća HTTP 200');
assertCheck(hasCanonical($post['body'], $base_url . $post_path), 'Blog članak ima ispravan canonical');
$post_schema = jsonLdBlocks($post['body']);
assertSchemaType($post_schema, 'BlogPosting', 'Blog članak sadrži BlogPosting schema');

$sitemap = requestUrl($base_url . '/sitemap-index.xml');
assertCheck($sitemap['status'] === 200, 'Sitemap indeks vraća HTTP 200');
assertCheck(stripos($sitemap['headers'], 'Content-Type: application/xml') !== false, 'Sitemap koristi XML Content-Type');
assertCheck(stripos($sitemap['headers'], 'X-Robots-Tag: noindex') !== false, 'Sitemap šalje X-Robots-Tag noindex');
assertCheck(strpos($sitemap['body'], '_blog.xml') !== false, 'Sitemap indeks uključuje blog');
assertCheck(strpos($sitemap['body'], '_product.xml') !== false, 'Sitemap indeks uključuje proizvode');
assertCheck(strpos($sitemap['body'], 'category_product') === false, 'Sitemap indeks ne uključuje duplikat category_product');

$llms = requestUrl($base_url . '/llms.txt');
assertCheck($llms['status'] === 200, 'llms.txt vraća HTTP 200');
assertCheck(strpos($llms['body'], 'Cijene i dostupnost') !== false, 'llms.txt opisuje javno pravilo kataloga');

$robots_route = requestUrl($base_url . '/index.php?route=common/robots');
assertCheck($robots_route['status'] === 200, 'Robots kontroler vraća HTTP 200');
assertCheck(strpos($robots_route['body'], 'Sitemap:') !== false, 'Robots sadržaj navodi sitemap');

$robots_file = requestUrl($base_url . '/robots.txt');
if ($robots_file['status'] === 200) {
    passCheck('robots.txt vraća HTTP 200');
} elseif (strpos($base_url, '.test') !== false && $robots_file['status'] === 404 && strpos($robots_file['body'], 'Sitemap:') !== false) {
    warnCheck('Herd presreće /robots.txt svojim exact-location pravilom; produkcija mora vratiti HTTP 200');
} else {
    failCheck('robots.txt mora vratiti HTTP 200');
}

$live_options = requestUrl($base_url . '/index.php?route=extension/basel/live_options&product_id=1');
assertCheck($live_options['status'] === 403, 'Guest live-options endpoint vraća HTTP 403');

$live_price = requestUrl($base_url . '/index.php?route=extension/liveopencart/liveprice/price&product_id=1');
assertCheck($live_price['status'] === 403, 'Guest live-price endpoint vraća HTTP 403');

$cart_add = requestUrl(
    $base_url . '/index.php?route=checkout/cart/add',
    array('product_id' => 1, 'quantity' => 1)
);
assertCheck($cart_add['status'] === 403, 'Guest dodavanje u košaricu vraća HTTP 403');

$google_feed = requestUrl($base_url . '/index.php?route=extension/feed/google_base');
assertCheck($google_feed['status'] === 404, 'Javni Google Base feed nije dostupan gostu');

$live_search = requestUrl($base_url . '/index.php?route=extension/basel/live_search&filter_name=trodat');
$live_search_json = json_decode($live_search['body'], true);
$search_is_private = $live_search['status'] === 200 && is_array($live_search_json);
if ($search_is_private && !empty($live_search_json['products'])) {
    foreach ($live_search_json['products'] as $search_product) {
        if (!array_key_exists('price', $search_product)
            || $search_product['price'] !== false
            || !array_key_exists('special', $search_product)
            || $search_product['special'] !== false
        ) {
            $search_is_private = false;
            break;
        }
    }
}
assertCheck($search_is_private, 'Brza pretraga gostu ne vraća cijene');

fwrite(STDOUT, PHP_EOL);
if ($failures > 0) {
    fwrite(STDOUT, sprintf("Rezultat: %d grešaka, %d upozorenja.%s", $failures, $warnings, PHP_EOL));
    exit(1);
}

fwrite(STDOUT, sprintf("Rezultat: sve provjere prolaze, %d upozorenja.%s", $warnings, PHP_EOL));
