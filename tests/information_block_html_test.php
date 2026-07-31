<?php
/**
 * Regression tests for information-block HTML decoding.
 *
 * Usage:
 *   php tests/information_block_html_test.php
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

require_once(__DIR__ . '/../upload/system/library/information_block_html.php');

$failures = 0;

function informationBlockHtmlAssertSame($expected, $actual, $message) {
	global $failures;

	if ($expected === $actual) {
		fwrite(STDOUT, "[OK]   {$message}\n");
		return;
	}

	$failures++;
	fwrite(STDOUT, "[FAIL] {$message}\n");
	fwrite(STDOUT, "       Expected: {$expected}\n");
	fwrite(STDOUT, "       Actual:   {$actual}\n");
}

informationBlockHtmlAssertSame(
	'<p>Normalan HTML</p>',
	InformationBlockHtml::decode('<p>Normalan HTML</p>'),
	'Sirovi HTML ostaje nepromijenjen'
);

informationBlockHtmlAssertSame(
	'<p>Ruby&reg;</p>',
	InformationBlockHtml::decode('&lt;p&gt;Ruby&amp;reg;&lt;/p&gt;'),
	'OpenCart HTML kodiran jednom vraća se u oblik za prikaz i editor'
);

informationBlockHtmlAssertSame(
	'<p><strong>Tvrtka Trotec</strong> i Ruby&reg;</p>',
	InformationBlockHtml::decode('&lt;p&gt;&amp;lt;p&amp;gt;&amp;lt;strong&amp;gt;Tvrtka Trotec&amp;lt;/strong&amp;gt; i Ruby&amp;amp;reg;&amp;lt;/p&amp;gt;&lt;/p&gt;'),
	'Stari dvostruko kodirani Summernote sadržaj automatski se oporavlja'
);

informationBlockHtmlAssertSame(
	'<code>&lt;primjer&gt;</code>',
	InformationBlockHtml::decode('&lt;code&gt;&amp;lt;primjer&amp;gt;&lt;/code&gt;'),
	'Namjerno prikazani HTML primjer ostaje tekst'
);

$raw_html = '<p>Premješteni blok s Ruby&reg;</p>';
$first_save = htmlspecialchars($raw_html, ENT_COMPAT, 'UTF-8');
$second_save = htmlspecialchars(InformationBlockHtml::decode($first_save), ENT_COMPAT, 'UTF-8');

informationBlockHtmlAssertSame(
	$first_save,
	$second_save,
	'Ponovno spremanje nakon promjene redoslijeda ne kodira HTML drugi put'
);

exit($failures ? 1 : 0);
