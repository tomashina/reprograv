<?php
/**
 * CLI alternative to importing sql/2026-07-30_category_seo.sql in phpMyAdmin.
 *
 * The SQL file is the single source of truth so cPanel and local installations
 * always install exactly the same category content and FAQ relations.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$project_root = dirname(__DIR__);
require $project_root . '/upload/config.php';

if (DB_PREFIX !== 'oc_') {
    fwrite(
        STDERR,
        "The SQL installer expects DB_PREFIX oc_. Adapt the SQL file before importing it.\n"
    );
    exit(1);
}

$sql_file = $project_root . '/sql/2026-07-30_category_seo.sql';
$sql = file_get_contents($sql_file);

if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "Category SEO SQL file could not be read.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli(
        DB_HOSTNAME,
        DB_USERNAME,
        DB_PASSWORD,
        DB_DATABASE,
        (int) DB_PORT
    );
    $db->set_charset('utf8mb4');

    $language_result = $db->query(
        "SELECT language_id FROM `oc_language` " .
        "WHERE code = 'hr-hr' AND status = 1 LIMIT 1"
    );
    $language = $language_result->fetch_assoc();

    if (!$language || (int) $language['language_id'] !== 3) {
        throw new RuntimeException(
            'The SQL installer expects active Croatian language_id 3.'
        );
    }

    $summary = array();
    $db->multi_query($sql);

    do {
        if ($result = $db->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['uredjene_kategorije'])) {
                    $summary = $row;
                }
            }

            $result->free();
        }
    } while ($db->more_results() && $db->next_result());

    if (!$summary) {
        throw new RuntimeException('The SQL installer did not return a summary.');
    }

    fwrite(
        STDOUT,
        sprintf(
            "Category SEO installed: %d completed categories, %d FAQs, %d category links.\n",
            (int) $summary['uredjene_kategorije'],
            (int) $summary['faq_pitanja'],
            (int) $summary['faq_veze_s_kategorijama']
        )
    );
} catch (Throwable $error) {
    fwrite(STDERR, "Category SEO installation failed: " . $error->getMessage() . "\n");
    exit(1);
}
