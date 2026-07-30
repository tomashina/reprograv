<?php
/**
 * Contract-withdrawal and cookie-consent public smoke test.
 *
 * Usage:
 *   php tests/contract_withdrawal_smoke.php
 *   php tests/contract_withdrawal_smoke.php https://www.repro-grav.com
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$base_url = isset($argv[1]) ? rtrim($argv[1], '/') : 'https://reprograv.test';
$url = $base_url . '/jednostrani-raskid-ugovora';
$cookie_file = tempnam(sys_get_temp_dir(), 'rg-withdrawal-');
$failures = 0;

function withdrawalRequest($url, $cookie_file, array $post = null) {
    $handle = curl_init($url);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_COOKIEJAR => $cookie_file,
        CURLOPT_COOKIEFILE => $cookie_file,
        CURLOPT_ENCODING => ''
    );

    if ($post !== null) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($post);
    }

    curl_setopt_array($handle, $options);
    $response = curl_exec($handle);
    $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $result = array(
        'status' => (int)curl_getinfo($handle, CURLINFO_HTTP_CODE),
        'headers' => $response === false ? '' : substr($response, 0, $header_size),
        'body' => $response === false ? '' : substr($response, $header_size),
        'error' => $response === false ? curl_error($handle) : ''
    );
    curl_close($handle);

    return $result;
}

function withdrawalAssert($condition, $message) {
    global $failures;
    if ($condition) {
        fwrite(STDOUT, "[OK]   {$message}\n");
    } else {
        $failures++;
        fwrite(STDOUT, "[FAIL] {$message}\n");
    }
}

function hiddenField($html, $name) {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($document);
    $nodes = $xpath->query('//input[@name="' . $name . '"]');

    return $nodes->length ? $nodes->item(0)->getAttribute('value') : '';
}

try {
    $get = withdrawalRequest($url, $cookie_file);
    withdrawalAssert($get['status'] === 200, 'SEO URL obrasca vraća HTTP 200');
    withdrawalAssert(strpos($get['body'], 'Jednostrani raskid ugovora') !== false, 'Obrazac prikazuje traženi naslov');
    withdrawalAssert(strpos($get['body'], 'reprograv-consent.js') !== false, 'Custom cookie manager je učitan');
    withdrawalAssert(strpos($get['body'], 'catalog/view/javascript/mpgdpr/cookieconsent/cookieconsent.js') === false, 'Stari GDPR popup nije učitan');
    withdrawalAssert(strpos($get['body'], 'src="https://www.googletagmanager.com/') === false, 'Analitika se ne učitava prije privole');
    withdrawalAssert(strpos($get['body'], '<link href="' . $url . '" rel="canonical"') !== false, 'Obrazac ima ispravan canonical');

    $csrf = hiddenField($get['body'], 'csrf_token');
    $started_at = hiddenField($get['body'], 'form_started_at');
    withdrawalAssert($csrf !== '' && $started_at !== '', 'Obrazac sadrži sigurnosne tokene');

    $review = withdrawalRequest($url, $cookie_file, array(
        'withdrawal_action' => 'review',
        'csrf_token' => $csrf,
        'form_started_at' => $started_at,
        'website' => '',
        'full_name' => 'Test Potrošač',
        'email' => 'test@example.test',
        'phone' => '+385 1 555 0100',
        'address_line' => 'Testna 1',
        'postal_code' => '10000',
        'city' => 'Zagreb',
        'country_code' => 'HR',
        'order_number' => 'TEST-1001',
        'contract_date' => date('Y-m-d', strtotime('-5 days')),
        'received_date' => date('Y-m-d', strtotime('-2 days')),
        'items' => 'Testni proizvod, 1 kom',
        'note' => ''
    ));
    withdrawalAssert($review['status'] === 200, 'Korak pregleda vraća HTTP 200');
    withdrawalAssert(strpos($review['body'], 'Pregledajte izjavu o raskidu') !== false, 'Korak pregleda prikazuje sažetak izjave');
    withdrawalAssert(strpos($review['body'], 'Potvrditi raskid ugovora') !== false, 'Konačni gumb je jasno označen');
    withdrawalAssert(hiddenField($review['body'], 'draft_token') !== '', 'Pregled ima vremenski ograničen token nacrta');
} finally {
    if (is_file($cookie_file)) {
        unlink($cookie_file);
    }
}

exit($failures ? 1 : 0);
